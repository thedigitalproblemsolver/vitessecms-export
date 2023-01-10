<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use DateTime;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\RepositoryInterface;
use VitesseCms\Shop\Enums\AmazonEnum;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Media\Helpers\ImageHelper;
use VitesseCms\Shop\Enum\SizeAndColorEnum;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Models\Discount;

class AmazonExportHelper extends AbstractExportHelper
{
    /**
     * @var DiscountHelper
     */
    protected $discountService;

    public static function buildAdminForm(ExportTypeForm $form, ExportType $item, RepositoryInterface $repositories): void
    {
        parent::buildAdminForm($form, $item, $repositories);

        $form->addDropdown(
            'Amazon Browse Node',
            'AmazonBrowseNode',
            (new Attributes())->setRequired(true)->setOptions(ElementHelper::arrayToSelectOptions(
                AmazonEnum::nodes,
                [$item->_('AmazonBrowseNode')]
            ))
        );
    }

    public function setDiscountHelper(DiscountHelper $discountHelper): AbstractExportHelperInterface
    {
        $this->discountService = $discountHelper;
    }

    public function setHeaders(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $this->getFilename('csv'));
    }

    public function createOutput(): string
    {
        $output = fopen('php://output', 'b');
        foreach ($this->items as $items) :
            /** @var AbstractCollection $item */
            foreach ($items as $item) :
                $putParent = true;
                if (!empty($item->_('ean')) && !empty($item->_('variations'))) :
                    $gender = $this->repositories->item->getById($item->_('gender'));
                    $parent = $this->repositories->item->getById($item->getParentId());
                    foreach ($item->_('variations') as $variation) :
                        if (!empty($variation['ean']) && $variation['stock'] > 0) :
                            if ($putParent) :
                                fputcsv($output, $this->buildParentRow(
                                    $item,
                                    $gender->_('AmazonCatalogGender'),
                                    $parent
                                ));
                                $putParent = false;
                            endif;
                            fputcsv($output, $this->buildChildRow(
                                $item,
                                $variation,
                                $gender->_('AmazonCatalogGender'),
                                ImageHelper::buildUrl($variation['image'][0]),
                                $parent
                            ));
                        endif;
                    endforeach;
                endif;
            endforeach;
        endforeach;

        return '';
    }

    protected function buildParentRow(
        AbstractCollection $item,
        string $gender,
        Item $parent
    ): array
    {
        $return = [
            '',
            '',
            $parent->_('AmazonCatalogType'),
            $item->_('ean'),
            'CraftBeerShirts',
            trim($item->getNameField()),
            $parent->_('AmazonBrowseNode'),
            '', //Cotton
            '', //Black
            '', //Black
            '', //Large
            '', //Large
            '', //Cotton
            $gender,
            $item->_('price_sale'),
            10,
            'Specialized',
            'core',
            str_replace(
                'http://new.craftbeershirts.net',
                'https://craftbeershirts.net',
                ImageHelper::buildUrl($item->_('image'))
            ),
            '', //Image1
            '', //Image2
            '', //Image3
            'Parent',
            '',
            '',
            'SizeColor',
            'Update',
            $item->_('ean'),
            'EAN',
        ];
        //TODO move to shop and listener
        if (!empty($item->_('discount'))) :
            $discount = Discount::findById($item->_('discount')[0]);
            if ($discount && $this->discountService->isValid($discount)) :
                for ($i = 0; $i < 78; $i++) :
                    $return[] = '';
                endfor;
                $return[] = DiscountHelper::calculateFinalPrice($discount, $item->_('price_sale'));
                $return[] = (new DateTime())->modify('-1 day')->format('Y-m-d');
                $return[] = (new DateTime())->modify('+1 year')->format('Y-m-d');
            endif;
        endif;

        return $return;
    }

    protected function buildChildRow(
        AbstractCollection $item,
        array $variation,
        string $gender,
        string $image,
        Item $parent
    ): array
    {
        $size = $this->getSize($variation['size']);
        $material = 'Cotton';

        $color = array_reverse(explode('_', $variation['sku']));
        unset($color[0]);
        $color = implode('_', array_reverse($color));

        $return = [
            '',
            '',
            $parent->_('AmazonCatalogType'),
            $variation['ean'],
            'CraftBeerShirts',
            trim($item->_('name')),
            $parent->_('AmazonBrowseNode'),
            $material, //Cotton
            $this->getColorBySku($variation['sku']), //Black
            $color, //Black
            $size, //Large
            $size, //Large
            $material, //Cotton
            $gender,
            $item->_('price_sale'),
            $variation['stock'],
            'Specialized',
            'core',
            str_replace(
                'http://new.craftbeershirts.net',
                'https://craftbeershirts.net',
                $image
            ),
            '', //Image1
            '', //Image2
            '', //Image3
            'Child',
            $item->_('ean'),
            'Variation',
            'SizeColor',
            'Update',
            $variation['ean'],
            'EAN',
        ];

        //TODO move to shop and listener
        if (!empty($item->_('discount'))) :
            $discount = Discount::findById($item->_('discount')[0]);
            if ($discount && $this->discountService->isValid($discount)) :
                for ($i = 0; $i < 78; $i++) :
                    $return[] = '';
                endfor;
                $return[] = DiscountHelper::calculateFinalPrice($discount, $item->_('price_sale'));
                $return[] = (new DateTime())->modify('-1 day')->format('Y-m-d');
                $return[] = (new DateTime())->modify('+1 year')->format('Y-m-d');
            endif;
        endif;

        return $return;
    }

    protected function getSize(string $size): string
    {
        if (isset(SizeAndColorEnum::sizes[$size])) :
            return SizeAndColorEnum::sizes[$size];
        endif;

        die('Base size : ' . $size . ' unknow');

        /*
            Medium
            Large
            X-Large
            XX-Large
            XXX-Large
            XXXX-Large
            XXXXX-Large
         */
    }

    protected function getColorBySku(string $sku): string
    {
        $color = array_reverse(explode('_', $sku));
        unset($color[0]);
        $color = strtoupper(implode('_', array_reverse($color)));
        switch ($color) {
            case 'BLACK':
            case 'NEPPY_BLACK':
            case 'ASPHALT':
            case 'USED_BLACK':
            case 'USED-BLACK':
            case 'ZWART':
                return 'Black';
            case 'DIVA_BLUE':
            case 'NAVY':
            case 'ROYAL_BLUE':
            case 'HEATHER_BLUE':
            case 'TURQUOISE':
            case 'STONE-BLUE':
            case 'SKY':
                return 'Blue';
            case 'KHAKI':
            case 'KAKI':
                return 'Brown';
            case 'ASH-GREY':
            case 'GRAPHITE_GREY':
            case 'CHARCOAL_GREY':
            case 'GREY':
            case 'SPORTS-GREY':
            case 'HEATHER_GREY':
                return 'Grey';
            case 'KELLY_GREEN':
            case 'BOTTLE-GREEN':
                return 'Green';
            case 'ORANGE':
                return 'Orange';
            case 'RED':
            case 'ROOD':
                return 'Red';
            case 'WHITE':
                return 'White';
        }

        die('Base color : ' . $color . ' unknow');

        /*
            Beige

            Gold


            Multicoloured
            Off-White
            Orange
            Pink
            Purple

            Silver
            Turquoise

            Yellow
        */
    }

    public function preFindAll(ExportType $exportType): void
    {
        $parent = $this->repositories->item->findFirst((new FindValueIterator(
            [new FindValue('AmazonBrowseNode', $exportType->_('AmazonBrowseNode'))]
        )));

        if ($parent !== null) :
            Item::setFindValue('parentId', (string)$parent->getId());
        endif;
    }
}
