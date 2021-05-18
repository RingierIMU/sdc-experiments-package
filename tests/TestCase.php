<?php

namespace Ringierimu\Experiments\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Ringierimu\Experiments\ExperimentsServiceProvider;
use Ringierimu\Experiments\Facades\SdcExperiments;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;
use Spatie\GoogleTagManager\GoogleTagManagerServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(__DIR__ . '/..');
        config(
            [
                'experiments' => [
                    'recommend' => [
                        'control' => 'personalize',
                        'test' => 'alice',
                    ],
                ],
            ]
        );
    }

    /**
     * @param $app
     *
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            ExperimentsServiceProvider::class,
            GoogleTagManagerServiceProvider::class,
        ];
    }

    /**
     * @param $app
     *
     * @return string[]
     */
    protected function getPackageAliases($app)
    {
        return [
            'GoogleTagManager' => GoogleTagManagerFacade::class,
            'SdcExperiments' => SdcExperiments::class,
        ];
    }
}
