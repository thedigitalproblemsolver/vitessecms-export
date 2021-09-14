<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use Thepixeldeveloper\Sitemap\Drivers\XmlWriterDriver;
use VitesseCms\Core\AbstractController;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Helpers\SitemapExportHelper;
use Thepixeldeveloper\Sitemap\Output;
use Thepixeldeveloper\Sitemap\Sitemap;
use Thepixeldeveloper\Sitemap\SitemapIndex;
use VitesseCms\Export\Repositories\RepositoriesInterface;

class SitemapController extends AbstractController implements RepositoriesInterface
{
    public function IndexAction(): void
    {
        $sitemaps = $this->repositories->exportType->findAll(
            new FindValueIterator([new FindValue('type', SitemapExportHelper::class)])
        );

        if ($sitemaps->count() > 0) :
            $sitemapIndex = new SitemapIndex();
            while ($sitemaps->valid()) :
                $sitemap = $sitemaps->current();
                $url = (new Sitemap(
                    $this->url->getBaseUri() . 'export/index/index/' . $sitemap->getId()
                ));
                $url->setLastMod($sitemap->getUpdatedOn());

                $sitemapIndex->add($url);
                $sitemaps->next();
            endwhile;

            $driver = new XmlWriterDriver();
            $sitemapIndex->accept($driver);

            header('Content-type: text/xml');
            echo $driver->output();
        endif;

        $this->view->disable();
    }
}
