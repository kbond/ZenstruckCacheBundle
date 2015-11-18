# ZenstruckCacheBundle

[![Build Status](http://img.shields.io/travis/kbond/ZenstruckCacheBundle.svg?style=flat-square)](https://travis-ci.org/kbond/ZenstruckCacheBundle)
[![Scrutinizer Code Quality](http://img.shields.io/scrutinizer/g/kbond/ZenstruckCacheBundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/kbond/ZenstruckCacheBundle/)
[![Code Coverage](http://img.shields.io/scrutinizer/coverage/g/kbond/ZenstruckCacheBundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/kbond/ZenstruckCacheBundle/)
[![StyleCI](https://styleci.io/repos/7914882/shield)](https://styleci.io/repos/7914882)
[![Latest Stable Version](http://img.shields.io/packagist/v/zenstruck/cache-bundle.svg?style=flat-square)](https://packagist.org/packages/zenstruck/cache-bundle)
[![License](http://img.shields.io/packagist/l/zenstruck/cache-bundle.svg?style=flat-square)](https://packagist.org/packages/zenstruck/cache-bundle)

Provides a httpcache warmup command for Symfony2. The command simply executes a `GET` request on a list of urls.
One or more url providers must be registered. This bundle requires an implementation of
[php-http/httplug](https://packagist.org/packages/php-http/httplug) and
[php-http/message-factory](https://packagist.org/packages/php-http/message-factory). The bundle will try and
auto-discover these if not configured directly.

## Installation

1. Add to your `composer.json`:

    ```
    $ composer require zenstruck/cache-bundle
    ```

2. Register this bundle with Symfony2:

    ```php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Zenstruck\CacheBundle\ZenstruckCacheBundle(),
        );
        // ...
    }
    ```

## Configuration

An `http_client` (class or service implementing `Http\Client\HttpClient`) and `message_factory`
(class or service implementing `Http\Message\MessageFactory`) must be configured.

```yaml
zenstruck_cache:
    http_client:    Acme\MyHttpClient    # or a service (acme.my_http_client)
    message_factory: Acme\MyMessageFactory # or a service (acme.my_message_factory)
```

If left blank, the bundle will try and auto-discover these classes. The following HTTP clients and
message factories are currently auto-discoverable:

* [guzzle6-adapter](https://packagist.org/packages/php-http/guzzle6-adapter) (Provides HttpClient
and allows discovery of MessageFactory)

        composer require php-http/guzzle6-adapter


* [guzzle5-adapter](https://packagist.org/packages/php-http/guzzle5-adapter) (Provides HttpClient)
and [guzzlehttp/psr7](https://packagist.org/packages/guzzlehttp/psr7) (allows discovery of MessageFactory)

        composer require php-http/guzzle5-adapter guzzlephp/psr7

## HttpCache Warmup Command

Usage:

    app/console zenstruck:http-cache:warmup

## Sitemap Provider

This bundle comes with a URL provider that looks at a list of sitemaps to retrieve a list of urls. If a url is
given without the sitemap or sitemap index, the provider first looks for a `{url}/sitemap_index.xml` to find a
set of sitemap files.  If no index is found, it defaults to using `{url}/sitemap.xml`.

* See http://www.sitemaps.org/ for information on how to create a sitemap.
* See [DpnXmlSitemapBundle](https://github.com/bjo3rnf/DpnXmlSitemapBundle) for creating a sitemap with Symfony2.

To enable the sitemap provider, configure it in your `config.yml`:

```yaml
zenstruck_cache:
    sitemap_provider:
        sitemaps:
            - http://example.com/sitemap.xml # detects if sitemap or sitemap index and act accordingly
            - http://example.com/en/sitemap.xml # same as above
            - http://www.example.com # trys http://example.com/sitemap_index.xml and http://example.com/sitemap.xml

```

## Add a Custom URL Provider

1. Create a class that implements `Zenstruck\CacheBundle\Url\UrlProvider`:

    ```php
    use Zenstruck\CacheBundle\Url\UrlProvider;

    namespace Acme;

    class MyUrlProvider implements UrlProvider
    {
        public function getUrls()
        {
            $urls = array();

            // fetch from a datasource

            return $urls;
        }

        public function count()
        {
            return count($this->getUrls());
        }
    }
    ```

2. Register the class as a service tagged with `zenstruck_cache.url_provider`:

    ```yaml
    my_url_provider:
        class: Acme\MyUrlProvider
        tags:
            - { name: zenstruck_cache.url_provider }
    ```

## Full Default Config

```yaml
zenstruck_cache:
    # Either a class or a service that implements Http\Client\HttpClient. Leave blank to attempt auto discovery.
    http_client:             ~

    # Either a class or a service that implements Http\Message\MessageFactory. Leave blank to attempt auto discovery.
    message_factory:          ~

    sitemap_provider:
        enabled:              false
        sitemaps:             []
```
