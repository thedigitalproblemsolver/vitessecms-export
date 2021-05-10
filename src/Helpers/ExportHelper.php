<?php declare(strict_types=1);

namespace VitesseCms\Export\Helpers;

use VitesseCms\Form\AbstractForm;

class ExportHelper
{
    public static function getFieldsFromForm(AbstractForm $form): array
    {
        $fields = ['id', 'published', 'parentId', 'createdAt'];
        foreach ($form->getElements() as $element) :
            if (
                get_class($element) != 'Phalcon\Forms\Element\Submit'
                && get_class($element) != 'Phalcon\Forms\Element\Hidden'
            ) :
                $fields[] = $element->getName();
            endif;
        endforeach;

        return $fields;
    }
}
