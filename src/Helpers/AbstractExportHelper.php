<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use DateTime;
use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\RepositoryInterface;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Language\Models\Language;
use function is_array;

abstract class AbstractExportHelper extends AbstractInjectable implements AbstractExportHelperInterface
{
    /**
     * @var array
     */
    protected static $adminFormFields = [];
    /**
     * @var array
     */
    protected $fields;
    /**
     * @var array
     */
    protected $items;

    /**
     * @var ItemIterator
     */
    protected $itemIterator;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var ExportType
     */
    protected $exportType;

    /**
     * @var RepositoryInterface
     */
    protected $repositories;

    public function __construct(Language $language, RepositoryInterface $repositories)
    {
        $this->fields = [];
        $this->items = [];
        $this->repositories = $repositories;
        $this->language = $language;
    }

    public static function buildAdminForm(
        ExportTypeForm $form,
        ExportType $item,
        RepositoryInterface $repositories
    ): void
    {
        if (
            $item->getDatagroup() !== ''
            && count($item->getType()::$adminFormFields) > 0
        ) :
            $datafieldOptions = [];
            $datagroup = $repositories->datagroup->getById($item->_('datagroup'));
            foreach ($datagroup->getDatafields() as $datafieldSet) :
                if ($datafieldSet['published'] === true) :
                    $datafield = $repositories->datafield->getById($datafieldSet['id']);
                    if ($datafield !== null) :
                        $datafieldOptions[$datafield->getCallingName()] = $datafield->getNameField();
                    endif;
                endif;
            endforeach;

            $form->addHtml('<p><br/><b>Match Exportfields with custom text or a DataField</b></p>');
            foreach ($item->_('type')::$adminFormFields as $exportField) :
                $form->addDropdown(
                    '%ADMIN_DATAFIELD% ' . ucfirst($exportField),
                    'exportDatafield_' . $exportField,
                    (new Attributes())->setInputClass('select2')->setOptions(ElementHelper::arrayToSelectOptions($datafieldOptions))
                )->addText(
                    '%CORE_OR%',
                    'exportField_' . $exportField,
                    (new Attributes())->setMultilang(true)
                );
            endforeach;
        endif;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function setHeaders(): void
    {
    }

    public function preFindAll(ExportType $exportType): void
    {
    }

    public function setExportType(AbstractCollection $exportType): void
    {
        $this->exportType = $exportType;
    }

    public function createOutputByIterator(ItemIterator $itemIterator, ExportType $exportType, UrlService $url): string
    {
        // TODO: Implement createOutputByIterator() method.
    }

    protected function getItemValue(AbstractCollection $item, string $fieldName): string
    {
        if (get_class($item) === Item::class && !empty($item->_($fieldName))) :
            $datafield = $this->repositories->datafield->findFirst(
                new FindValueIterator([new FindValue('calling_name', $fieldName)])
            );
            if ($datafield && $datafield->getFieldType() === 'FieldModel') :
                /** @var AbstractCollection $className */
                $className = $datafield->getModel();
                /** @var AbstractCollection $model */
                $model = $className::findById($item->_($fieldName));

                return $model->getNameField($this->language->getShortCode());
            endif;
        endif;

        $return = $item->_($fieldName, $this->language->_('short'));
        if (is_array($return)) :
            return $return['name'][$this->language->_('short')];
        endif;

        return (string)$return;
    }

    protected function getFilename(string $extension): string
    {
        return (new DateTime())->format('Y-m-d-H-i-s') .
            '_' .
            'item' .
            '_' .
            $this->language->getLocale() .
            '.' . $extension;
    }
}
