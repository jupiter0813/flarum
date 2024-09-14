<?php

namespace ACPL\FlarumLSCache;

final class LSCacheHeader
{
    const CACHE_CONTROL = 'X-LiteSpeed-Cache-Control';
    const PURGE = 'X-LiteSpeed-Purge';
    const TAG = 'X-LiteSpeed-Tag';
    const VARY = 'X-LiteSpeed-Vary';
}
