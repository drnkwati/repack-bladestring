<?php
namespace Repack\Bladestring;

if (class_exists('\Illuminate\View\Engines\EngineResolver')) {
    class EngineResolver extends \Illuminate\View\Engines\EngineResolver
    {}
} else {
    class EngineResolver extends \Repack\View\Engines\EngineResolver
    {}
}
