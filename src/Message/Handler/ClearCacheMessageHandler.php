<?php

namespace CorepulseCacheBundle\Message\Handler;

use CorepulseCacheBundle\Message\ClearCacheMessage;
use CorepulseCacheBundle\Model\CorepulseCache;
use Pimcore\Tool\Storage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ClearCacheMessageHandler implements MessageHandlerInterface
{

    public function __invoke(ClearCacheMessage $clearCacheMessage)
    {

        $id = $clearCacheMessage->getId();
        $create = $clearCacheMessage->getCreate();

        $data = CorepulseCache::getById($id, 0);
        if ($data) {
            $storage = Storage::get('corepulse_cache');

            $url = $data->getUrl();
            $type = $data->getType();

            $storagePath = $url . "/index." . $type;

            $storage->delete($storagePath);

            if ($create) {

                $response = file_get_contents($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $url);

            }
        }
    }
}
