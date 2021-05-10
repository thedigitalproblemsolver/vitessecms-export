<?php declare(strict_types=1);

namespace VitesseCms\Export\Models;

class ItemIdIterator extends ArrayIterator
{
    public function __construct(array $items)
    {
        parent::__construct($items);
    }

    public function current(): ItemId
    {
        return parent::current();
    }

    public function add(ItemId $item): void
    {
        $this->append($item);
    }
}
