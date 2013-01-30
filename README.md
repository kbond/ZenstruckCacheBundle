# ZenstruckCacheBundle

Provides a httpcache warmup command for Symfony2

## Installation

1. Add to your `composer.json`:

    ```json
    {
        "require": {
            "zenstruck/cache-bundle": "*"
        }
    }
    ```

2. Register the bundle with Symfony2:

    ```php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Zenstruck\Bundle\CacheBundle\ZenstruckCacheBundle(),
        );
        // ...
    }
    ```

## HttpCache Warmup Command

```
Usage:
 zenstruck:http-cache:warmup [host]

Arguments:
 host  The full host - ie http://www.google.com

Help:
 The zenstruck:http-cache:warmup command warms up the http cache.
```

## Add a Warmup URL Provider

1. Create a class that implements `Zenstruck\Bundle\CacheBundle\HttpCache\WarmupProviderInterface`:

    ```php
    class MyWarmupProvider implements WarmupProviderInterface
    {
       public function getUrls($host = null)
       {
           $urls = array();

           // fetch from a datasource

           return $urls;
       }
    }
    ```

2. Register the class as a service tagged with `zenstruck_cache.warmup_provider`:

    ```yaml
    my_warmup_provider:
            class: Acme\DemoBundle\HttpCache\MyWarmupProvider
            tags:
                - { name: zenstruck_cache.warmup_provider }
    ```

## TODO

- add default Sitemap Provider
- add default Spider Provider