<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use DateTime;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\AbstractController;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Database\Models\FindOrder;
use VitesseCms\Database\Models\FindOrderIterator;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Helpers\AbstractExportHelperInterface;
use VitesseCms\Export\Helpers\SitemapExportHelper;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\RepositoriesInterface;

class IndexController extends AbstractController implements RepositoriesInterface
{
    public function IndexAction(string $id): void
    {
        $exportType = $this->repositories->exportType->getById($id);
        if ($exportType !== null) :
            $class = $exportType->getTypeClass();
            $exportHelper = new $class($this->configuration->getLanguage(), $this->repositories);

            $content = null;
            $cacheKey = null;
            if ($exportType->hasCachingTime()) :
                $this->cache->setTimetoLife(
                    (int)(new DateTime())->modify($exportType->getCachingTime())->format('U')
                );
                $cacheKey = $this->cache->getCacheKey('ExportType' . $id);
                $content = $this->cache->get($cacheKey);
            endif;

            if ($content === null) :
                switch ($exportType->getType()):
                    case SitemapExportHelper::class:
                        $content = $this->parseItemsAsIterator($exportType, $exportHelper);
                        break;
                    default:
                        $content = $this->parseItemsAsArray($exportType, $exportHelper);
                endswitch;

                if ($exportType->hasCachingTime()) :
                    $this->cache->save($cacheKey, $content);
                endif;
            endif;

            $exportHelper->setHeaders();
            echo $content;
        endif;

        $this->view->disable();
    }

    protected function parseItemsAsIterator(
        ExportType $exportType,
        AbstractExportHelperInterface $exportHelper
    ): string
    {
        $datagroupItems = $this->repositories->item->getItemIdsByDatagroupForExportType(
            $exportType->getDatagroup(),
            (string)$exportType->getId()
        );

        if (!empty($exportType->getGetChildrenFrom())) :
            $this->repositories->item->appendRecursiveChildrenForExportType(
                $exportType->getGetChildrenFrom(),
                $datagroupItems
            );
        endif;

        return $exportHelper->createOutputByIterator(
            $datagroupItems,
            $exportType,
            $this->url
        );
    }

    protected function parseItemsAsArray(
        ExportType $exportType,
        AbstractExportHelperInterface $exportHelper
    ): string
    {
        $items = [[]];
        $exportHelper->preFindAll($exportType);
        $datagroupItems = $this->repositories->item->findAll(
            new FindValueIterator([
                new FindValue('datagroup', $exportType->getDatagroup()),
                new FindValue('excludeFromExport', ['$nin' => [(string)$exportType->getId()]])
            ]),
            true,
            9999,
            new FindOrderIterator([new FindOrder('createdAt', -1)])
        );

        if ($exportType->hasGetChildrenFrom()) :
            $datagroupItems = array_merge(
                $datagroupItems,
                ItemHelper::getRecursiveChildren($exportType->getGetChildrenFrom())
            );
        endif;

        if ($datagroupItems) :
            $items[] = $datagroupItems;
        endif;
        $items = array_merge($items);

        $exportHelper->setExportType($exportType);
        $exportHelper->setItems($items);

        return $exportHelper->createOutput();
    }

    public function ChannelEngineSyncAction(): void
    {
        Item::addFindOrder('channelEngineLastSyncDate', 1);
        Item::setFindValue('outOfStock', ['$in' => ['', null, false]]);
        Item::setFindValue('ean', ['$nin' => ['', null, false]]);
        $item = Item::findFirst();

        if ($item) :
            if ($this->channelEngine->createOrUpdateProduct($item)) :
                $this->log->write(
                    $item->getId(),
                    Item::class,
                    'ChannelEngine <b>' . $item->_('name') . '</b> synced.'
                );
            endif;
            $item->set('channelEngineLastSyncDate', time())->save();
        endif;

        die();
    }

    public function MailChimpSyncAction(): void
    {
        $datagroup = $this->setting->get('MAILCHIMP_PRODUCT_DATAGROUP');

        Item::addFindOrder('mailChimpLastSyncDate.' . $this->configuration->getLanguageShort(), 1);
        Item::setFindValue('outOfStock', ['$in' => ['', null, false]]);
        Item::setFindValue('datagroup', $datagroup);
        $item = Item::findFirst();

        $parents = ItemHelper::getPathFromRoot($item);

        if ($item) :
            $product = $this->mailchimp->getProductById((string)$item->getId());
            if (empty($product) || $product['status'] === 404) :
                $this->mailchimp->createProduct($item);
                $this->log->write(
                    $item->getId(),
                    Item::class,
                    'MailChimp product created for <b>' . implode(' > ', $parents) . '</b>'
                );
            else :
                $this->mailchimp->updateProduct($item);
                $this->log->write(
                    $item->getId(),
                    Item::class,
                    'MailChimp product updated for <b>' . implode(' > ', $parents) . '</b>'
                );
            endif;
        endif;

        $item->set(
            'mailChimpLastSyncDate',
            time(),
            true,
            $this->configuration->getLanguageShort()
        )->save();

        die();
    }
}
