<?php declare(strict_types=1);

namespace VitesseCms\Export\Factories;

use VitesseCms\Content\Models\Item;
use Phalcon\Di;

class ChannelEngineFactory
{
    public static function createByItem(Item $item): array
    {
        if (empty($item->_('ean'))) :
            return [];
        endif;

        $products = [];
        $di = Di::getDefault();
        $baseUri = $di->get('url')->getBaseUri();
        $uploadUri = $di->get('configuration')->getUploadUri();
        $baseProduct = [
            'Name' => $item->_('name'),
            'Description' => '',
            'Brand' => 'CraftBeerShirts',
            'Price' => $item->_('price_sale'),
            'Url' => $baseUri . $item->_('slug'),
            'Category' => 'Clothing > Women > Tops & T-Shirts > T-Shirts',
            'CategoryTrail' => 'Clothing > Women > Tops & T-Shirts > T-Shirts',
        ];

        if ($item->_('variations')):
            $product = $baseProduct;
            $product += [
                'MerchantProductNo' => $item->_('ean'),
                'ImageUrl' => $uploadUri . $item->_('image'),
            ];
            $products[] = $product;

            foreach ($item->_('variations') as $variation) :
                if (!empty($variation['ean'])) :
                    $product = $baseProduct;
                    $color = explode('_', $variation['sku']);
                    $color = array_reverse($color);
                    unset($color[0]);
                    $color = array_reverse($color);

                    $imageUrl = '';
                    foreach ($item->_('variationsTemplate')['colors'] as $colorOptions) :
                        if ($colorOptions['color'] === $variation['color']) :
                            $imageUrl = $uploadUri . $colorOptions['image'];
                        endif;
                    endforeach;

                    $product += [
                        'MerchantProductNo' => $variation['ean'],
                        'ParentMerchantProductNo' => $item->_('ean'),
                        'Size' => $variation['size'],
                        'Color' => implode(' ', $color),
                        'Stock' => $variation['stock'],
                        'ImageUrl' => $imageUrl,
                        'ManufacturerProductNumber' => $variation['sku'],
                        'Ean' => $variation['ean']
                    ];

                    $products[] = $product;
                endif;

                /*echo '<pre>';
                var_dump($variation);
                var_dump($product);
                die();*/
            endforeach;
        else :
            $product = $baseProduct;
            $product += [
                'MerchantProductNo' => $item->_('ean'),
                'ImageUrl' => $uploadUri . $item->_('image'),
            ];
            $products[] = $product;
        endif;

        return $products;
    }
}

/*

[
  {
      "MerchantProductNo": "string",
    "ParentMerchantProductNo": "string",
    "ParentMerchantProductNo2": "string",
    "Name": "string",
    "Description": "string",
    "Brand": "string",
    "Size": "string",
    "Color": "string",
    "Ean": "string",
    "ManufacturerProductNumber": "string",
    "Stock": 0,
    "Price": 0,
    "MSRP": 0,
    "PurchasePrice": 0,
    "VatRateType": "STANDARD",
    "ShippingCost": 0,
    "ShippingTime": "string",
    "Url": "string",
    "ImageUrl": "string",
    "ExtraImageUrl1": "string",
    "ExtraImageUrl2": "string",
    "ExtraImageUrl3": "string",
    "ExtraImageUrl4": "string",
    "ExtraImageUrl5": "string",
    "ExtraImageUrl6": "string",
    "ExtraImageUrl7": "string",
    "ExtraImageUrl8": "string",
    "ExtraImageUrl9": "string",
    "CategoryTrail": "string",
    "ExtraData": [
      {
          "Key": "string",
        "Value": "string",
        "OverriddenValue": "string",
        "Type": "TEXT",
        "IsPublic": true
      }
    ]
  }
]*/
