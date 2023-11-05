<?php
declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use stdClass;
use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use VitesseCms\Core\AbstractControllerFrontend;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Enums\ExportTypeEnums;
use VitesseCms\Export\Helpers\SitemapExportHelper;
use Thepixeldeveloper\Sitemap\Output;
use Thepixeldeveloper\Sitemap\Sitemap;
use Thepixeldeveloper\Sitemap\SitemapIndex;
use VitesseCms\Export\Repositories\ExportTypeRepository;

class SitemapController extends AbstractControllerFrontend
{
    private ExportTypeRepository $exportTypeRepository;

    public function OnConstruct()
    {
        parent::onConstruct();

        $this->exportTypeRepository = $this->eventsManager->fire(
            ExportTypeEnums::GET_REPOSITORY->value,
            new stdClass()
        );
    }

    public function IndexAction(): void
    {
        $xmlResponse = '';
        $sitemaps = $this->exportTypeRepository->findAll(
            new FindValueIterator([new FindValue('type', SitemapExportHelper::class)])
        );

        if ($sitemaps->count() > 0) :
            $sitemapIndex = new SitemapIndex();
            while ($sitemaps->valid()) :
                $sitemap = $sitemaps->current();
                $url = (new Sitemap($this->urlService->getBaseUri() . 'export/index/index/' . $sitemap->getId()));
                $url->setLastMod($sitemap->getUpdatedOn());

                $sitemapIndex->add($url);
                $sitemaps->next();
            endwhile;

            $driver = new XmlWriterDriver();
            $sitemapIndex->accept($driver);
            $xmlResponse = $driver->output();
        endif;

        $this->xmlResponse($xmlResponse);
    }
}
