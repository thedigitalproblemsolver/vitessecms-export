<?php declare(strict_types=1);

namespace VitesseCms\Export\Repositories;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;

class ItemRepository
{
    public function getItemIdsByDatagroupForExportType(
        string $datagroupId,
        string $exportTypeId
    ): ItemIterator {
        Item::setFindValue('datagroup', $datagroupId);
        Item::addFindOrder('createdAt', -1);
        Item::setFindValue('excludeFromExport', ['$nin' => [$exportTypeId]]);
        Item::setFindLimit(1);
        Item::setReturnFields(['_id']);

        return new ItemIterator(Item::findAll());
    }

    public function appendRecursiveChildrenForExportType(
        string $parentId,
        ItemIterator $itemIterator
    ): ItemIterator {
        Item::setFindValue('parentId', $parentId);
        Item::setReturnFields(['hasChildren' => true]);
        Item::setFindLimit(9999);
        $items = Item::findAll();
        foreach ($items as $item):
            $itemIterator->add($item);
            if($item->hasChildren()) :
                $itemIterator = $this->appendRecursiveChildrenForExportType(
                    (string) $item->getId(),
                    $itemIterator
                );
            endif;
        endforeach;

        return $itemIterator;
    }
}
