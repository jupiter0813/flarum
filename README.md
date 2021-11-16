# LiteSpeed Cache for Flarum


[![Latest Stable Version](https://img.shields.io/packagist/v/acpl/flarum-lscache)](https://packagist.org/packages/acpl/flarum-lscache) [![Total Downloads](https://img.shields.io/packagist/dt/acpl/flarum-lscache.svg)](https://packagist.org/packages/acpl/flarum-lscache)

A [Flarum](http://flarum.org) extension. Integrates LSCache with your forum.

Requires a LiteSpeed Web Server.

# Installation

### Install with composer:

```sh

composer require acpl/flarum-lscache:"*"

```

#### You need to include this code in your `.htaccess` file:

```apacheconf
<IfModule LiteSpeed>
    CacheLookup on
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} ^HEAD|GET$
    # Detection of logged-in user.
    RewriteRule .* - [E="Cache-Vary:flarum_remember,flarum_lscache_vary"]
    # If you have a non-default path to the admin panel, change "admin" to match.
    RewriteCond %{ORG_REQ_URI} !/admin
    # Enable private cache for admin panel. If it causes problems replace "private" with "no-cache".
    RewriteRule .* - [E=Cache-Control:private]
</IfModule>
```
Rules in `.htaccess` have a higher priority than those added by the extension. For example, you can exclude specific URLs from the cache or change their expiration time. For more options, see here: [https://docs.litespeedtech.com/lscache/noplugin/settings/#rewrite-rules](https://docs.litespeedtech.com/lscache/noplugin/settings/#rewrite-rules)


### Updating

```sh

composer update acpl/flarum-lscache:"*"

php flarum migrate

php flarum cache:clear

php flarum lscache:clear

```

## Links

- [Packagist](https://packagist.org/packages/acpl/flarum-lscache)

- [GitHub](https://github.com/android-com-pl/flarum-lscache)

[comment]: <> (- [Discuss]&#40;https://discuss.flarum.org/d/PUT_DISCUSS_SLUG_HERE&#41;)
