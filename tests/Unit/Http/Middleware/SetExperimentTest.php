<?php

namespace Ringierimu\Experiments\Tests\Unit\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Ringierimu\Experiments\Facades\SdcExperiments;
use Ringierimu\Experiments\Http\Middleware\SetExperiment;
use Ringierimu\Experiments\Tests\TestCase;
use Symfony\Component\HttpFoundation\Cookie as CookieObject;

class SetExperimentTest extends TestCase
{
    public function testSuccess()
    {
        $request = new Request();

        $this->setExperiments();

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function () {
                }
            );

        $experiment = $this->getExperiment();

        $this->assertTrue(
            in_array(
                $experiment['experiment_recommend'],
                array_keys(
                    config('experiments.tests.recommend')
                )
            )
        );
    }

    public function testSuccessSeveralExperiments()
    {
        config(
            [
                'experiments' => [
                    'tests' => [
                        'recommend' => [
                            'control' => 'personalize',
                            'test' => 'alice',
                        ],
                        'recommend_2' => [
                            'control' => 'personalize',
                            'test' => 'alice',
                        ],
                    ],
                ],
            ]
        );
        $request = new Request();

        $this->setExperiments();

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function () {
                }
            );

        $experiment = $this->getExperiment();

        $this->assertTrue(
            in_array(
                $experiment['experiment_recommend'],
                array_keys(
                    config('experiments.tests.recommend')
                )
            )
        );

        $this->assertTrue(
            in_array(
                $experiment['experiment_recommend_2'],
                array_keys(
                    config('experiments.tests.recommend_2')
                )
            )
        );
    }

    public function testRandSwitcher()
    {
        $request = new Request();
        // this ensures control always get chosen
        mt_srand(0);

        $this->setExperiments();

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function () {
                }
            );

        $experiment = $this->getExperiment();

        $this
            ->assertEquals(
                'control',
                $experiment['experiment_recommend']
            );
    }

    /**
     * This is to ensure that a "test" candidate
     * doesn't get set to control
     */
    public function testPresetExperiment()
    {
        // this ensures control always get chosen
        mt_srand(0);

        $this
            ->app['request']
            ->cookies
            ->set(
                'experiments',
                json_encode(
                    [
                        'experiment_recommend' => 'test',
                    ]
                )
            );

        resolve(SetExperiment::class)
            ->handle(
                new Request(),
                function () {
                }
            );

        $experiment = $this->getExperiment();

        $this
            ->assertEquals(
                'test',
                $experiment['experiment_recommend']
            );
    }

    /**
     * This is to ensure that a "test" candidate for
     * several experiments doesn't get set to control
     */
    public function testPresetExperiments()
    {
        config(['experiments.tests.recommend_2' => []]);

        // this ensures control always get chosen
        mt_srand(0);

        $this
            ->app['request']
            ->cookies
            ->set(
                'experiments',
                json_encode(
                    [
                        'experiment_recommend' => 'test',
                        'experiment_recommend_2' => 'test',
                        'experiment_recommend_3' => 'test',
                    ]
                )
            );

        resolve(SetExperiment::class)
            ->handle(
                new Request(),
                function () {
                }
            );

        $this
            ->assertEquals(
                [
                    'experiment_recommend' => 'test',
                    'experiment_recommend_2' => 'test',
                    // recommend_3 should not appear, the exclusion is on purpose
                    //'experiment_recommend_3' => 'test',
                ],
                $this->getExperiment()
            );
    }

    protected function setExperiments() {
        foreach (array_keys(config('experiments.tests')) as $experiment) {
            SdcExperiments::getOrStartExperiment($experiment);
        }
    }

    protected function getExperiment()
    {
        /** @var CookieObject $cookie */
        $cookie = Arr::first(Cookie::getQueuedCookies('experiments'));

        return json_decode(
            $cookie->getValue(),
            true
        );
    }
}
