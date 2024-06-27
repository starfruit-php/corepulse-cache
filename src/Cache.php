<?php

namespace CorepulseCacheBundle;

use CorepulseCacheBundle\Message\ClearCacheMessage;
use CorepulseCacheBundle\Message\ExecutedCacheMessage;
use CorepulseCacheBundle\Model\CorepulseCache;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Pimcore\Tool\Storage;

class Cache
{
    private $bus;
    private $transportName;

    const PARENT_NOT = [
        '/admin',
        '/cms/document/edit-mode'
    ];

    public function __construct(
        MessageBusInterface $bus,
    ) {
        $this->bus = $bus;

        $transportName = $_ENV['COPULSECACHE_TRANSPORT_NAME'];

        // Kiểm tra xem giá trị có phải là 'sync' hoặc 'async' hay không
        if ($transportName !== 'sync' && $transportName !== 'async') {
            $this->transportName = 'sync';
        }else{
            $this->transportName = $transportName;
        }
        
    }

    // tạo cache theo url
    public function create(string $url, string $view, array $tags, string $type)
    {
        foreach (self::PARENT_NOT as $parent) {
            if (strpos($url, $parent) === 0) {
                return false;
            }
        }
        return $this->executedCache($url, $view, $tags, $type);
    }

    // tạo lại cache theo tags
    public function createByTags(array $tags)
    {
        if(count($tags) == 0) return false;

        return $this->executedTagsCache($tags, true);
    }
   
    // xóa cache theo tags
    public function clearByTags(array $tags)
    {
        if(count($tags) == 0) return false;
        
        return $this->executedTagsCache($tags, false);
    }

    // xóa cache
    public function clearAll()
    {
        $storagePath = '/';

        $storage = Storage::get('corepulse_cache');

        if($storage->directoryExists($storagePath)){
            
            $storage->deleteDirectory($storagePath);
        }

        return true;
    }


    
    private function executedTagsCache(array $tags, bool $create = fasle)
    {
        $data = new CorepulseCache\Listing();
        $data->addConditionParam('active = 1');
        $where = null;
        $params = [];

        if (count($tags) > 0) {
            $where = "(" . implode(" OR ", array_fill(0, count($tags), "tags LIKE ?")) . ")";
            $params = array_map(function($tag) {
                return "%$tag%";
            }, $tags);
        }

        if($where && count($params) > 0){
            $data->addConditionParam($where, $params);
        }

        if ($data->count() > 0) {
            foreach ($data as $item) {
                $id = $item->getId();
                $this->bus->dispatch(new ClearCacheMessage($id, $create), [new TransportNamesStamp($this->transportName)]);
            }
        }

        return true;

    }

    private function executedCache($url, $view, $tags, $type)
    {

        $this->bus->dispatch(new ExecutedCacheMessage($url, $view, $tags, $type), [new TransportNamesStamp($this->transportName)]);

        return true;
    }

}
