<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use DateTime;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Export\Helpers\AbstractExportHelperInterface;
use VitesseCms\Export\Repositories\RepositoriesInterface;
use VitesseCms\Language\Models\Language;
use function get_class;

abstract class AbstractExportController extends AbstractAdminController implements RepositoriesInterface
{
    public function createExport(array $fields, array $items, string $type = 'csv'): void
    {
        $language = $this->repositories->language->getById($this->request->get('language'));

        $helperClass = 'VitesseCms\\Export\\Helpers\\' . ucfirst($type) . 'ExportHelper';
        /** @var AbstractExportHelperInterface $exportHelper */
        $exportHelper = new $helperClass($language, $this->repositories);
        $exportHelper->setFields($fields);
        $exportHelper->setItems($items);
        $exportHelper->setHeaders();
        $exportHelper->createOutput(
            (new DateTime())->format('Y-m-d-H-i-s') .
            '_' .
            get_class($items[0]) .
            '_' .
            $language->getLocale() .
            '.' . $type
        );
    }
}
