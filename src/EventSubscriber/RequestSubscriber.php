<?php

namespace CorepulseCacheBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

class RequestSubscriber implements EventSubscriberInterface
{
    private $security;
    private $tokenStorage;

    public function __construct(Security $security, TokenStorageInterface $tokenStorage)
    {
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // Truy cập thông tin người dùng đang đăng nhập
        // $user = $this->security->getUser();
        // $request = $event->getRequest();

        // if ($user && $user->getId() && $request->isMethod('GET')) {
        //     $token = $this->tokenStorage->getToken();
        //     $firewallName = $token->getFirewallName();
        //     $userId = $user->getId();

        //     $storage = Storage::get('corepulse_cache');

        //     $pathInfo = $request->getPathInfo();
        //     $queryString = $request->getQueryString();

        //     $url = $pathInfo . '.html';

        //     if (strpos($pathInfo, '/api') === 0) {
        //         if ($queryString !== null) {
        //             $url = $pathInfo . '?' . $queryString . '.json';
        //         } else {
        //             $url = $pathInfo . '.json';
        //         }
        //     }

        //     $path = "/pages/$firewallName/$userId" . $url;
        //     $hostcache = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/var/tmp/cpcache';
        //     if ($storage->has($path)) {
        //         // Nếu có, trả về nội dung của tệp cache
        //         if ($queryString !== null) {
        //             $path = $path . '?' . $queryString;
        //         }

        //         $content = file_get_contents($hostcache . $path);
        //         $response = new Response($content);
        //         $event->setResponse($response);
        //     }
        // }
    }
}
