<?php

namespace CorepulseCacheBundle\Message\Handler;

use Carbon\Carbon;
use CorepulseCacheBundle\Message\ExecutedCacheMessage;
use CorepulseCacheBundle\Model\CorepulseCache;
use Pimcore\Tool\Storage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecutedCacheMessageHandler implements MessageHandlerInterface
{

    public function __invoke(ExecutedCacheMessage $executedCacheMessage)
    {

        $url = $executedCacheMessage->getUrl();
        $view = $executedCacheMessage->getView();
        $tags = $executedCacheMessage->getTags();
        $type = $executedCacheMessage->getType();
        $rowCache = null;

        $data = new CorepulseCache\Listing();
        $data->addConditionParam('url = ?', [$url]);

        if ($data->current()) {
            $rowCache = $data->current();
        }

        if ($rowCache && $rowCache->getActive() != 1) {
            return;
        }

        // dd($tags);
        $storage = Storage::get('corepulse_cache');

        if (!$type || ($type && !in_array($type, ['html, json']))) {
            $type = "html";
        }

        $storagePath = $url . "/index." . $type;

        if ($_ENV['COPULSECACHE_CDN'] && $_ENV['COPULSECACHE_OLD_DOMAIN']) {
            $domain = $_ENV['COPULSECACHE_CDN'];
            $old_domain = $_ENV['COPULSECACHE_OLD_DOMAIN'];

            $view = preg_replace('/' . preg_quote($old_domain, '/') . '/', '', $view);

            // Replace image URLs
            $view = preg_replace('/<img\s+(?:[^>]*?\s+)?src="(\/[^"]*)"/i', '<img src="' . $domain . '$1"', $view);

            // Replace script URLs
            $view = preg_replace('/<script\s+(?:[^>]*?\s+)?src="(\/[^"]*)"/i', '<script src="' . $domain . '$1"', $view);

            // Replace CSS URLs
            $view = preg_replace('/<link\s+(?:[^>]*?\s+)?href="(\/[^"]*)"/i', '<link href="' . $domain . '$1"', $view);

            // Replace background image URLs in inline CSS
            $view = preg_replace('/url\((\/[^)]*)\)/i', 'url(' . $domain . '$1)', $view);
        }

        $storage->write($storagePath, $view);

        if ($rowCache) {

            $rowCache->setUpdateAt(Carbon::now());
        } else {

            $rowCache = new CorepulseCache();
            $rowCache->setUrl($url);
            $rowCache->setTags("," . implode(',', $tags) . ",");
            $rowCache->setType($type);
            $rowCache->setActive(1);
        }

        $rowCache->save();
    }
}
