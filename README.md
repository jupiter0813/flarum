# LiteSpeed Cache for Flarum


[![Latest Stable Version](https://img.shields.io/packagist/v/acpl/flarum-lscache)](https://packagist.org/packages/acpl/flarum-lscache) [![Total Downloads](https://img.shields.io/packagist/dt/acpl/flarum-lscache.svg)](https://packagist.org/packages/acpl/flarum-lscache)

A [Flarum](http://flarum.org) extension. Integrates [LSCache](https://lscache.io/) with your forum.

Requires a LiteSpeed Web Server or OpenLiteSpeed.

# Installation

### Install with composer:

```sh
composer require acpl/flarum-lscache:"*"
```

#### You need to include this code in your `.htaccess` file:

```apacheconf
<IfModule LiteSpeed>
    CacheLookup on
    # Detection of logged-in user.
    RewriteCond %{REQUEST_METHOD} ^HEAD|GET$
    RewriteRule .* - [E="Cache-Vary:flarum_remember,flarum_lscache_vary,locale"]
</IfModule>
```
You can also add your own rules. For more information, visit: [https://docs.litespeedtech.com/lscache/noplugin/settings/#rewrite-rules](https://docs.litespeedtech.com/lscache/noplugin/settings/#rewrite-rules)


### Updating

```sh
composer update acpl/flarum-lscache:"*"
php flarum migrate
php flarum cache:clear
```

### Cache Management

This extension smartly manages the cache by purging it only where needed. For instance, when a new post is added in a discussion, the cache for that specific discussion, its tags, and the homepage are purged.

When you clear the Flarum cache, the LSCache is also cleared automatically, unless you disable this feature in the settings.

You can clear the LSCache without clearing the Flarum cache via the admin panel. This option is available under the standard Flarum cache clearing option. There is also the `php flarum lscache:clear` command. The command supports the `--path` argument. For example, `php flarum lscache:clear --path=/tags --path=/d/1-test`. You can use this if you only want to purge specific paths instead of the entire cache.

### FAQ
_How can I prevent different cache versions from being generated for specific query strings? For example, fbclid._

You can use `CacheKeyModify -qs:[key]`.

Example:
```apacheconf
<IfModule LiteSpeed>
    #... Rest of the code
    
    CacheKeyModify -qs:fbclid
    CacheKeyModify -qs:gclid
    CacheKeyModify -qs:utm*
    CacheKeyModify -qs:_ga
</IfModule>
```

## Links

- [Packagist](https://packagist.org/packages/acpl/flarum-lscache)
- [GitHub](https://github.com/android-com-pl/flarum-lscache)
- [Discuss](https://discuss.flarum.org/d/29475)
