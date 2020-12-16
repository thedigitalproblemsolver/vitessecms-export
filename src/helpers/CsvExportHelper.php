<?php

namespace VitesseCms\Export\Helpers;

use VitesseCms\Database\AbstractCollection;

/**
 * Class CsvExportHelper
 */
class CsvExportHelper extends AbstractExportHelper
{
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function setHeaders(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$this->getFilename('csv'));
    }

    /**
     * {@inheritdoc}
     */
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
