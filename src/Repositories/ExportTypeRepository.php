<?php declare(strict_types=1);

namespace VitesseCms\Export\Repositories;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Database\Models\FindOrderIterator;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Models\ExportTypeIterator;

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

    public function findAll(?FindValueIterator $findValues = null): ExportTypeIterator {
        $this->parseFindValues($findValues);

        return new ExportTypeIterator(ExportType::findAll());
    }

    protected function parseFindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                ExportType::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }
}
