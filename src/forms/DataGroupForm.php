<?php

namespace VitesseCms\Export\Forms;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Language\Models\Language;
use VitesseCms\Form\AbstractForm;

/**
 * Class DataGroupForm
 */
class DataGroupForm extends AbstractForm
{

    public function initialize()
    {
        Datagroup::setFindPublished(false);
        $this->_(
            'select',
            '%ADMIN_DATAGROUP%',
            'datagroup',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions(Datagroup::findAll()),
                'inputClass' => 'select2'
            ]
        );

        Language::setFindPublished(false);
        $this->_(
            'select',
            '%ADMIN_LANGUAGE%',
            'language',
            [
                'required' => 'required',
                'options'  => ElementHelper::arrayToSelectOptions(Language::findAll()),
                'inputClass' => 'select2',
            ]
        );

        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
