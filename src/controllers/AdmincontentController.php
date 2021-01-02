<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\RepositoryInterface;

class AdmincontentController extends AbstractExportController implements RepositoryInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = ExportType::class;
        $this->classForm = ExportTypeForm::class;
    }
}
