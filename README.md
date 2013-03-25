# ZenstruckCacheBundle

Provides a httpcache warmup command for Symfony2.  The command simply executes a `GET` request on a list of urls.
One or more url providers must be registered.

## Installation

1. Add to your `composer.json`:

    ```json
    {
        "require": {
            "zenstruck/cache-bundle": "*"
        }
    }
    ```

2. Register this bundle as well as the required `SensioBuzzBundle` with Symfony2:

    ```php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
            new Zenstruck\Bundle\CacheBundle\ZenstruckCacheBundle(),
        );
        // ...
    }
    ```

## HttpCache Warmup Command

```
Usage:
 zenstruck:http-cache:warmup [-f|--format="..."] [host]

Arguments:
 host                  The full host - ie http://www.google.com

Options:
 --format (-f)         progress|quiet|verbose (default: "progress")

Help:
 The zenstruck:http-cache:warmup command warms up the http cache.
```

## Sitemap Provider

This bundle comes with URL provider that looks at a site's `sitemap.xml` to retrieve a list of urls.  The provider
first looks for a `sitemap_index.xml` to find a set of sitemap files.  If no index is found, it defaults to using
`sitemap.xml`.

See http://www.sitemaps.org/ for information on how to create a sitemap.

See [DpnXmlSitemapBundle](https://github.com/dreipunktnull/DpnXmlSitemapBundle) for creating a sitemap with Symfony2.

### Usage

1. Enable the provider in your `config.yml`:

    ```yaml
    zenstruck_cache:
        sitemap_provider:     true
    ```

2. Run the command - make sure the host argument is set.

    ```
    $ php app/console zenstruck:http-cache:warmup http://www.example.com
    ```

## Add a Warmup URL Provider

1. Create a class that implements `Zenstruck\Bundle\CacheBundle\HttpCache\UrlProviderInterface`:

    ```php
    class MyWarmupProvider implements UrlProviderInterface
    {
       public function getUrls($host = null)
       {
           $urls = array();

           // fetch from a datasource

           return $urls;
       }
    }
    ```

2. Register the class as a service tagged with `zenstruck_cache.url_provider`:

    ```yaml
    my_url_provider:
            class: Acme\DemoBundle\HttpCache\MyWarmupProvider
            tags:
                - { name: zenstruck_cache.url_provider }
    ```

## Full Default Config

```yaml
zenstruck_cache:
    sitemap_provider:     false
```