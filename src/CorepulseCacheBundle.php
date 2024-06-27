<?php

namespace CorepulseCacheBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;

class CorepulseCacheBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/corepulsecache/js/pimcore/startup.js',
            '/bundles/corepulsecache/js/pimcore/settings.js',
        ];
    }

    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }

}
