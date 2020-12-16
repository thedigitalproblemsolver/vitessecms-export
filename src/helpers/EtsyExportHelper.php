<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Shop\Helpers\EtsyHelper;
use Phalcon\Di;

/**
 * Class EtsyExportHelper
 */
class EtsyExportHelper extends AbstractExportHelper
{

    /**
     * {@inheritdoc}
     * @throws \Phalcon\Mvc\Collection\Exception
     *
     * http://new.craftbeershirts.nl/export/index/index/5ae0be1ca9fc77024018a8c2?embedded=1
     * @throws \OAuthException
     * @throws \Exception
     */
    public function createOutput(): string
    {
        $ids = [
            '5a0e1385583b4111a5084a02',
            '590ae59b8dd1773ab3442862',
        ];
        foreach ($ids as $id ) :
            $etsy = new EtsyHelper();

            $item = Item::findById($id);
            if(empty($item->_('etsyId'))) :
                $listing = $etsy->createListingFromItem($item);
                $item->set('etsyId', $listing->results[0]->listing_id);
                $item->save();

                if($item->_('variations')) :
                    $colorImages = [];
                    foreach((array)$item->_('variations') as $variation) :
                        if(!isset($colorImages[$variation['color']])) :
                            $colorImages[$variation['color']] = $variation['image'];
                        endif;
                    endforeach;

                    foreach ($colorImages as $colorImage) :
                        foreach ((array)$colorImage as $image) :
                            $etsy->addImageToListing(
                                Di::getDefault()->get('config')->get('uploadDir').$image,
                                (int)$item->_('etsyId')
                            );
                        endforeach;
                    endforeach;
                else :
                    $etsy->addImageToListing(
                        Di::getDefault()->get('config')->get('uploadDir').$item->_('firstImage'),
                        (int)$item->_('etsyId')
                    );
                endif;
            endif;
            echo '<pre>';
            //var_dump($etsy->getListing(603143686));
            var_dump($etsy->updateInventoryFromItem($item));
        endforeach;
die();
        return '';
    }
}
