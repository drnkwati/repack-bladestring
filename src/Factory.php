<?php
namespace Repack\Bladestring;

if (class_exists('\Illuminate\View\Factory')) {
    class BaseFactory extends \Illuminate\View\Factory
    {}
} else {
    class BaseFactory extends \Repack\View\Factory
    {}
}

class Factory extends BaseFactory
{
    /**
     * The extension to engine bindings.
     *
     * @var array
     */
    // protected $extensions = array(
    //     'blade.php' => 'blade',
    //     'php' => 'php',
    //     'css' => 'file',
    // );

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected $renderCount = 0;

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|array  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return View|StringView
     */
    public function make($view, $data = array(), $mergeData = array())
    {
        $data = array_merge($mergeData, $this->parseData($data));

        $me = $this;

        // For string rendering
        if (is_array($view)) {
            $tap = function ($view) use ($me) {$me->callCreator($view);};
            $tap($val = $this->stringViewInstance($view, $data));
            return $val;
        }

        $path = $this->finder->find($view = $this->normalizeName($view));

        // Next, we will create the view instance and call the view creator for the view
        // which can set any data, etc. Then we will return the view instance back to
        // the caller for rendering or performing other view manipulations on this.
        $tap = function ($view) use ($me) {$me->callCreator($view);};
        $tap($val = $this->viewInstance($view, $path, $data));
        return $val;
    }

    /**
     * Create a new string view instance from the given arguments.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @return StringView
     */
    protected function stringViewInstance($view, $data)
    {
        return new StringView($this, $this->engines->resolve('stringblade'), $view, null, $data);
    }

    /**
     * Flush all of the section contents if done rendering.
     *
     * @return void
     */
    public function flushStateIfDoneRendering()
    {
        !$this->doneRendering() ?: $this->flushState();
    }

    /**
     * Flush all of the factory state like sections and stacks.
     *
     * @return void
     */
    public function flushState()
    {
        $this->renderCount = 0;

        $this->flushSections();
        $this->flushStacks();
    }

    /**
     * Get the appropriate view engine for the given string key.
     *
     * @param  string  $stringkey
     * @return \Illuminate\Contracts\View\Engine
     *
     *  ['file', 'php', 'blade', 'stringblade'] in StringBladeServiceProvider:registerEngineResolver
     *
     * @throws \InvalidArgumentException
     */
    public function getEngineFromStringKey($stringkey)
    {
        // resolve function throws error if $stringkey is not a registered engine
        return $this->engines->resolve($stringkey);
    }
}
