<?php

namespace ACPL\FlarumCache;

class LSCache
{
    const VARY_COOKIE = 'lscache_vary';

    public static function extractRootRouteName(string $name): string
    {
        return explode('.', $name, 2)[0];
    }
}
