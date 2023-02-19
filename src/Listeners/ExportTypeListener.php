<?php declare(strict_types=1);

namespace VitesseCms\Export\Listeners;

use VitesseCms\Export\Repositories\ExportTypeRepository;

class ExportTypeListener
{
    public function __construct(readonly ExportTypeRepository $exportTypeRepository){}

    public function getRepository(): ExportTypeRepository
    {
        return $this->exportTypeRepository;
    }
}