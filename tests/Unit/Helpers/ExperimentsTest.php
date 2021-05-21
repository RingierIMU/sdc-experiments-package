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

        $assignmentIndex = $assignment === 'control' ? 0 : 1;

        $this->assertEquals(
            [
                'experiments' => [
                    'recommend' => $assignment,
                ],
                'ga_optimize_exp' => sprintf(
                    'GOOGLE_OPTIMIZE_EXPERIMENT_ID.%s!GOOGLE_OPTIMIZE_EXPERIMENT_ID_2.%s-%s',
                    $assignmentIndex,
                    $assignmentIndex,
                    $assignmentIndex
                ),
                'experiments_running' => sprintf(
                    'recommend_group.%s!another_group_not_called_recommend.%s-%s',
                    $assignmentIndex,
                    $assignmentIndex,
                    $assignmentIndex
                ),
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
                    'tests' => [
                        'experiment-1' => [],
                        'experiment-2' => [],
                    ],
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
