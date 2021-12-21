# LiteSpeed Cache for Flarum


[![Latest Stable Version](https://img.shields.io/packagist/v/acpl/flarum-lscache)](https://packagist.org/packages/acpl/flarum-lscache) [![Total Downloads](https://img.shields.io/packagist/dt/acpl/flarum-lscache.svg)](https://packagist.org/packages/acpl/flarum-lscache) [![Supports latest Flarum](https://flarum-badge-api.davwheat.dev/v1/compat-latest/acpl/flarum-lscache)](https://extiverse.com/extension/acpl/flarum-lscache)

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
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} ^HEAD|GET$
    # Detection of logged-in user.
    RewriteRule .* - [E="Cache-Vary:flarum_remember,flarum_lscache_vary,locale"]
    # If you have a non-default path to the admin panel, change "admin" to match.
    RewriteCond %{ORG_REQ_URI} !/admin
    # Enable private cache for admin panel. If it causes issues set [E=Cache-Control:no-cache]
    RewriteRule .* - [E=Cache-Control:private,max-age=300]
</IfModule>
```
You can also add your own rules. For more information see here: [https://docs.litespeedtech.com/lscache/noplugin/settings/#rewrite-rules](https://docs.litespeedtech.com/lscache/noplugin/settings/#rewrite-rules)


### Updating

```sh
composer update acpl/flarum-lscache:"*"
php flarum migrate
php flarum cache:clear
```
When you clear the Flarum cache, the LSCache is cleared automatically.

You can clear LSCache without clearing the Flarum cache in the admin panel. The option is available under the standard Flarum cache clearing option. There is also the `php flarum lscache:clear` command.

## Links

- [Packagist](https://packagist.org/packages/acpl/flarum-lscache)
- [GitHub](https://github.com/android-com-pl/flarum-lscache)
- [Discuss](https://discuss.flarum.org/d/29475-litespeed-cache-for-flarum)
