<?php declare(strict_types=1);

namespace VitesseCms\Export\Models;

use ArrayIterator;

class ExportTypeIterator extends ArrayIterator
{
    public function __construct(array $products)
    {
        parent::__construct($products);
    }

    public function current(): ExportType
    {
        return parent::current();
    }
}
