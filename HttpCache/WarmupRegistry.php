<?php

namespace Zenstruck\Bundle\CacheBundle\HttpCache;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class WarmupRegistry
{
    /** @var WarmupProviderInterface[] */
    protected $providers = array();

    public function addProvider(WarmupProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @return WarmupProviderInterface[]
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
