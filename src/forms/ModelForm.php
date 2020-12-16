<?php declare(strict_types=1);

namespace VitesseCms\Export\Forms;

use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Language\Models\Language;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Form\AbstractForm;

class ModelForm extends AbstractForm
{
    public function initialize(): void
    {
        Language::setFindPublished(false);

        $this->addDropdown(
            '%ADMIN_MODEL%',
            'model',
            (new Attributes())
                ->setRequired(true)
                ->setInputClass('select2')
                ->setOptions(ElementHelper::arrayToSelectOptions(SystemUtil::getModels()))
        )
            ->addDropdown(
                '%ADMIN_LANGUAGE%',
                'language',
                (new Attributes())
                    ->setRequired(true)
                    ->setInputClass('select2')
                    ->setOptions(ElementHelper::arrayToSelectOptions(Language::findAll()))
            )
            ->addDate('From', 'date_from')
            ->addDate('Till', 'date_till')
            ->_('submit', '%CORE_SAVE%')
        ;
    }
}
