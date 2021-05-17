<?php

namespace Ringierimu\Experiments\Tests\Unit\Helpers;

use Ringierimu\Experiments\Facades\SdcExperiments;
use Ringierimu\Experiments\Tests\TestCase;
use Spatie\GoogleTagManager\GoogleTagManagerFacade as GoogleTagManager;

class ExperimentsTest extends TestCase
{
    public function testTrackExperiments()
    {
        $assignment = SdcExperiments::getOrStartExperiment('recommend');

        SdcExperiments::googleTagManagerSetTrackingVars();

        $this->assertEquals(
            [
                'experiments' => [
                    'recommend' => $assignment,
                ],
            ],
            GoogleTagManager::getDataLayer()->toArray()
        );
    }

    public function testGetRunningExperiments()
    {
        $this->assertEquals(
            ['recommend'],
            SdcExperiments::availableExperiments()
        );

        config(
            [
                'experiments' => [],
            ]
        );

        $this->assertEquals(
            [],
            SdcExperiments::availableExperiments()
        );
    }

    public function testExperimentGroup()
    {
        $this
            ->assertNull(
                SdcExperiments::getExperiment('experiment-1')
            );

        $assignment = SdcExperiments::getOrStartExperiment('experiment-1');

        $this
            ->assertNull($assignment);

        config(
            [
                'experiments' => [
                    'experiment-1' => [],
                    'experiment-2' => [],
                ],
            ]
        );

        $assignment = SdcExperiments::getOrStartExperiment('experiment-1');

        $this
            ->assertNotNull($assignment);

        $this
            ->assertEquals(
                $assignment,
                SdcExperiments::getExperiment('experiment-1')
            );

        $assignment = SdcExperiments::getOrStartExperiment('experiment-2');

        $this
            ->assertNotEmpty($assignment);

        $this
            ->assertEquals(
                $assignment,
                SdcExperiments::getExperiment('experiment-2')
            );
    }
}
