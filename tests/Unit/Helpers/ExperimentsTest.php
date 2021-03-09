<?php

namespace Ringierimu\Experiments\Tests\Unit\Helpers;

use GoogleTagManager;
use Ringierimu\Experiments\Tests\TestCase;
use Spatie\GoogleTagManager\DataLayer;

class ExperimentsTest extends TestCase
{
    public function testExperimentGroup()
    {
        $experiments = [
            'experiment-1' => 'test',
        ];

        $this
            ->assertNull(experiment_group('experiment-1'));

        $_COOKIE['experiments'] = json_encode($experiments);

        $this
            ->assertEquals(
                'test',
                experiment_group('experiment-1')
            );

        $experiments = [
            'experiment-1' => 'control',
        ];

        $_COOKIE['experiments'] = json_encode($experiments);

        $this
            ->assertEquals(
                'control',
                experiment_group('experiment-1')
            );
    }

    public function testTrackExperiments()
    {
        $experiments = [
            'recommend' => 'test',
        ];

        $_COOKIE['experiments'] = json_encode($experiments);

        track_experiments();

        /** @var DataLayer $dataLayer */
        $dataLayer = GoogleTagManager::getDataLayer();

        $this->assertEquals(
            [
                'sdc_recommend' => 'test',
            ],
            $dataLayer->toArray()
        );
    }

    public function testGetRunningExperiments()
    {
        $this->assertEquals(
            ['recommend'],
            get_running_experiments()
        );

        config(
            [
                'experiments' => [],
            ]
        );

        $this->assertEquals(
            [],
            get_running_experiments()
        );
    }
}
