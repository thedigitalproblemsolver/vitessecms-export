<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use VitesseCms\Admin\Interfaces\AdminModelAddableInterface;
use VitesseCms\Admin\Interfaces\AdminModelCopyableInterface;
use VitesseCms\Admin\Interfaces\AdminModelDeletableInterface;
use VitesseCms\Admin\Interfaces\AdminModelEditableInterface;
use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Admin\Interfaces\AdminModelListInterface;
use VitesseCms\Admin\Interfaces\AdminModelPublishableInterface;
use VitesseCms\Admin\Traits\TraitAdminModelAddable;
use VitesseCms\Admin\Traits\TraitAdminModelCopyable;
use VitesseCms\Admin\Traits\TraitAdminModelDeletable;
use VitesseCms\Admin\Traits\TraitAdminModelEditable;
use VitesseCms\Admin\Traits\TraitAdminModelList;
use VitesseCms\Admin\Traits\TraitAdminModelPublishable;
use VitesseCms\Admin\Traits\TraitAdminModelSave;
use VitesseCms\Core\AbstractControllerAdmin;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Models\FindOrder;
use VitesseCms\Database\Models\FindOrderIterator;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Export\Enums\ExportTypeEnums;
use VitesseCms\Export\Forms\ExportTypeForm;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Export\Repositories\ExportTypeRepository;
use VitesseCms\Export\Repositories\RepositoryInterface;

class AdmincontentController  extends AbstractControllerAdmin implements
    AdminModelPublishableInterface,
    AdminModelListInterface,
    AdminModelEditableInterface,
    AdminModelDeletableInterface,
    AdminModelAddableInterface,
    AdminModelCopyableInterface
{
    use TraitAdminModelPublishable,
        TraitAdminModelList,
        TraitAdminModelEditable,
        TraitAdminModelSave,
        TraitAdminModelDeletable,
        TraitAdminModelAddable,
        TraitAdminModelCopyable
        ;

    private readonly ExportTypeRepository $exportTypeRepository;

    public function onConstruct()
    {
        parent::onConstruct();

        $this->exportTypeRepository = $this->eventsManager->fire(ExportTypeEnums::GET_REPOSITORY->value, new \stdClass());
    }

    public function getModel(string $id): ?AbstractCollection
    {
        return match ($id) {
            'new' => new ExportType(),
            default => $this->exportTypeRepository->getById($id, false)
        };
    }

    public function getModelList( ?FindValueIterator $findValueIterator): \ArrayIterator
    {
        return $this->exportTypeRepository->findAll(
            $findValueIterator,
            false,
            99999,
            new FindOrderIterator([new FindOrder('createdAt', -1)])
        );
    }

    public function getModelForm(): AdminModelFormInterface
    {
        return new ExportTypeForm();
    }
}
