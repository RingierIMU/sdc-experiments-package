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
                    'tests' => [
                        'recommend' => [
                            'control' => 'personalize',
                            'test' => 'alice',
                        ],
                    ],
                    'groups' => [
                        'recommend_group' => [
                            'id' => 'GOOGLE_OPTIMIZE_EXPERIMENT_ID',
                            'variations' => [
                                'recommend',
                            ],
                        ],
                        'another_group_not_called_recommend' => [
                            'id' => 'GOOGLE_OPTIMIZE_EXPERIMENT_ID_2',
                            'variations' => [
                                'recommend',
                                'recommend', // repeat the same one here for
                            ],
                        ],
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
