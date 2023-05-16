<?php

namespace ACPL\FlarumCache\Utility;

use ACPL\FlarumCache\LSCache;
use Flarum\Foundation\Paths;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Flarum\Http\CookieFactory;

class HtaccessManager
{
    private const BEGIN_LSCACHE = '# BEGIN LSCACHE';
    private const END_LSCACHE = '# END LSCACHE';

    private string $htaccessPath;
    private Filesystem $filesystem;
    private CookieFactory $cookie;

    public function __construct(Paths $paths, CookieFactory $cookie)
    {
        $this->filesystem = new Filesystem();
        $this->htaccessPath = $paths->public.'/.htaccess';

        $this->cookie = $cookie;
    }

    /**
     * Updates the .htaccess file with the new LSCache block content. If the LSCache block is already there, it replaces it. Otherwise, it appends it
     * @throws FileNotFoundException
     */
    public function updateHtaccess(): void
    {
        $htaccessContent = $this->filesystem->get($this->htaccessPath);
        $newContent = $this->generateLsCacheBlock();

        if ($this->hasLsCacheBlock($htaccessContent)) {
            $htaccessContent = $this->replaceLsCacheBlock($htaccessContent, $newContent);
        } else {
            $htaccessContent = $this->prependLsCacheBlock($htaccessContent, $newContent);
        }

        $this->filesystem->put($this->htaccessPath, $htaccessContent);
    }

    /** Generates the content of the LSCache block to be inserted into the .htaccess file. */
    private function generateLsCacheBlock(): string
    {
        $block = self::BEGIN_LSCACHE." - Do not edit the contents of this block!\n";
        $block .= '<IfModule LiteSpeed>'.
            $this->addLine('CacheLookup on').
            $this->addLine('RewriteEngine On').
            $this->addLine('RewriteCond %{REQUEST_METHOD} ^HEAD|GET$').
            $this->addLine(
                'RewriteRule .* - [E="Cache-Vary:'.implode(',', [
                    $this->cookie->getName(LSCache::VARY_COOKIE),
                    $this->cookie->getName('remember'),
                    'locale'
                ]).'"]'
            );
        $block .= "\n</IfModule>";
        $block .= "\n".self::END_LSCACHE;

        return $block;
    }

    /** Helper method for generating a line of the LSCache block content */
    private function addLine(string $line): string
    {
        return "\n\t$line";
    }

    /** Checks if the LSCache block is present in the provided .htaccess content */
    private function hasLsCacheBlock(string $htaccessContent): bool
    {
        return str_contains($htaccessContent, self::BEGIN_LSCACHE);
    }

    /** Replaces the existing LSCache block in the .htaccess content with the new content */
    private function replaceLsCacheBlock(string $htaccessContent, string $newContent): string
    {
        $pattern = '/'.preg_quote(self::BEGIN_LSCACHE, '/').'.*'.preg_quote(self::END_LSCACHE, '/').'/s';
        return preg_replace($pattern, $newContent, $htaccessContent);
    }

    /** Prepends the new LSCache block to the .htaccess content */
    private function prependLsCacheBlock(string $htaccessContent, string $newContent): string
    {
        return "$newContent\n".$htaccessContent;
    }

    /**
     * Removes the LSCache block from the .htaccess file. If the block is not present, it does nothing
     * @throws FileNotFoundException
     */
    public function removeLsCacheBlock(): void
    {
        $htaccessContent = $this->filesystem->get($this->htaccessPath);

        if (! $this->hasLsCacheBlock($htaccessContent)) {
            return;
        }

        $pattern = '/'.preg_quote(self::BEGIN_LSCACHE, '/').'.*'.preg_quote(self::END_LSCACHE, '/').'/s';
        $htaccessContent = preg_replace($pattern, '', $htaccessContent);

        $this->filesystem->put($this->htaccessPath, $htaccessContent);
    }
}
