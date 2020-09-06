<?php

namespace Horizon\Experiments\Unit\Helpers;

use Ringierimu\Experiments\Tests\TestCase;
use GoogleTagManager;

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
            'experiment-1' => 'test',
        ];

        $_COOKIE['experiments'] = json_encode($experiments);

        GoogleTagManager::shouldReceive('set')
            ->with('sdc_experiment-1', 'test');

        track_experiments();
    }
}
