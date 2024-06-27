<?php

namespace CorepulseCacheBundle\Model;

use Pimcore\Model\AbstractModel;
use Pimcore\Model\Exception\NotFoundException;

/**
 * @method bool isWriteable()
 * @method string getWriteTarget()
 */
class CorepulseCache extends AbstractModel
{

    public ?int $id = null;

    public ?string $url = null;

    public ?string $query = null;

    public ?string $tags = null;

    public ?string $type = null;

    public ?int $active = null;

    public $updateAt = null;

    /**
     * get score by id
     */
    public static function getById(int $id): ?self
    {
        try {
            $obj = new self;
            $obj->getDao()->getById($id);
            return $obj;
        } catch (NotFoundException $ex) {
            \Pimcore\Logger::warn("Vote with id $id not found");
        }

        return null;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setTags(?string $tags): void
    {
        $this->tags = $tags;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setActive(?string $active): void
    {
        $this->active = $active;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    public function setUpdateAt($updateAt): void
    {
        $this->updateAt = $updateAt;
    }

}
