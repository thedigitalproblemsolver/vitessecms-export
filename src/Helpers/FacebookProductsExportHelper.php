<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use Exception;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Media\Helpers\ImageHelper;
use VitesseCms\Sef\Utils\UtmUtil;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Models\Discount;
use Phalcon\Filter;

class FacebookProductsExportHelper extends AbstractExportHelper
{
    protected static $adminFormFields = [
        'availability',
        'condition',
        'description',
        'title',
        'price',
        'brand',
        //'google_product_category',
    ];
    protected $fields = [
        'id',
        'gtin',
        'availability',
        'condition',
        'description',
        'image_link',
        'link',
        'title',
        'price',
        'sale_price',
        'brand',
        'age_group',
        'gender',
    ];

    public function createOutput(): string
    {
        ob_start();
        UtmUtil::setSource('facebook');
        UtmUtil::setMedium('shopping');

        $output = fopen('php://output', 'w');
        fputcsv($output, $this->fields);

        $discountService = new DiscountHelper();

        /** @var AbstractCollection $item */
        //TODO verder uitwerken
        foreach ($this->items as $items) :
            foreach ($items as $item) :
                $row = [];
                foreach ($this->fields as $field) :
                    switch ($field) :
                        case 'id':
                            $row['id'] = $item->getId();
                            break;
                        case 'age_group':
                            $row['age_group'] = 'adult';
                            break;
                        case 'gtin':
                            $row['gtin'] = $item->_('ean');
                            break;
                        case 'link':
                            $row['url'] = UtmUtil::appendToUrl(
                                $this->url->getBaseUri() . $item->_('slug'),
                                false
                            );
                            break;
                        case 'image_link':
                            $row['url_productplaatje'] = ImageHelper::buildUrl($item->_('image'));
                            if ($item->_('firstImage')) :
                                $row['url_productplaatje'] = ImageHelper::buildUrl($item->_('firstImage'));
                            endif;
                            break;
                        case 'price':
                            $row[$field] = '';
                            if ($this->exportType->_('exportDatafield_' . $field)) :
                                $price = $item->_($this->exportType->_('exportDatafield_' . $field) . '_sale');
                            elseif ($this->exportType->_('exportField_' . $field)) :
                                $price = $this->exportType->_('exportField_' . $field);
                            endif;

                            //TOOD move to shop and listener
                            $row = $this->addField($row, $field, $price);
                            if (!empty($item->_('discount'))) :
                                $discount = Discount::findById($item->_('discount')[0]);

                                if ($discount && $discountService->isValid($discount)) :
                                    $row = $this->addField(
                                        $row,
                                        'sale_price',
                                        (string)DiscountHelper::calculateFinalPrice(
                                            $discount,
                                            (float)$price
                                        )
                                    );
                                else :
                                    $row = $this->addField($row, 'sale_price', '');
                                endif;
                            else :
                                $row = $this->addField($row, 'sale_price', '');
                            endif;
                            break;
                        case 'sale_price':
                            break;
                        case 'description':
                            $description = (new Filter())->sanitize(
                                    $this->setting->get('SITE_LABEL_MOTTO'),
                                    'string'
                                ) . ' at ' .
                                $this->setting->get('WEBSITE_DEFAULT_NAME');

                            $row = $this->addField(
                                $row,
                                $field,
                                $description
                            );
                            break;
                        case 'title':
                            $title = $item->getNameField();
                            if ($item->_('gender')) :
                                $gender = $this->repositories->item->getById($item->_('gender'));
                                if ($gender) :
                                    $title .= ' - ' . $gender->getNameField();
                                endif;
                            endif;

                            if ($item->getParentId() !== null) {
                                $parent = $this->repositories->item->getById($item->getParentId());
                                if ($item->_('gender')) :
                                    $title = $item->getNameField() .
                                        ' - ' .
                                        $parent->getNameField() .
                                        ' - ' .
                                        $gender->getNameField();
                                else :
                                    $title = $item->getNameField() . ' - ' . $parent->getNameField();
                                endif;
                            }

                            $row = $this->addField($row, $field, $title);
                            break;
                        case 'gender':
                            if ($item->_('gender')) :
                                $gender = $this->repositories->item->getById($item->_('gender'));
                                $row = $this->addField(
                                    $row,
                                    $field,
                                    $gender->_('FacebookCatalogGender')
                                );
                            endif;
                            break;
                        default:
                            $row[$field] = '';
                            if ($this->exportType->_('exportDatafield_' . $field)) :
                                $callingName = $this->exportType->_('exportDatafield_' . $field);
                                try {
                                    new ObjectID($item->_($callingName));
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
                fputcsv($output, $row);
            endforeach;
        endforeach;
        UtmUtil::reset();

        return ob_get_clean();
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
        header('Content-Disposition: attachment; filename=' . $this->getFilename('xml'));
    }
}
