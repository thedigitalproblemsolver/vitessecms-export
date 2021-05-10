<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Export\Forms\ModelForm;
use VitesseCms\Export\Helpers\ExportHelper;
use VitesseCms\Export\Helpers\AbstractExportHelperInterface;

class AdminmodelController extends AbstractExportController
{
    public function indexAction(): void
    {
        $this->view->setVar(
            'content',
            (new ModelForm())
                ->setRepositories($this->repositories)
                ->buildForm()
                ->renderForm(
                    'admin/export/adminmodel/createExport',
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
            $className = SystemUtil::createNamespaceFromPath($this->request->get('model'));
            /** @var AbstractCollection $className */
            $className::setFindLimit(9999);
            if (
                !empty($this->request->get('date_from'))
                && empty($this->request->get('date_till'))
            ) :
                $className::setFindValue('createdAt', $this->request->get('date_from'), 'greater');
            endif;

            if (
                !empty($this->request->get('date_till'))
                && empty($this->request->get('date_from'))
            ) :
                $className::setFindValue('createdAt', $this->request->get('date_till'), 'smaller');
            endif;

            if (
                !empty($this->request->get('date_from'))
                && !empty($this->request->get('date_till'))
            ):
                $className::setFindValue(
                    'createdAt',
                    [$this->request->get('date_from'), $this->request->get('date_till')],
                    'between'
                );
            endif;

            /** @var AbstractCollection[] $items */
            $items = $className::findAll();

            /** @var AbstractForm $form */
            $formClass = SystemUtil::getFormclassFromClass($className);

            $this->createExport(
                ExportHelper::getFieldsFromForm(new $formClass($items[0])),
                $items
            );
        else :
            $this->redirect();
        endif;

        $this->view->disable();
    }
}
