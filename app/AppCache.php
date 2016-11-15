<?php

use FOS\HttpCache\SymfonyCache\EventDispatchingHttpCache;
use FOS\HttpCache\SymfonyCache\PurgeSubscriber;
use FOS\HttpCache\SymfonyCache\RefreshSubscriber;
use FOS\HttpCache\SymfonyCache\UserContextSubscriber;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class AppCache extends EventDispatchingHttpCache
{

    /**
     * http://foshttpcache.readthedocs.io/en/stable/symfony-cache-configuration.html
     *
     * Overwrite constructor to register event subscribers for FOSHttpCache.
     *
     * @throws \RuntimeException
     */
    public function __construct(HttpKernelInterface $kernel, $cacheDir = null)
    {
        if(null === $cacheDir){
            $cacheDir = new \Symfony\Component\HttpKernel\HttpCache\Store($kernel->getCacheDir());
        }
        parent::__construct($kernel, $cacheDir);
        $this->addSubscriber(new UserContextSubscriber());
        $this->addSubscriber(new PurgeSubscriber());
        $this->addSubscriber(new RefreshSubscriber());
    }

}
