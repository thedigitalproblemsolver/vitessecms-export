<?php declare(strict_types=1);

namespace VitesseCms\Export\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Export\Controllers\AdmincontentController;
use VitesseCms\Export\Enums\ExportTypeEnums;
use VitesseCms\Export\Listeners\Admin\AdminMenuListener;
use VitesseCms\Export\Listeners\Controllers\AdmincontentControllerListener;
use VitesseCms\Export\Repositories\ExportTypeRepository;

class InitiateAdminListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        $di->eventsManager->attach('adminMenu', new AdminMenuListener());
        $di->eventsManager->attach(AdmincontentController::class, new AdmincontentControllerListener());
        $di->eventsManager->attach(ExportTypeEnums::SERVICE_LISTENER->value, new ExportTypeListener(new ExportTypeRepository()));
    }
}
