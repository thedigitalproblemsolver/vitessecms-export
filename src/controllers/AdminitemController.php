<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use VitesseCms\Content\Forms\ItemForm;
use VitesseCms\Content\Models\Item;
use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Forms\DataGroupForm;
use VitesseCms\Export\Helpers\ExportHelper;
use VitesseCms\Export\Repositories\RepositoriesInterface;

class AdminitemController extends AbstractExportController implements RepositoriesInterface
{
    public function indexAction(): void
    {
        $this->view->setVar(
            'content',
            (new DataGroupForm())
                ->setRepositories($this->repositories)
                ->buildForm()
                ->renderForm(
                'admin/export/adminitem/createExport',
                'exportForm',
                true,
                true
            )
        );
        $this->prepareView();
    }

    public function createExportAction(): void
    {
        if ($this->request->isPost()) :
            $datagroup = $this->repositories->datagroup->getById($this->request->get('datagroup'));
            $datagroupFields = $datagroup->getDatafields();

            $items = $this->repositories->item->findAll(
                new FindValueIterator([new FindValue('datagroup', $this->request->get('datagroup'))]),
                false
            );

            $fields = ExportHelper::getFieldsFromForm(new ItemForm($items->current()));
            foreach ($fields as $key => $fieldName) :
                $datafield = $this->repositories->datafield->findFirst(
                    new FindValueIterator([new FindValue('calling_name', $fieldName)])
                );
                if (
                    $datafield !== null
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
                (array)$items
            );
        else :
            $this->redirect();
        endif;

        $this->view->disable();
    }
}
