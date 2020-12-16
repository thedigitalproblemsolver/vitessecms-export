<?php declare(strict_types=1);

namespace VitesseCms\Export\Repositories;

use VitesseCms\Export\Interfaces\RepositoryInterface;

class RepositoryCollection implements RepositoryInterface
{
    /**
     * @var ExportTypeRepository
     */
    public $exportType;

    /**
     * @var ItemRepository
     */
    public $item;

    public function __construct(
        ExportTypeRepository $exportTypeRepository,
        ItemRepository $itemRepository
    )
    {
        $this->exportType = $exportTypeRepository;
        $this->item = $itemRepository;
    }
}
