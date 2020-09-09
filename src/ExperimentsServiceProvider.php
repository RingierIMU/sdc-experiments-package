<?php

namespace Ringierimu\Experiments;

use Illuminate\Support\ServiceProvider;

class ExperimentsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes(
            [
                $this->configPath() => config_path('experiments.php'),
            ],
            'config'
        );

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    Console\InstallCommand::class,
                ]
            );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            $this->configPath(),
            'experiments'
        );
    }

    /**
     * Return config file.
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__ . '/../config/experiments.php';
    }
}
