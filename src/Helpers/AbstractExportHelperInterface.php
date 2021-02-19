<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\RepositoryInterface;
use VitesseCms\Language\Models\Language;

interface AbstractExportHelperInterface
{
    public static function buildAdminForm(ExportTypeForm $form, ExportType $item, RepositoryInterface $repositories): void;

    public function setFields(array $fields): void;

    public function setItems(array $items): void;

    public function setHeaders(): void;

    public function createOutput(): string;

    public function createOutputByIterator(
        ItemIterator $itemIterator,
        ExportType $exportType,
        UrlService $url
    ): string;

    public function setExportType(AbstractCollection $exportType): void;

    public function preFindAll(ExportType $exportType): void;
}
