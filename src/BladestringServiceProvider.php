<?php
namespace Repack\Bladestring;

use Illuminate\View\ViewServiceProvider;

class BladestringServiceProvider extends ViewServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        // $this->registerEngineResolver($this->app);

        Bootstrapper::bootstrap($this->app);
    }
}
