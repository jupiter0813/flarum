<?php

namespace ACPL\FlarumCache;

class LSCache
{
    const VARY_COOKIE = 'lscache_vary';
    const DEFAULT_DROP_QS = ['fbclid', 'gclid', 'utm*', '_ga'];

    public static function extractRootRouteName(string $name): string
    {
        return explode('.', $name, 2)[0];
    }
}
