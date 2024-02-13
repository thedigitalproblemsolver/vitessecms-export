<?php

declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use Phalcon\Http\Request;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use Thepixeldeveloper\Sitemap\Sitemap;
use Thepixeldeveloper\Sitemap\SitemapIndex;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Urlset;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\RepositoryInterface;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

/**
 * Class SitemapExportHelper
 * https://packagist.org/packages/thepixeldeveloper/sitemap
 */
class SitemapExportHelper extends AbstractExportHelper
{
    private int $limit = 3000;

    public static function buildAdminForm(
        ExportTypeForm $form,
        ExportType $item,
        RepositoryInterface $repositories
    ): void {
        $form->addDropdown(
            '%EXPORT_SITEMAP_CHANGE_FREQUENCY%',
            'frequency',
            (new Attributes())->setRequired(true)->setOptions(
                ElementHelper::arrayToSelectOptions([
                    'always' => '%EXPORT_SITEMAP_ALWAYS%',
                    'hourly' => '%EXPORT_SITEMAP_HOURLY%',
                    'daily' => '%EXPORT_SITEMAP_DAILY%',
                    'weekly' => '%EXPORT_SITEMAP_WEEKLY%',
                    'monthly' => '%EXPORT_SITEMAP_MONTHLY%',
                    'yearly' => '%EXPORT_SITEMAP_YEARLY%',
                    'never' => '%EXPORT_SITEMAP_NEVER%',
                ])
            )
        )->addDropdown(
            '%EXPORT_SITEMAP_PRIORITY%',
            'priority',
            (new Attributes())->setRequired(true)->setOptions(
                ElementHelper::arrayToSelectOptions([
                    '0.1' => '%EXPORT_SITEMAP_NOT_IMPORTANT%',
                    '0.5' => '%EXPORT_SITEMAP_IMPORTANT%',
                    '1' => '%EXPORT_SITEMAP_VERY_IMPORTANT%'
                ])
            )
        );
    }

    public function createOutput(): string
    {
        $urlSet = new Urlset();
        foreach ($this->items as $items) {
            /** @var Item $item */
            foreach ($items as $item) {
                $this->addItemToSitemap($item, $this->exportType, $this->url, $urlSet);
            }
        }

        return $this->createXmlOutput($urlSet);
    }

    private function addItemToSitemap(Item $item, ExportType $exportType, UrlService $urlService, urlSet $urlSet): void
    {
        $url = new Url($urlService->getBaseUri() . $item->getSlug());
        $url->setLastMod($item->getUpdatedOn() ?? $item->getCreateDate());
        $url->setChangeFreq($exportType->_('frequency'));
        $url->setPriority($exportType->_('priority'));

        $urlSet->add($url);
    }

    private function createXmlOutput(Urlset $urlset): string
    {
        $driver = new XmlWriterDriver();
        $urlset->accept($driver);

        return $driver->output();
    }

    public function createOutputByIterator(
        ItemIterator $itemIterator,
        ExportType $exportType,
        UrlService $urlService
    ): string {
        if ($itemIterator->count() < $this->limit) {
            return $this->createUrlSet($itemIterator, $exportType, $urlService);
        }

        $request = new Request();
        if ($request->has('offset')) {
            return $this->createUrlSubSet($itemIterator, (int)$request->get('offset'), $exportType, $urlService);
        }

        return $this->createSitemapIndex($itemIterator->count(), $exportType, $urlService);
    }

    private function createUrlSet(\Iterator $itemIterator, ExportType $exportType, UrlService $urlService): string
    {
        $urlSet = new Urlset();
        while ($itemIterator->valid()) {
            $this->addItemToSitemap($itemIterator->current(), $exportType, $urlService, $urlSet);
            $itemIterator->next();
        }

        return $this->createXmlOutput($urlSet);
    }

    private function createUrlSubSet(
        ItemIterator $itemIterator,
        int $offset,
        ExportType $exportType,
        UrlService $urlService
    ): string {
        $urlSet = new Urlset();
        for ($i = $offset; $i <= ($offset + $this->limit); $i++) {
            $itemIterator->seek($i);
            $this->addItemToSitemap($itemIterator->current(), $exportType, $urlService, $urlSet);
        }

        return $this->createXmlOutput($urlSet);
    }

    private function createSitemapIndex(int $itemCount, ExportType $exportType, UrlService $urlService): string
    {
        $sitemapIndex = new SitemapIndex();
        $baseUrl = $urlService->getBaseUri() . 'export/index/index/' . $exportType->getId();

        for ($offset = 0; $offset <= $itemCount; $offset += $this->limit) {
            $url = (new Sitemap($baseUrl . '?offset=' . $offset));
            $url->setLastMod($exportType->getUpdatedOn());
            $sitemapIndex->add($url);
        }

        $driver = new XmlWriterDriver();
        $sitemapIndex->accept($driver);

        return $driver->output();
    }

    public function setHeaders(): void
    {
        header('Content-type: text/xml');
    }
}
