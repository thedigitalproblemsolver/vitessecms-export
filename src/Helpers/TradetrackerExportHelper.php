<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use Exception;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Sef\Utils\UtmUtil;
use MongoDB\BSON\ObjectId;
use Phalcon\Di;
use Phalcon\Filter;

class TradetrackerExportHelper extends AbstractExportHelper
{
    protected static $adminFormFields = [
        'name',
        'description',
        'categoryPath',
        'categories',
        'subcategories',
        'subsubcategories',
        'material',
        'price',
        'fromPrice',
        'discount',
        'gender',
        'brand',
        'sale',
        'deliveryTime',
        'deliveryCosts',
        'EAN',
    ];
    protected $fields = [
        'ID',
        'name',
        'description',
        'productURL',
        'categoryPath',
        'categories',
        'subcategories',
        'subsubcategories',
        'material',
        'price',
        'fromPrice',
        'discount',
        'gender',
        'brand',
        'sale',
        'deliveryTime',
        'deliveryCosts',
        'EAN',
    ];

    public function createOutput(): string
    {
        ob_start();
        UtmUtil::setSource('tradetracker');
        UtmUtil::setMedium('productfeed');

        $output = fopen('php://output', 'w');
        fputcsv($output, $this->fields);

        /** @var AbstractCollection $item */
        //TODO verder uitwerken
        foreach ($this->items as $item) :
            $row = [];
            foreach ($this->fields as $field) :
                switch ($field) :
                    case 'ID':
                        $row['ID'] = (string)$item->getId();
                        break;
                    case 'productURL':
                        $row['productURL'] = UtmUtil::appendToUrl(
                            $this->url->getBaseUri() . $item->_('slug'),
                            false
                        );
                        break;
                    case 'price':
                    case 'fromPrice':
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
                                $datafieldItem = $this->repositories->item->getById($item->_($callingName));
                                $row = $this->addField(
                                    $row,
                                    $field,
                                    $datafieldItem->getNameField()
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

            //TODO move to shop and listeners
            if ($item->_('variations')) :
                $colors = $sizes = $stockSizes = $ean = [];
                $image = '';
                $stockTotal = 0;
                foreach ($item->_('variations') as $variation) :
                    if ($variation['stock'] > 0):
                        $sku = explode('_', $variation['sku']);
                        $color = strtolower($sku[0]);

                        if (!in_array($color, $colors, true)) :
                            $colors[] = $color;
                            $sizes[$color] = [];
                            $stockSizes[$color] = [];
                            $ean[$color] = [];
                        endif;

                        $sizes[$color][] = $variation['size'];
                        $stockSizes[$color][] = $variation['stock'];
                        $image = $this->url->getBaseUri() .
                            'uploads/' .
                            Di::getDefault()->get('config')->get('account') .
                            '/' . $variation['image'];
                        $ean[$color][] = $variation['ean'];
                        $stockTotal += $variation['stock'];

                    endif;
                endforeach;

                foreach ($colors as $color) :
                    $row['imageURL'] = $image;
                    $row['color'] = $color;
                    $row['size'] = implode('|', $sizes[$color]);
                    $row['sizeStock'] = implode('|', $stockSizes[$color]);
                    $row['stock'] = $stockTotal;
                    $row['EAN'] = implode('|', $ean[$color]);
                    fputcsv($output, $row);
                endforeach;
            else :
                $fields['EAN'] = $item->_('ean');
                fputcsv($output, $row);
            endif;
        endforeach;
        UtmUtil::reset();

        return ob_get_flush();
    }

    protected function addField(array $row, string $field, string $value): array
    {
        $row[$field] = trim((new Filter())->sanitize($value, 'string'));

        return $row;
    }

    public function setHeaders(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $this->getFilename('csv'));
    }
}
