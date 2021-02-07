<?php declare(strict_types=1);

namespace VitesseCms\Export\Repositories;

use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Database\Interfaces\BaseRepositoriesInterface;
use VitesseCms\Language\Repositories\LanguageRepository;

/**
 * Interface RepositoryInterface
 * @property ExportTypeRepository $exportType
 * @property ItemRepository $item
 * @property LanguageRepository $language
 * @property DatagroupRepository $datagroup
 * @property DatafieldRepository $datafield
 */
interface RepositoryInterface extends BaseRepositoriesInterface
{
}
