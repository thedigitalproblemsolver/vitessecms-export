<?php

namespace VitesseCms\Export\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Export\Interfaces\AbstractExportHelperInterface;
use VitesseCms\Language\Models\Language;
use DateTime;

/**
 * Class AbstractAdminController
 */
abstract class AbstractExportController extends AbstractAdminController
{

    /**
     * @param array $fields
     * @param array $items
     * @param string $type
     */
    public function createExport(array $fields, array $items, string $type = 'csv'): void
    {
        /** @var Language $language */
        $language = Language::findById($this->request->get('language'));

        $helperClass = 'VitesseCms\\Export\\Helpers\\' . ucfirst($type) . 'ExportHelper';
        /** @var AbstractExportHelperInterface $exportHelper */
        $exportHelper = new $helperClass();
        $exportHelper->setFields($fields);
        $exportHelper->setItems($items);
        $exportHelper->setLanguage($language);
        $exportHelper->setHeaders();
        $exportHelper->createOutput((new DateTime())->format('Y-m-d-H-i-s') .
            '_'.
            \get_class($items[0]).
            '_'.
            $language->_('locale').
            '.'.$type
        );
    }
}
