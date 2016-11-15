<?php

require_once __DIR__ . '/AppKernel.php';

use FOS\HttpCache\SymfonyCache\CacheInvalidationInterface;
use FOS\HttpCache\SymfonyCache\EventDispatchingHttpCache;
use FOS\HttpCache\SymfonyCache\PurgeSubscriber;
use FOS\HttpCache\SymfonyCache\RefreshSubscriber;
use FOS\HttpCache\SymfonyCache\UserContextSubscriber;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpKernelInterface;


class AppCache extends EventDispatchingHttpCache implements CacheInvalidationInterface
{
//    use EventDispatchingHttpCache;

    /**
     *
     * Overwrite constructor to register event subscribers for FOSHttpCache.
     *
     * @param HttpKernelInterface $kernel
     * @param null $cacheDir
     */
    public function __construct(HttpKernelInterface $kernel, $cacheDir = null)
    {

        /* @var AppKernel $kernel */
        if (null === $cacheDir) {
            $cacheDir = new \Symfony\Component\HttpKernel\HttpCache\Store($kernel->getCacheDir());
        }
        parent::__construct($kernel, $cacheDir);
//        $this->addSubscriber(new UserContextSubscriber());
        $this->addSubscriber(new PurgeSubscriber());
//        $this->addSubscriber(new RefreshSubscriber());
    }



}
