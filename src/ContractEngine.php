<?php

namespace Repack\Bladestring;

if (interface_exists('\Illuminate\Contracts\View\Engine')) {
    interface ContractEngine extends \Illuminate\Contracts\View\Engine
    {}
} else {
    interface ContractEngine extends \Repack\View\Engine
    {}
}
