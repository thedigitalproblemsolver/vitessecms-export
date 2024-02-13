<?php
declare(strict_types=1);

namespace VitesseCms\Export\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Export\Enums\ExportTypeEnums;
use VitesseCms\Export\Listeners\Admin\AdminMenuListener;
use VitesseCms\Export\Repositories\ExportTypeRepository;

class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $injectable): void
    {
        if ($injectable->user->hasAdminAccess()) :
            $injectable->eventsManager->attach('adminMenu', new AdminMenuListener());
        endif;
        $injectable->eventsManager->attach(
            ExportTypeEnums::SERVICE_LISTENER->value,
            new ExportTypeListener(new ExportTypeRepository())
        );
    }
}
