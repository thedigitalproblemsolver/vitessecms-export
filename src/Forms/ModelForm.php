<?php declare(strict_types=1);

namespace VitesseCms\Export\Forms;

use VitesseCms\Export\Repositories\RepositoryInterface;
use VitesseCms\Form\AbstractFormWithRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Core\Utils\SystemUtil;

class ModelForm extends AbstractFormWithRepository
{
    public function buildForm(): FormWithRepositoryInterface
    {
        $this->addDropdown(
            '%ADMIN_MODEL%',
            'model',
            (new Attributes())
                ->setRequired(true)
                ->setInputClass('select2')
                ->setOptions(ElementHelper::arrayToSelectOptions(SystemUtil::getModels()))
        )->addDropdown(
            '%ADMIN_LANGUAGE%',
            'language',
            (new Attributes())
                ->setRequired(true)
                ->setInputClass('select2')
                ->setOptions(ElementHelper::modelIteratorToOptions(
                    $this->repositories->language->findAll(null, false)
                )
                )
        )
            ->addDate('From', 'date_from')
            ->addDate('Till', 'date_till')
            ->addSubmitButton('%CORE_SAVE%');

        return $this;
    }
}
