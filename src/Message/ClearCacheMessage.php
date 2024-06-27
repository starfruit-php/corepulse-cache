<?php

namespace CorepulseCacheBundle\Message;

class ClearCacheMessage
{

    private $id;
    private $create;

    public function __construct(int $id = null, bool $create = false)
    {
        $this->id = $id;
        $this->create = $create;

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreate(): bool
    {
        return $this->create;
    }

}
