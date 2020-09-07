<?php

namespace Ringierimu\Experiments;

use Illuminate\Support\ServiceProvider;

class ExperimentsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
    }

    public function register()
    {
        $this->commands(
            [
                Console\InstallCommand::class,
            ]
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

    /**
     * Publish config file.
     */
    protected function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    $this->configPath() => config_path('experiments.php'),
                ],
                'experiments-config'
            );
        }
    }
}
