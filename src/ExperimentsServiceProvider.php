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
        // do not merge config from base package, this can cause a/b test inheritance from the example config
        //$this->mergeConfigFrom($this->configPath(), 'experiments');

        $this->app->singleton('experiments', function ($app) {
            return $app->make(Experiments::class);
        });
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
