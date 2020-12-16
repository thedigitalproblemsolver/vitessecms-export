<?php

namespace VitesseCms\Export\Controllers;

use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;

/**
 * Class DatagroupController
 */
class AdmincontentController extends AbstractExportController
{
    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = ExportType::class;
        $this->classForm = ExportTypeForm::class;
    }
}
