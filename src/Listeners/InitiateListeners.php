<?php declare(strict_types=1);

namespace VitesseCms\Export\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Export\Enums\ExportTypeEnums;
use VitesseCms\Export\Listeners\Admin\AdminMenuListener;
use VitesseCms\Export\Repositories\ExportTypeRepository;

class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        if($di->user->hasAdminAccess()) :
            $di->eventsManager->attach('adminMenu', new AdminMenuListener());
        endif;
        $di->eventsManager->attach(ExportTypeEnums::SERVICE_LISTENER->value, new ExportTypeListener(new ExportTypeRepository()));
    }
}
