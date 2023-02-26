<?php declare(strict_types=1);

namespace VitesseCms\Export\Forms;

use Phalcon\Forms\Form;
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
use VitesseCms\Form\AbstractFormWithRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;
use VitesseCms\Form\Models\Attributes;

class ExportTypeForm extends AbstractFormWithRepository
{
    /**
     * @var ExportType
     */
    protected $item;

    public function buildForm(): FormWithRepositoryInterface
    {
        $this->addText('%CORE_NAME%', 'name', (new Attributes())->setRequired(true))
            ->addDropdown(
                '%ADMIN_DATAGROUP%',
                'datagroup',
                (new Attributes())->setOptions(ElementHelper::modelIteratorToOptions(
                    $this->repositories->datagroup->findAll()
                ))
            );

        if ($this->item->getDatagroup() !== '') :
            $items = $this->repositories->item->findAll(new FindValueIterator(
                [new FindValue('datagroup', $this->item->getDatagroup())]
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

        if ($this->item !== null && $this->item->hasType()) :
            //TODO move to listener
            $object = $this->item->getTypeClass();
            $object::buildAdminForm($this, $this->item, $this->repositories);
        endif;

        if ($this->item->getId()) :
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

        return $this;
    }

    public function setEntity($entity): Form
    {
        $this->item = $entity;

        parent::setEntity($entity);

        return $this;
    }
}
