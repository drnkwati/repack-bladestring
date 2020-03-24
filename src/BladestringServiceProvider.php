<?php
namespace Repack\Bladestring;

use Illuminate\Support\ServiceProvider;

class BladestringServiceProvider extends ServiceProvider
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
