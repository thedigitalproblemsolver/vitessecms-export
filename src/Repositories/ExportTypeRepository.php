<?php declare(strict_types=1);

namespace VitesseCms\Export\Repositories;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Database\Models\FindOrder;
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
        if (is_object($exportType)):
            return $exportType;
        endif;

        return null;
    }

    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true,
        ?int $limit = null,
        ?FindOrderIterator $findOrders = null
    ): ExportTypeIterator
    {
        ExportType::setFindPublished($hideUnpublished);
        if ($limit !== null) :
            ExportType::setFindLimit($limit);
        endif;
        if ($findOrders === null):
            $findOrders = new FindOrderIterator([new FindOrder('name', 1)]);
        endif;

        $this->parseFindValues($findValues);
        $this->parseFindOrders($findOrders);

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

    protected function parseFindOrders(?FindOrderIterator $findOrders = null): void
    {
        if ($findOrders !== null) :
            while ($findOrders->valid()) :
                $findOrder = $findOrders->current();
                ExportType::addFindOrder(
                    $findOrder->getKey(),
                    $findOrder->getOrder()
                );
                $findOrders->next();
            endwhile;
        endif;
    }
}
