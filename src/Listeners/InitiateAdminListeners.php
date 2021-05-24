<?php declare(strict_types=1);

namespace VitesseCms\Export\Listeners;

use Phalcon\Events\Manager;
use VitesseCms\Export\Controllers\AdmincontentController;
use VitesseCms\Export\Listeners\Admin\AdminMenuListener;
use VitesseCms\Export\Listeners\Controllers\AdmincontentControllerListener;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdmincontentController::class, new AdmincontentControllerListener());
    }
}
