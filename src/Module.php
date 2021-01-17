<?php declare(strict_types=1);

namespace VitesseCms\Export;

use VitesseCms\Communication\Services\MailchimpService;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Export\Repositories\ExportTypeRepository;
use VitesseCms\Export\Repositories\ItemRepository;
use VitesseCms\Export\Repositories\RepositoryCollection;
use VitesseCms\Export\Services\ChannelEngineService;
use Phalcon\DiInterface;
use VitesseCms\Language\Repositories\LanguageRepository;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        $di->setShared('channelEngine', new ChannelEngineService());
        $di->setShared('mailchimp', new MailchimpService(
            $di->get('session'),
            $di->get('setting'),
            $di->get('url'),
            $di->get('configuration')
        ));
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
