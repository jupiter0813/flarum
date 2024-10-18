# LiteSpeed Cache for Flarum


[![Latest Stable Version](https://img.shields.io/packagist/v/acpl/flarum-lscache)](https://packagist.org/packages/acpl/flarum-lscache) [![Total Downloads](https://img.shields.io/packagist/dt/acpl/flarum-lscache.svg)](https://packagist.org/packages/acpl/flarum-lscache) [![GitHub Sponsors](https://img.shields.io/badge/Donate-%E2%9D%A4-%23db61a2.svg?&logo=github&logoColor=white&labelColor=181717)](https://github.com/android-com-pl/flarum-lscache?sponsor=1)

A [Flarum](http://flarum.org) extension. Integrates [LSCache](https://lscache.io/) with your forum.

Requires a LiteSpeed Web Server or OpenLiteSpeed.

## Installation

### Install with composer:

```sh
composer require acpl/flarum-lscache
```

Upon initial activation, the extension will add its configurations to the `.htaccess` file.
It's recommended to back up your `.htaccess` file before installing the extension.

### Updating

```sh
composer update acpl/flarum-lscache
php flarum migrate
php flarum cache:clear
```

### Cache Management

This extension smartly manages the cache by purging it only where needed. For instance, when a new post is added in a discussion, the cache for that specific discussion, its tags, and the homepage are purged.

When you clear the Flarum cache, the LSCache is also cleared automatically unless you disable this feature in the settings.

You can clear the LSCache without clearing the Flarum cache via the admin panel. This option is available under the standard Flarum cache clearing option. There is also the `php flarum lscache:clear` command. The command supports the `--path` argument. For example, `php flarum lscache:clear --path=/tags --path=/d/1-test`. You can use this if you only want to purge specific paths instead of the entire cache.

---

## For Developers

### How the Extension Tags Paths

First, it's useful to understand how the extension adds LSCache tags to forum paths.
The extension uses route names. For example, if you registered routes for a resource in your extension named `examples`:

```php
(new Extend\Routes('api'))
    ->get('/examples', 'examples.index', ExamplesListController::class)
    ->post('/examples', 'examples.create', ExamplesCreateController::class)
    // and so on
```

The extension will automatically add the following tags to the response:
- For paths with the `.index` suffix (e.g., `examples.index`):
  - A tag corresponding to the main resource name is added (e.g., `examples`)
- For other paths:
  - A tag with the full path name is added (e.g., `examples.overview`)
- If the request contains parameters:
  - For the `id` parameter: a tag `{resource}_{id}` is added (e.g., `example_1`)
  - For the `slug` parameter: a tag `{resource}_{slug}` is added (e.g., `example_example-slug`)

> [!TIP]
> To see the cache tags added for a given request, check the value of the `X-LiteSpeed-Tag` header in the Network tab of DevTools.

### Purging Cache

By default, the extension purges the cache for a resource with a given ID if it detects successful requests to paths with the suffixes `.create`, `.update`, `.delete`.

To disable this behavior and add your own event handling, add your resource to the `$resourcesSupportedByEvent` array:

```php
// 💡 resource name should be in singular form
\ACPL\FlarumLSCache\Utility\LSCachePurger::$resourcesSupportedByEvent[] = 'example'

return [
    // ... your current extenders
];
```

Then you can create an event listener:

```php
// extend.php
use Flarum\Extend;

return [
    // ... your current extenders
    (new Extend\Conditional)
        ->whenExtensionEnabled('acpl-lscache', [
            (new Extend\Event)->listen(ExampleUpdated::class, ExampleUpdatedListener::class)
        ]),
];
```

```php
// ExampleUpdatedListener.php
use ACPL\FlarumLSCache\Listener\AbstractCachePurgeListener;

class ExampleUpdatedListener extends AbstractCachePurgeListener
{
    /** @param  ExampleUpdated  $event */
    protected function addPurgeData($event): void  
    {
        // Purge cache tag
        $this->purger->addPurgeTag('examples');
        // or purge multiple cache tags
        $this->purger->addPurgeTags([
            'examples',
            "examples_{$event->example->id}"
        ]);

        // Purge a single path
        $this->purger->addPurgePath('/examples');
        // or purge multiple paths
        $this->purger->addPurgePaths([
            '/examples',
            "/examples_{$event->example->id}",
        ]);
    }
}
```

> [!TIP]
> It is recommended to purge cache tags instead of paths, as they also apply to different versions of the address, e.g., with query strings.

It's also possible to create an event subscriber if you want to group multiple listeners in one class:

```php
// extend.php
use Flarum\Extend;

return [
    // ... your current extenders
    (new Extend\Conditional)
        ->whenExtensionEnabled('acpl-lscache', [
            (new Extend\Event)->subscribe(ExampleEventSubscriber::class),
        ]),
];
```

```php
// ExampleEventSubscriber.php
use ACPL\FlarumLSCache\Listener\AbstractCachePurgeSubscriber;
use Illuminate\Contracts\Events\Dispatcher;

class ExampleEventSubscriber extends AbstractCachePurgeSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $this->addPurgeListener($events, ExampleUpdated::class, [$this, 'handleExampleUpdated']);
        // ... rest of listeners
    }

    public function handleExampleUpdated(ExampleUpdated $event): void
    {
        $this->purger->addPurgeTags([
            'examples',
            "example_{$event->example->id}",
        ]);
    }
    // ... rest of methods
}
```

## Links

- [Packagist](https://packagist.org/packages/acpl/flarum-lscache)
- [GitHub](https://github.com/android-com-pl/flarum-lscache)
- [Discuss](https://discuss.flarum.org/d/29475)
