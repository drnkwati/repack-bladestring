<?php

namespace Repack\Bladestring;

if (class_exists('\Illuminate\View\View')) {
    class BaseView extends \Illuminate\View\View
    {}
} else {
    class BaseView extends \Repack\View\View
    {}
}

class View extends BaseView
{
    /**
     * Create a new view instance.
     *
     * @param  Factory  $factory
     * @param  Engine  $engine
     * @param  string  $view
     * @param  string  $path
     * @param  array   $data
     *
     */
    public function __construct(Factory $factory, ContractEngine $engine, $view, $path, $data = array())
    {
        $this->view = $view;
        $this->path = $path;
        $this->engine = $engine;
        $this->factory = $factory;

        $this->data = method_exists($data, 'toArray') ? $data->toArray() : (array) $data;
    }

    /**
     * Dynamically bind parameters to the view.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return View
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        return parent::__call($method, $parameters);
    }
}
