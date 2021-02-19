<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use VitesseCms\Database\AbstractCollection;

class CsvExportHelper extends AbstractExportHelper
{
    protected $output;

    public function setHeaders(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $this->getFilename('csv'));
    }

    public function createOutput(): string
    {
        ob_start();
        $this->output = fopen('php://output', 'w');
        fputcsv($this->output, $this->fields);
        /** @var AbstractCollection $item */
        foreach ($this->items as $item) :
            $row = [];
            foreach ($this->fields as $fieldName) :
                switch ($fieldName) :
                    case 'id':
                        $row[] = $item->getId();
                        break;
                    default:
                        $row[] = $this->getItemValue($item, $fieldName);
                        break;
                endswitch;
            endforeach;
            fputcsv($this->output, $row);
        endforeach;

        return ob_get_flush();
    }
}
