<?php declare(strict_types=1);

namespace VitesseCms\Export\Listeners;

use Phalcon\Events\Manager;
use VitesseCms\Export\Listeners\Admin\AdminMenuListener;

class InitiateListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
    }
}
