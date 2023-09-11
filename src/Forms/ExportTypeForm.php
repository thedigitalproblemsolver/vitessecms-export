<?php declare(strict_types=1);

namespace VitesseCms\Export\Forms;

use Phalcon\Forms\Form;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Helpers\AmazonExportHelper;
use VitesseCms\Export\Helpers\BeslistExportHelper;
use VitesseCms\Export\Helpers\EtsyExportHelper;
use VitesseCms\Export\Helpers\FacebookProductsExportHelper;
use VitesseCms\Export\Helpers\GoogleShoppingExportHelper;
use VitesseCms\Export\Helpers\RssExportHelper;
use VitesseCms\Export\Helpers\SitemapExportHelper;
use VitesseCms\Export\Helpers\TradetrackerExportHelper;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\RepositoryInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\AbstractFormWithRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;
use VitesseCms\Form\Models\Attributes;

class ExportTypeForm extends AbstractForm implements AdminModelFormInterface
{
    public function buildForm(): void
    {
        $this->addText('%CORE_NAME%', 'name', (new Attributes())->setRequired()->setMultilang())
            ->addDropdown(
                '%ADMIN_DATAGROUP%',
                'datagroup',
                (new Attributes())->setOptions(ElementHelper::modelIteratorToOptions(
                    $this->repositories->datagroup->findAll()
                ))
            );

        if ($this->entity->getDatagroup() !== '') :
            $items = $this->repositories->item->findAll(new FindValueIterator(
                [new FindValue('datagroup', $this->entity->getDatagroup())]
            ),
                true,
                999
            );
            $this->addDropdown(
                '%EXPORT_INCLUDE_CHILDREN_FROM_ITEM%',
                'getChildrenFrom',
                (new Attributes())->setOptions(ElementHelper::modelIteratorToOptions($items))
            );
        endif;

        $this->addDropdown(
            '%EXPORT_TYPE%',
            'type',
            (new Attributes())
                ->setRequired(true)
                ->setOptions(
                    ElementHelper::arrayToSelectOptions([
                            SitemapExportHelper::class => '%EXPORT_TYPE_SITEMAP%',
                            GoogleShoppingExportHelper::class => '%EXPORT_TYPE_GOOGLE_SHOPPING%',
                            TradetrackerExportHelper::class => '%EXPORT_TYPE_TRADETRACKER%',
                            BeslistExportHelper::class => '%EXPORT_TYPE_BESLIST%',
                            FacebookProductsExportHelper::class => '%EXPORT_TYPE_FACEBOOKPRODUCTS%',
                            RssExportHelper::class => '%EXPORT_TYPE_RSS%',
                            EtsyExportHelper::class => '%EXPORT_TYPE_ETSY%',
                            AmazonExportHelper::class => '%EXPORT_TYPE_AMAZON%',
                        ]
                    )
                )
        )->addDropdown(
            'Caching time',
            'cachingTime',
            (new Attributes())
                ->setRequired(true)
                ->setOptions(ElementHelper::arrayToSelectOptions([
                    'none' => 'None',
                    '-1 day' => '1 day',
                    '-1 week' => '1 week',
                    '-2 weeks' => '2 weeks',
                    '-3 weeks' => '3 weeks',
                    '-1 month' => '1 month',
                ])
                )
        );

        if ($this->entity !== null && $this->entity->hasType()) :
            //TODO move to listener
            $object = $this->entity->getTypeClass();
            $object::buildAdminForm($this, $this->entity, $this->repositories);
        endif;

        if ($this->entity->getId()) :
            $languages = $this->repositories->language->findAll();
            if ($languages->count() > 0) :
                $this->addHtml(
                    $this->view->renderTemplate(
                        'export_type_form_index_list',
                        $this->configuration->getVendorNameDir() . 'export/src/Resources/views/admin/',
                        [
                            'languages' => $languages,
                            'domain' => $this->url->getBaseUri()
                        ]
                    )
                );
            endif;
        endif;

        $this->addSubmitButton('%CORE_SAVE%');
    }
}
