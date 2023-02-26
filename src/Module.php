<?php declare(strict_types=1);

namespace VitesseCms\Export;

use VitesseCms\Communication\Services\MailchimpService;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Export\Repositories\ExportTypeRepository;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Export\Repositories\RepositoryCollection;
use VitesseCms\Export\Services\ChannelEngineService;
use Phalcon\Di\DiInterface;
use VitesseCms\Language\Repositories\LanguageRepository;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        $di->setShared('repositories', new RepositoryCollection(
            new ExportTypeRepository(),
            new ItemRepository(),
            new LanguageRepository(),
            new DatagroupRepository(),
            new DatafieldRepository()
        ));

        parent::registerServices($di, 'Export');
    }
}
