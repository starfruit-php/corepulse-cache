<?php

namespace CorepulseCacheBundle\Controller;

use CorepulseCacheBundle\Cache;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\UrlSlug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends FrontendController
{

    /**
     * @Route("/corepulse_cache")
     */
    public function indexAction(Request $request, Cache $cache): Response
    {

        // $this->setLocaleRequest();
        // dd($this->request->getLocale(), $this->translator->getLocale(), $this->validator);
        // $containerConfig = \Pimcore::getContainer()->getParameterBag();
        // dd($this->getParameter('framework'));
        // dd($containerConfig);
        $response = new Response('Hello world from corepulse_cache');

        return $response;
    }

    /**
     * @param Request $request
     * @param AbstractObject $object
     * @param UrlSlug $urlSlug
     *
     * @return Response
     */
    public function slugAction(Request $request, DataObject\News $object, UrlSlug $urlSlug)
    {
        // we use param resolver to the the matched data object ($object)
        // $urlSlug contains the context information of the slug
        dd($object->getSlug());
        return [
            'product' => $object,
        ];
    }
}
