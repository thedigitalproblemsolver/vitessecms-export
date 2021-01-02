<?php declare(strict_types=1);

namespace VitesseCms\Export\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Export\Helpers\AbstractExportHelperInterface;

class ExportType extends AbstractCollection
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $cachingTime;

    /**
     * @var string
     */
    public $datagroup;

    /**
     * @var ?string
     */
    public $getChildrenFrom;

    public function getCachingTime(): ?string
    {
        return $this->cachingTime;
    }

    public function hasCachingTime(): bool
    {
        return $this->cachingTime !== null && $this->cachingTime !== 'none';
    }

    public function getDatagroup(): string
    {
        return $this->datagroup??'';
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getGetChildrenFrom(): ?string
    {
        return $this->getChildrenFrom;
    }

    public function hasGetChildrenFrom(): ?bool
    {
        return $this->getChildrenFrom !== null;
    }

    public function hasType(): bool
    {
        return $this->type !== null;
    }

    public function getTypeClass(): string
    {
        if (substr_count($this->type, 'VitesseCms\\Export\\Helpers')) :
            return $this->type;
        endif;

        if (substr_count($this->type, 'Modules')) :
            return str_replace('Modules', 'VitesseCms', $this->type);
        endif;

        return 'VitesseCms\\Field\\Models\\' . $this->type;
    }

    public function afterFetch()
    {
        parent::afterFetch();

        if(AdminUtil::isAdminPage()) :
            //TODO move to mustache
            $this->adminListName = '<a 
                href="/export/index/index/'.(string)$this->getId().'" 
                class="fa fa-external-link"
                target="_blank"
                ></a> 
                <a 
                    href="/admin/export/admincontent/edit/'.(string)$this->getId().'"
                    class="openmodal"
                >'.
                    $this->getNameField().
                '</a>';
        endif;
    }
}
