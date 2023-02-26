<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use DateTime;
use VitesseCms\Content\Enum\ItemEnum;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractControllerFrontend;
use VitesseCms\Core\Enum\CacheEnum;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Services\CacheService;
use VitesseCms\Database\Models\FindOrder;
use VitesseCms\Database\Models\FindOrderIterator;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Enums\ExportTypeEnums;
use VitesseCms\Export\Helpers\AbstractExportHelperInterface;
use VitesseCms\Export\Helpers\SitemapExportHelper;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\ExportTypeRepository;

class IndexController extends AbstractControllerFrontend
{
    private ExportTypeRepository $exportTypeRepository;
    private CacheService $cacheService;
    private ItemRepository $itemRepository;

    public function OnConstruct()
    {
        parent::onConstruct();

        $this->exportTypeRepository = $this->eventsManager->fire(ExportTypeEnums::GET_REPOSITORY->value, new \stdClass());
        $this->cacheService = $this->eventsManager->fire(CacheEnum::ATTACH_SERVICE_LISTENER, new \stdClass());
        $this->itemRepository = $this->eventsManager->fire(ItemEnum::GET_REPOSITORY, new \stdClass());
    }

    public function IndexAction(string $id): void
    {
        $exportType = $this->exportTypeRepository->getById($id);
        if ($exportType !== null) :
            $class = $exportType->getTypeClass();
            $exportHelper = new $class($this->configService->getLanguage(), $this->repositories);

            $content = null;
            $cacheKey = null;
            if ($exportType->hasCachingTime()) :
                $this->cacheService->setTimetoLife(
                    (int)(new DateTime())->modify($exportType->getCachingTime())->format('U')
                );
                $cacheKey = $this->cacheService->getCacheKey('ExportType' . $id);
                $content = $this->cacheService->get($cacheKey);
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
                    $this->cacheService->save($cacheKey, $content);
                endif;
            endif;

            $exportHelper->setHeaders();
            echo $content;
            die();
        endif;

        $this->viewService->disable();
    }

    protected function parseItemsAsIterator(ExportType $exportType, AbstractExportHelperInterface $exportHelper): string
    {
        $datagroupItems = $this->getItemIdsByDatagroupForExportType(
            $exportType->getDatagroup(),
            (string)$exportType->getId()
        );

        if (!empty($exportType->getGetChildrenFrom())) :
            $this->appendRecursiveChildrenForExportType(
                $exportType->getGetChildrenFrom(),
                $datagroupItems
            );
        endif;

        return $exportHelper->createOutputByIterator(
            $datagroupItems,
            $exportType,
            $this->urlService
        );
    }

    private function getItemIdsByDatagroupForExportType(string $datagroupId, string $exportTypeId): ItemIterator
    {
        return $this->itemRepository->findAll(
            new FindValueIterator([
                new FindValue('datagroup', $datagroupId),
                new FindValue('excludeFromExport', ['$nin' => [$exportTypeId]])
            ]),
            true,
            null,
            new FindOrderIterator([
                new FindOrder('createdAt', -1)
            ]),
            ['_id']
        );
    }

    private function appendRecursiveChildrenForExportType(string $parentId, ItemIterator $itemIterator): ItemIterator
    {
        $items = $this->itemRepository->findAll(
            new FindValueIterator([new FindValue('parentId', $parentId)]),
            true,
            9999,
            null,
            ['hasChildren' => true]
        );
        foreach ($items as $item):
            $itemIterator->add($item);
            if ($item->hasChildren()) :
                $itemIterator = $this->appendRecursiveChildrenForExportType((string)$item->getId(), $itemIterator);
            endif;
        endforeach;

        return $itemIterator;
    }

    private function parseItemsAsArray(ExportType $exportType, AbstractExportHelperInterface $exportHelper): string
    {
        $items = [[]];
        $exportHelper->preFindAll($exportType);
        $datagroupItems = $this->itemRepository->findAll(
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
}
