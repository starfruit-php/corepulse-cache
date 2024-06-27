<?php

namespace CorepulseCacheBundle\Message;

class ExecutedCacheMessage
{

    private $url;
    private $view;
    private $tags;
    private $type;

    public function __construct(string $url = null, string $view = null, array $tags = [], string $type = null)
    {
        $this->url = $url;
        $this->view = $view;
        $this->tags = $tags;
        $this->type = $type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getType(): string
    {
        return $this->type;
    }

}
