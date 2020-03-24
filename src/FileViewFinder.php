<?php
namespace Repack\Bladestring;

if (class_exists('\Illuminate\View\FileViewFinder')) {
    class FileViewFinder extends \Illuminate\View\FileViewFinder
    {}
} else {
    class FileViewFinder extends \Repack\View\FileViewFinder
    {}
}
