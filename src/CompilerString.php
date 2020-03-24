<?php

namespace Repack\Bladestring;

class CompilerString extends CompilerBlade
{
    private $use_cache_keys = array();

    private $viewData = array();

    /**
     * Create a new compiler instance.
     *
     * @param  string  $cachePath
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($cachePath = null)
    {
        $this->cachePath = $cachePath;
    }

    public function setViewData($viewData)
    {
        $this->viewData = $viewData;
    }

    /**
     * Compile the view at the given path.
     *
     * @param  object $viewData
     * @return void
     */
    public function compile($viewData = null)
    {
        if (!is_null($viewData)) {
            $this->viewData = $viewData;
        }

        if (property_exists($this->viewData, 'cache_key')) {
            $this->setPath($this->viewData->cache_key);
        }

        $contents = $this->compileString(
            $this->viewData->template
        );

        $tokens = $this->getOpenAndClosingPhpTokens($contents);

        // If the tokens we retrieved from the compiled contents have at least
        // one opening tag and if that last token isn't the closing tag, we
        // need to close the statement before adding the path at the end.
        if ($tokens && end($tokens) !== T_CLOSE_TAG) {
            $contents .= ' ?>';
        }

        if (isset($this->viewData->templateRefKey)) {
            $contents .= "<?php /**PATH {$this->viewData->templateRefKey} ENDPATH**/ ?>";
        }

        if (!is_null($this->cachePath)) {
            file_put_contents($this->getCompiledPath($this->viewData), $contents);
        }
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($viewData)
    {
        if (!property_exists($viewData, 'cache_key')) {
            $cacheKey = static::quickRandom(40);
            while (in_array($cacheKey, $this->use_cache_keys)) {
                $cacheKey = static::quickRandom(40);
            }

            $viewData->cache_key = $cacheKey;
        }

        return $this->cachePath . '/' . sha1($viewData->cache_key) . '.php';
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  object $viewData
     * @return bool
     */
    public function isExpired($viewData)
    {

        // adds ability to force template recompile
        if ($this->forceTemplateRecompile) {
            return true;
        }

        $compiled = $this->getCompiledPath($viewData);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (!$this->cachePath || !file_exists($compiled)) {
            return true;
        }

        // If set to 0, then return cache has expired
        if (property_exists($viewData, 'secondsTemplateCacheExpires')) {
            if ($viewData->secondsTemplateCacheExpires == 0) {
                return true;
            }
        } else {
            $viewData->secondsTemplateCacheExpires = 0;
        }

        // Note: The lastModified time for a file on homestead will use the time from the host system.
        //       This means the vm time could be off, so setting the timeout to seconds may not work as expected.

        return time() >= (filemtime($compiled) + $viewData->secondsTemplateCacheExpires);
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param  int     $length
     * @return string
     */
    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    /**
     * Get the open and closing PHP tag tokens from the given string.
     *
     * @param  string  $contents
     * @return Collection
     */
    protected function getOpenAndClosingPhpTokens($contents)
    {
        $tokens = token_get_all($contents);

        foreach ($tokens as $in => $token) {$tokens[$in] = $tokens[0];}

        return array_filter($tokens, function ($token) {
            return in_array($token, array(T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG));
        });
    }
}
