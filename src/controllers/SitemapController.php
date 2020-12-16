<?php

namespace VitesseCms\Export\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Export\Helpers\SitemapExportHelper;
use VitesseCms\Export\Models\ExportType;
use Thepixeldeveloper\Sitemap\Output;
use Thepixeldeveloper\Sitemap\Sitemap;
use Thepixeldeveloper\Sitemap\SitemapIndex;

/**
 * Class SitemapController
 */
class SitemapController extends AbstractController
{
    /**
     * IndexAction
     */
    public function IndexAction(): void
    {
        ExportType::setFindValue('type', SitemapExportHelper::class);
        $sitemaps = ExportType::findAll();
        if ($sitemaps) :
            $sitemapIndex = new SitemapIndex();
            foreach ($sitemaps as $sitemap) :
                $url = (new Sitemap(
                    $this->url->getBaseUri() . 'export/index/index/'.$sitemap->getId()
                ))->setLastMod(date('Y-m-d', time()));

                $sitemapIndex->addSitemap($url);
            endforeach;

            header('Content-type: text/xml');
            echo (new Output())->getOutput($sitemapIndex);
        endif;

        $this->view->disable();
    }
}
