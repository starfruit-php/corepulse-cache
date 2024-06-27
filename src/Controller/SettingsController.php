<?php
declare (strict_types = 1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CorepulseCacheBundle\Controller;

use CorepulseCacheBundle\Model\CorepulseCache;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use CorepulseCacheBundle\Message\ClearCacheMessage;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Pimcore\Tool\Storage;
use CorepulseCacheBundle\Cache;

class SettingsController extends UserAwareController
{
    use JsonHelperTrait;

    public function __construct(
        MessageBusInterface $bus,
    ) {
        $this->bus = $bus;
    }

    /**
     * @Route("/settings", name="corepulsecache_settings", methods={"POST"}, options={"expose"=true})
     *
     */
    public function settingsAction(Request $request): JsonResponse
    {
        if ($request->get('data')) {
            // $this->checkPermission('routes');

            $data = $this->decodeJson($request->get('data'));

            if (is_array($data)) {
                foreach ($data as &$value) {
                    if (is_string($value)) {
                        $value = trim($value);
                    }
                }
            }

            if ($request->get('xaction') == 'destroy') {
                $data = $this->decodeJson($request->get('data'));
                $id = $data['id'];
                $cache = CorepulseCache::getById($id);
                // if (!$cache->isWriteable()) {
                //     throw new ConfigWriteException();
                // }
                $this->bus->dispatch(new ClearCacheMessage($id, false), [new TransportNamesStamp('sync')]);
                $cache->delete();

                

                return $this->jsonResponse(['success' => true, 'data' => []]);
            } elseif ($request->get('xaction') == 'update') {
                // save routes
                $cache = CorepulseCache::getById($data['id']);
                // if (!$cache->isWriteable()) {
                //     throw new ConfigWriteException();
                // }

                $cache->setValues($data);

                $cache->save();

                return $this->jsonResponse(['data' => $cache->getObjectVars(), 'success' => true]);
            } elseif ($request->get('xaction') == 'create') {
                // if (!(new Staticroute())->isWriteable()) {
                //     throw new ConfigWriteException();
                // }
                unset($data['id']);

                // save route
                $cache = new CorepulseCache();
                $cache->setValues($data);

                $cache->save();

                $responseData = $cache->getObjectVars();
                $responseData['writeable'] = true;

                return $this->jsonResponse(['data' => $responseData, 'success' => true]);
            }
        } else {
            // get list of routes

            $list = new CorepulseCache\Listing();

            if ($filter = $request->get('filter')) {
                $list->addConditionParam('url LIKE :filter OR tags LIKE :filter', ['filter' => '%' . $filter . '%']);
            }

            $offset = (int) $request->get('start', 0);
            $limit = (int) $request->get('limit', 50);

            $list->setOffset($offset);
            $list->setLimit($limit);

            

            if($request->get('sort')){
                $sort = json_decode($request->get('sort'), true)[0];

                $orderKey = $sort['property'];
                $order = $sort['direction'];
                // dd($orderKey, $order);
                $list->setOrderKey($orderKey);
                $list->setOrder($order);
            }
                
            $storage = Storage::get('corepulse_cache');



            $caches = [];
            foreach ($list as $cacheFromList) {
                $cache = $cacheFromList->getObjectVars();
                $cache['writeable'] = true;

                $url = $cache['url'];
                $type = $cache['type'];
                
                $storagePath = $url . "/index." . $type;
                
                $cache['status'] =  $storage->has($storagePath);
                
                $caches[] = $cache;
            }

            return $this->jsonResponse(['data' => $caches, 'success' => true, 'total' => $list->getTotalCount()]);
        }

        return $this->jsonResponse(['success' => false]);
    }

    /**
     * @Route("/clear-all", name="corepulsecache_clear_all", methods={"POST"}, options={"expose"=true})
     *
     */
    public function clearAllAction(Request $request, Cache $cache): JsonResponse
    {

        if($request->get('id')){
            $this->bus->dispatch(new ClearCacheMessage((int)$request->get('id'), false), [new TransportNamesStamp('sync')]);
        }else{
            $cache->clearAll();
        }

        return $this->jsonResponse(['success' => true]);
    }
}
