<?php

namespace Repack\Bladestring;

if (class_exists('\Illuminate\View\Compilers\BladeCompiler')) {
    class BladeCompiler extends \Illuminate\View\Compilers\BladeCompiler
    {}
} else {
    class BladeCompiler extends \Repack\View\Compilers\BladeCompiler
    {}
}

class CompilerBlade extends BladeCompiler
{
    /**
     *  Switch to force template recompile
     */
    protected $forceTemplateRecompile = false;

    /**
     * Switch to track escape setting for contentTags.
     *
     * @deprecated Just use the escape tags {{{ }}} default
     *
     * @var bool
     */
    // protected $contentTagsEscaped = true;

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected $rawTags = array('{!!', '!!}');

    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected $contentTags = array('{{', '}}');

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = array('{{{', '}}}');

    /**
     * Sets the content tags used for the compiler.
     *
     * @deprecated This feature was removed from Laravel (https://github.com/laravel/framework/issues/17736)
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     * @return void
     */
    public function setRawTags($openTag, $closeTag, $escaped = true)
    {
        $this->rawTags = array(preg_quote($openTag), preg_quote($closeTag));
    }

    /**
     * Sets the content tags used for the compiler.
     *
     * @deprecated This feature was removed from Laravel (https://github.com/laravel/framework/issues/17736)
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     * @return void
     */
    public function setContentTags($openTag, $closeTag, $escaped = true)
    {
        $this->contentTags = array(preg_quote($openTag), preg_quote($closeTag));
    }

    /**
     * Sets the escape tags used for the compiler.
     *
     * @deprecated This feature was removed from Laravel (https://github.com/laravel/framework/issues/17736)
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     * @return void
     */
    public function setEscapeTags($openTag, $closeTag, $escaped = true)
    {
        $this->escapedTags = array(preg_quote($openTag), preg_quote($closeTag));
    }

    /**
     * Enable/Disable force recompile of templates.
     *
     * @param  bool  $recompile
     * @return void
     */
    public function setForceTemplateRecompile($recompile = true)
    {
        $this->forceTemplateRecompile = $recompile;
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path)
    {

        // adds ability to force template recompile
        if ($this->forceTemplateRecompile) {
            return true;
        }

        return parent::isExpired($path);
    }
}
