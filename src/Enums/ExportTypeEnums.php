<?php declare(strict_types=1);

namespace VitesseCms\Export\Enums;

enum ExportTypeEnums: string
{
    case SERVICE_LISTENER = 'ExportTypeListener';
    case GET_REPOSITORY = 'ExportTypeListener:getRepository';
}
