<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Interfaces\AbstractExportHelperInterface;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Language\Models\Language;
use Phalcon\Di;
use DateTime;

abstract class AbstractExportHelper extends AbstractInjectable implements AbstractExportHelperInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected static $adminFormFields = [];

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

    public function __construct()
    {
        $this->fields = [];
        $this->items = [];
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    public function setHeaders(): void
    {
    }

    public static function buildAdminForm(
        ExportTypeForm $form,
        ExportType $item
    ): void {
        if (
            $item->_('datagroup')
            && count($item->_('type')::$adminFormFields) > 0
        ) :
            $datafieldOptions = [];
            $datagroup = Datagroup::findById($item->_('datagroup'));
            foreach ($datagroup->_('datafields') as $datafieldSet) :
                if ($datafieldSet['published'] === true) :
                    $datafield = Datafield::findById($datafieldSet['id']);
                    if ($datafield) :
                        $datafieldOptions[$datafield->_('calling_name')] = $datafield->_('name');
                    endif;
                endif;
            endforeach;

            $form->_(
                'html',
                'html',
                'html',
                [
                    'html' => '<p><br/><b>Match Exportfields with custom text or a DataField</b></p>',
                ]
            );
            foreach ($item->_('type')::$adminFormFields as $exportField) :
                $form->_(
                    'select',
                    '%ADMIN_DATAFIELD% '.ucfirst($exportField),
                    'exportDatafield_'.$exportField,
                    [
                        'options'    => ElementHelper::arrayToSelectOptions($datafieldOptions),
                        'inputClass' => 'select2',
                    ]
                );

                $form->_(
                    'text',
                    '%CORE_OR%',
                    'exportField_'.$exportField,
                    [
                        'multilang' => true,
                    ]
                );
            endforeach;
        endif;
    }

    public function preFindAll(ExportType $exportType): void
    {
    }

    public function setExportType(AbstractCollection $exportType): void
    {
        $this->exportType = $exportType;
    }

    protected function getItemValue(AbstractCollection $item, string $fieldName): string
    {
        if (\get_class($item) === Item::class && !empty($item->_($fieldName))) :
            Datafield::setFindValue('calling_name', $fieldName);
            $datafield = Datafield::findFirst();
            if ($datafield && $datafield->_('type') === 'FieldModel') :
                /** @var AbstractCollection $className */
                $className = $datafield->_('model');
                /** @var AbstractCollection $model */
                $model = $className::findById($item->_($fieldName));

                return $model->_('name', $this->language->_('short'));
            endif;
        endif;

        $return = $item->_($fieldName, $this->language->_('short'));
        if (\is_array($return)) :
            return $return['name'][$this->language->_('short')];
        endif;

        return (string) $return;
    }

    protected function getFilename(string $extension): string
    {
        return (new DateTime())->format('Y-m-d-H-i-s').
            '_'.
            'item'.
            '_'.
            Di::getDefault()->get('configuration')->getLanguageLocale().
            '.'.$extension;
    }

    public function createOutputByIterator(ItemIterator $itemIterator, ExportType $exportType, UrlService $url): string
    {
        // TODO: Implement createOutputByIterator() method.
    }
}
