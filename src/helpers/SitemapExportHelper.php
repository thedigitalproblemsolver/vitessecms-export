<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Form\Helpers\ElementHelper;
use Thepixeldeveloper\Sitemap\Output;
use Thepixeldeveloper\Sitemap\Url;
use Thepixeldeveloper\Sitemap\Urlset;

/**
 * Class SitemapExportHelper
 * https://packagist.org/packages/thepixeldeveloper/sitemap
 */
class SitemapExportHelper extends AbstractExportHelper
{
    public function createOutput(): string
    {
        $urlSet = new Urlset();
        /** @var AbstractCollection $item */
        foreach ($this->items as $items) :
            foreach ($items as $item) :
                $url = (new Url($this->url->getBaseUri() . $item->_('slug')))
                    ->setLastMod($item->_('updatedAt'))
                    ->setChangeFreq($this->exportType->_('frequency'))
                    ->setPriority($this->exportType->_('priority'));

                $urlSet->addUrl($url);
            endforeach;
        endforeach;

        return (new Output())->getOutput($urlSet);
    }

    public function createOutputByIterator(
        ItemIterator $itemIterator,
        ExportType $exportType,
        UrlService $urlService
    ): string
    {
        $urlSet = new Urlset();
        while ($itemIterator->valid()):
            $itemId = $itemIterator->current();
            Item::setRenderFields(false);
            /** @var Item $item */
            $item = Item::findById((string)$itemId->getId());
            $url = (new Url($urlService->getBaseUri() . $item->_('slug')))
                ->setLastMod($item->getUpdatedOn()->format('Y-m-d'))
                ->setChangeFreq($exportType->_('frequency'))
                ->setPriority($exportType->_('priority'));

            $urlSet->addUrl($url);
            $itemIterator->next();
        endwhile;

        return (new Output())->getOutput($urlSet);
    }

    public function setHeaders(): void
    {
        header('Content-type: text/xml');
    }

    public static function buildAdminForm(ExportTypeForm $form, ExportType $item): void
    {
        $form->_(
            'select',
            '%EXPORT_SITEMAP_CHANGE_FREQUENCY%',
            'frequency',
            [
                'required' => 'required',
                'options' => ElementHelper::arrayToSelectOptions([
                    'always' => '%EXPORT_SITEMAP_ALWAYS%',
                    'hourly' => '%EXPORT_SITEMAP_HOURLY%',
                    'daily' => '%EXPORT_SITEMAP_DAILY%',
                    'weekly' => '%EXPORT_SITEMAP_WEEKLY%',
                    'monthly' => '%EXPORT_SITEMAP_MONTHLY%',
                    'yearly' => '%EXPORT_SITEMAP_YEARLY%',
                    'never' => '%EXPORT_SITEMAP_NEVER%',
                ]),
            ]
        );

        $form->_(
            'select',
            '%EXPORT_SITEMAP_PRIORITY%',
            'priority',
            [
                'required' => 'required',
                'options' => ElementHelper::arrayToSelectOptions([
                    '0.1' => '%EXPORT_SITEMAP_NOT_IMPORTANT%',
                    '0.5' => '%EXPORT_SITEMAP_IMPORTANT%',
                    '1' => '%EXPORT_SITEMAP_VERY_IMPORTANT%'
                ])
            ]
        );
    }
}
