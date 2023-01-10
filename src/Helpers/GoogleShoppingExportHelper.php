<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use LukeSnowden\GoogleShoppingFeed\Containers\GoogleShopping;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Sef\Utils\UtmUtil;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Models\Discount;
use VitesseCms\Shop\Models\Shipping;
use Phalcon\Di\Di;
use Phalcon\Filter;

class GoogleShoppingExportHelper extends AbstractExportHelper
{
    public function createOutput(): string
    {
        UtmUtil::setSource('google');
        UtmUtil::setMedium('shopping');

        GoogleShopping::title('Google Shopping - ' . $this->url->getBaseUri());
        GoogleShopping::link(
            str_replace(
                ['&amp;', '&'],
                ['&', '&amp;'],
                UtmUtil::appendToUrl($this->url->getBaseUri(), false)
            )
        );
        GoogleShopping::setIso4217CountryCode($this->setting->get('SHOP_CURRENCY_ISO'));

        $shipping = Shipping::findFirst();
        $discountService = new DiscountHelper();

        /** @var AbstractCollection $item */
        foreach ($this->items as $items) :
            foreach ($items as $item) :
                if (!$item->_('outOfStock')) :
                    $product = GoogleShopping::createItem();
                    /** @var \LukeSnowden\GoogleShoppingFeed\Item $product */
                    $product->id((string)$item->getId());
                    $title = $item->_('name');

                    //$description = trim((new Filter())->sanitize($item->_('introtext'), 'string'));
                    $description = '';
                    if (empty($description)) :
                        $description = (new Filter())->sanitize(
                                $this->setting->get('SITE_LABEL_MOTTO'),
                                'string'
                            ) . ' @ ' .
                            $this->setting->get('WEBSITE_DEFAULT_NAME');
                    endif;
                    $product->description($description);

                    //TODO move to shop and listener
                    $product->price($item->_('price_sale'));
                    if (!empty($item->_('discount'))) :
                        $discount = Discount::findById($item->_('discount')[0]);
                        if ($discount && $discountService->isValid($discount)) :
                            $product->sale_price(DiscountHelper::calculateFinalPrice(
                                $discount,
                                (float)$item->_('price_sale'))
                            );
                        endif;
                    endif;

                    $product->mpn($item->getId());
                    $product->link(str_replace(
                            ['&amp;', '&'],
                            ['&', '&amp;'],
                            UtmUtil::appendToUrl(
                                $this->url->getBaseUri() . $item->_('slug')
                                , false
                            )
                        )
                    );
                    $product->image_link(
                        $this->url->getBaseUri() .
                        'uploads/' .
                        Di::getDefault()->get('config')->get('account') .
                        '/' . $item->_('firstImage')
                    );
                    $product->condition('new');
                    $product->availability('in stock');
                    $product->brand('CraftBeerShirts');
                    if ($item->_('ean')):
                        $product->gtin($item->_('ean'));
                    endif;
                    $product->product_type('Kleding en accessoires > Kleding > Overhemden, shirts en bovenstukjes');
                    $product->identifier_exists('yes');
                    $product->google_product_category('212');
                    $product->age_group('adult');

                    if ($item->_('gender')) :
                        $gender = $this->repositories->item->getById($item->_('gender'));
                        if ($gender) :
                            $product->gender($gender->_('productfeedName'));
                            $title = $item->getNameField() . ' - ' . $gender->getNameField();
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
                    $product->title($title);

                    if ($shipping):
                        $product->shipping(
                            null,
                            null,
                            $shipping->_('costsDefaultWithVat') . ' ' . $this->setting->get('SHOP_CURRENCY_ISO')
                        );
                    endif;

                    if ($item->_('variations')) :
                        foreach ((array)$item->_('variations') as $variation) :
                            $variant = $product->variant();
                            $variant->size($variation['size']);
                            $variant->id($item->getId() . '_' . str_replace('&', '', $variation['sku']));
                            $variant->title($title . ' - ' . $variation['size']);
                            $variant->gtin($variation['ean']);

                            $sku = explode('_', $variation['sku']);
                            $sku = array_reverse($sku);
                            unset($sku[0]);
                            $sku = array_reverse($sku);

                            $variant->color(strtolower(implode(' ', $sku)));
                        endforeach;

                        $product->delete();
                    endif;
                endif;
            endforeach;
        endforeach;
        UtmUtil::reset();

        ini_set('memory_limit', '512M');

        return GoogleShopping::asRss();
    }
}
