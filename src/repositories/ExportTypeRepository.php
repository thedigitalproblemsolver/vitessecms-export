<?php declare(strict_types=1);

namespace VitesseCms\Export\Repositories;

use VitesseCms\Export\Models\ExportType;

class ExportTypeRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?ExportType
    {
        ExportType::setFindPublished($hideUnpublished);

        /** @var ExportType $exportType */
        $exportType = ExportType::findById($id);
        if(is_object($exportType)):
            return $exportType;
        endif;

        return null;
    }
}
