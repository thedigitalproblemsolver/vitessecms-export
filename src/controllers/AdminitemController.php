<?php

namespace VitesseCms\Export\Controllers;

use VitesseCms\Content\Forms\ItemForm;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Export\Forms\DataGroupForm;
use VitesseCms\Export\Helpers\ExportHelper;

/**
 * Class AdminItemController
 */
class AdminitemController extends AbstractExportController
{
    /**
     * core action
     */
    public function indexAction(): void
    {
        $this->view->setVar(
            'content',
            (new DataGroupForm())->renderForm(
                'admin/export/adminitem/createExport',
                'exportForm',
                true,
                true
            )
        );
        $this->prepareView();
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function createExportAction(): void
    {
        if ($this->request->isPost()) :
            $datagroup = Datagroup::findById($this->request->get('datagroup'));
            $datagroupFields = $datagroup->_('datafields');

            Item::setFindPublished(false);
            Item::setFindValue('datagroup', $this->request->get('datagroup') );
            /** @var AbstractCollection[] $items */
            $items = Item::findAll();

            $fields = ExportHelper::getFieldsFromForm(new ItemForm($items[0]));
            foreach ($fields as $key => $fieldName) :
                Datafield::setFindValue('calling_name' , $fieldName);
                $datafield = Datafield::findFirst();
                if(
                    $datafield
                    && (
                        !isset($datagroupFields[(string)$datafield->getId()]['exportable'])
                        || !$datagroupFields[(string)$datafield->getId()]['exportable']
                    )
                ) :
                    unset($fields[$key]);
                endif;
            endforeach;

            $this->createExport(
                $fields,
                $items
            );
        else :
            $this->redirect();
        endif;

        $this->view->disable();
    }
}
