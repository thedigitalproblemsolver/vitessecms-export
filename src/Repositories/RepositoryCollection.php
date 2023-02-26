<?php declare(strict_types=1);

namespace VitesseCms\Export\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

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

    /**
     * @var LanguageRepository
     */
    public $language;

    /**
     * @var DatagroupRepository
     */
    public $datagroup;

    /**
     * @var DatafieldRepository
     */
    public $datafield;

    public function __construct(
        ExportTypeRepository $exportTypeRepository,
        ItemRepository $itemRepository,
        LanguageRepository $languageRepository,
        DatagroupRepository $datagroupRepository,
        DatafieldRepository $datafieldRepository
    )
    {
        $this->exportType = $exportTypeRepository;
        $this->item = $itemRepository;
        $this->language = $languageRepository;
        $this->datagroup = $datagroupRepository;
        $this->datafield = $datafieldRepository;
    }
}
