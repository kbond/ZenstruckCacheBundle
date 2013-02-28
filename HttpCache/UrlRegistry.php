<?php

namespace Zenstruck\Bundle\CacheBundle\HttpCache;

use Zenstruck\Bundle\CacheBundle\HttpCache\Provider\UrlProviderInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class UrlRegistry
{
    /** @var UrlProviderInterface[] */
    protected $providers = array();

    public function addProvider(UrlProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @return UrlProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Returns the array of absolute urls to warmup
     *
     * @param $host
     *
     * @return array
     */
    public function getUrls($host)
    {
        $urls = array();

        foreach ($this->providers as $provider) {
            $urls = array_merge($urls, $provider->getUrls($host));
        }

        return $urls;
    }
}
