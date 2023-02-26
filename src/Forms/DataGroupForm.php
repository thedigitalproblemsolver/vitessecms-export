<?php declare(strict_types=1);

namespace VitesseCms\Export\Forms;

use VitesseCms\Export\Repositories\RepositoriesInterface;
use VitesseCms\Form\AbstractFormWithRepository;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;
use VitesseCms\Form\Models\Attributes;

class DataGroupForm extends AbstractFormWithRepository
{
    public function buildForm(): FormWithRepositoryInterface
    {
        $this->addDropdown(
            '%ADMIN_DATAGROUP%',
            'datagroup',
            (new Attributes())
                ->setRequired(true)
                ->setInputClass('select2')
                ->setOptions(ElementHelper::modelIteratorToOptions(
                    $this->repositories->datagroup->findAll(null, false)
                ))
        )->addDropdown(
            '%ADMIN_LANGUAGE%',
            'language',
            (new Attributes())
                ->setRequired(true)
                ->setInputClass('select2')
                ->setOptions(ElementHelper::modelIteratorToOptions(
                    $this->repositories->language->findAll(null, false)
                ))
        )->addSubmitButton('%CORE_SAVE%');

        return $this;
    }
}
