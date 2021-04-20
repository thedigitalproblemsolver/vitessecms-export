<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use Exception;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Media\Helpers\ImageHelper;
use VitesseCms\Sef\Utils\UtmUtil;
use MongoDB\BSON\ObjectId;
use Phalcon\Filter;

class BeslistExportHelper extends AbstractExportHelper
{
    protected static $adminFormFields = [
        'titel',
        'merk',
        'prijs',
        'url',
        'url_productplaatje',
        'categorie',
        'levertijd',
        'beschrijving',
    ];
    protected $fields = [
        'titel',
        'merk',
        'prijs',
        'url',
        'url_productplaatje',
        'categorie',
        'levertijd',
        'beschrijving',
    ];

    public function createOutput(): string
    {
        ob_start();

        UtmUtil::setSource('beslist');
        UtmUtil::setMedium('productfeed');

        $output = fopen('php://output', 'w');
        fputcsv($output, $this->fields);
        /** @var AbstractCollection $item */
        //TODO verder uitwerken
        foreach ($this->items as $items) :
            foreach ($items as $item) :
                $row = [];
                foreach ($this->fields as $field) :
                    switch ($field) :
                        case 'url':
                            $row['url'] = UtmUtil::appendToUrl(
                                $this->url->getBaseUri() . $item->_('slug'),
                                false
                            );
                            break;
                        case 'url_productplaatje':
                            $row['url_productplaatje'] = ImageHelper::buildUrl($item->_('image'));
                            if ($item->_('firstImage')) :
                                $row['url_productplaatje'] = ImageHelper::buildUrl($item->_('firstImage'));
                            endif;
                            break;
                        case 'prijs':
                            $row[$field] = '';
                            if ($this->exportType->_('exportDatafield_' . $field)) :
                                $row = $this->addField(
                                    $row,
                                    $field,
                                    $item->_($this->exportType->_('exportDatafield_' . $field) . '_sale')
                                );
                            elseif ($this->exportType->_('exportField_' . $field)) :
                                $row = $this->addField($row, $field, $this->exportType->_('exportField_' . $field));
                            endif;
                            break;
                        default:
                            $row[$field] = '';
                            if ($this->exportType->_('exportDatafield_' . $field)) :
                                $callingName = $this->exportType->_('exportDatafield_' . $field);
                                try {
                                    new ObjectId($item->_($callingName));
                                    $datafieldItem = Item::findById($item->_($callingName));
                                    $row = $this->addField(
                                        $row,
                                        $field,
                                        $datafieldItem->_('name')
                                    );
                                } catch (Exception $e) {
                                    $row = $this->addField(
                                        $row,
                                        $field,
                                        $item->_($callingName)
                                    );
                                }
                            elseif ($this->exportType->_('exportField_' . $field)) :
                                $row = $this->addField(
                                    $row,
                                    $field,
                                    $this->exportType->_('exportField_' . $field)
                                );
                            endif;
                            break;
                    endswitch;
                endforeach;
                fputcsv($output, $row);
            endforeach;
        endforeach;
        UtmUtil::reset();

        return ob_get_flush();
    }

    protected function addField(array $row, string $field, string $value): array
    {
        $row[$field] = trim((new Filter())->sanitize(
            $value,
            'string'
        ));

        return $row;
    }

    public function setHeaders(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $this->getFilename('csv'));
    }
}
