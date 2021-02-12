<?php declare(strict_types=1);

namespace VitesseCms\Export\Listeners;

use Phalcon\Events\Manager;
use VitesseCms\Export\Controllers\AdmincontentController;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach(AdmincontentController::class, new AdmincontentControllerListener());
    }
}
