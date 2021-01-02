<?php declare(strict_types=1);

namespace VitesseCms\Export\Models;

class ItemId
{
    /**
     * @var string
     */
    protected $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
