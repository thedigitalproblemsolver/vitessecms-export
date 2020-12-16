<?php declare(strict_types=1);

namespace VitesseCms\Export\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Export\Helpers\AmazonExportHelper;
use VitesseCms\Export\Helpers\BeslistExportHelper;
use VitesseCms\Export\Helpers\EtsyExportHelper;
use VitesseCms\Export\Helpers\FacebookProductsExportHelper;
use VitesseCms\Export\Helpers\GoogleShoppingExportHelper;
use VitesseCms\Export\Helpers\RssExportHelper;
use VitesseCms\Export\Helpers\SitemapExportHelper;
use VitesseCms\Export\Helpers\TradetrackerExportHelper;
use VitesseCms\Export\Interfaces\AbstractExportHelperInterface;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Language\Models\Language;

class ExportTypeForm extends AbstractForm
{

    public function initialize(ExportType $item): void
    {
        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            [
                'required' => 'required',
            ]
        )->_(
            'select',
            '%ADMIN_DATAGROUP%',
            'datagroup',
            [
                'options' => ElementHelper::arrayToSelectOptions(Datagroup::findAll()),
            ]
        );

        if ($item->_('datagroup')) :
            Item::setFindValue('datagroup', $item->_('datagroup'));
            $items = Item::findAll();
            $this->_(
                'select',
                '%EXPORT_INCLUDE_CHILDREN_FROM_ITEM%',
                'getChildrenFrom',
                [
                    'options' => ElementHelper::arrayToSelectOptions($items),
                ]
            );
        endif;

        $this->_(
            'select',
            '%EXPORT_TYPE%',
            'type',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions([
                    SitemapExportHelper::class          => '%EXPORT_TYPE_SITEMAP%',
                    GoogleShoppingExportHelper::class   => '%EXPORT_TYPE_GOOGLE_SHOPPING%',
                    TradetrackerExportHelper::class     => '%EXPORT_TYPE_TRADETRACKER%',
                    BeslistExportHelper::class          => '%EXPORT_TYPE_BESLIST%',
                    FacebookProductsExportHelper::class => '%EXPORT_TYPE_FACEBOOKPRODUCTS%',
                    RssExportHelper::class              => '%EXPORT_TYPE_RSS%',
                    EtsyExportHelper::class             => '%EXPORT_TYPE_ETSY%',
                    AmazonExportHelper::class           => '%EXPORT_TYPE_AMAZON%',
                ]),
            ]
        )->_(
            'select',
            'Caching time',
            'cachingTime',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions([
                    'none'   => 'None',
                    '-1 day'   => '1 day',
                    '-1 week'  => '1 week',
                    '-2 weeks' => '2 weeks',
                    '-3 weeks' => '3 weeks',
                    '-1 month' => '1 month',
                ]),
            ]
        );

        if ($item !== null && $item->hasType()) :
            $object = $item->getTypeClass();
            $object::buildAdminForm($this, $item);
        endif;

        if ($item->getId()) :
            $html = '<ul>';
            foreach (Language::findAll() as $language) :
                $html .= '<li><a 
                        href="'.$language->_('domain').'/export/index/index/'.$item->getId().'" 
                        target="_blank"
                    >'.
                    $language->_('domain').
                    '</a>
                </li>';
            endforeach;
            $html .= '</ul>';

            $this->_('html', 'html', 'html', ['html' => $html]);
        endif;

        $this->_('submit', '%CORE_SAVE%');
    }
}
