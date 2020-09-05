<?php

namespace Ringierimu\Experiments\Tests\Unit\Http\Middleware;

use Ringierimu\Experiments\Http\Middleware\SetExperiment;
use Ringierimu\Experiments\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as CookieObject;

class SetExperimentTest extends TestCase
{
    public function testSuccess()
    {
        $request = new Request();

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function (){}
            );

        $experiment = $this->getExperiment();

        $this->assertTrue(
            in_array(
                $experiment->experiment_recommend,
                array_keys(
                    config('experiments.recommend')
                )
            )
        );
    }

    public function testSuccessSeveralExperiments()
    {
        config(
            [
                'experiments' => [
                    'recommend' => [
                        'control' => 'personalize',
                        'test' => 'alice',
                    ],
                    'recommend_2' => [
                        'control' => 'personalize',
                        'test' => 'alice',
                    ],
                ],
            ]
        );
        $request = new Request();

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function (){}
            );

        $experiment = $this->getExperiment();

        $this->assertTrue(
            in_array(
                $experiment->experiment_recommend,
                array_keys(
                    config('experiments.recommend')
                )
            )
        );

        $this->assertTrue(
            in_array(
                $experiment->experiment_recommend_2,
                array_keys(
                    config('experiments.recommend_2')
                )
            )
        );
    }

    public function testRandSwitcher()
    {
        $request = new Request();
        // this ensures control always get chosen
        mt_srand(0);

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function (){}
            );

        $experiment = $this->getExperiment();

        $this
            ->assertEquals(
                'control',
                $experiment->experiment_recommend
            );
    }

    /**
     * This is to ensure that a "test" candidate
     * doesn't get set to control
     */
    public function testPresetExperiment()
    {
        $request = new Request();
        // this ensures control always get chosen
        mt_srand(0);

        $request
            ->cookies
            ->set(
                'experiments',
                json_encode([
                    'experiment_recommend' => 'test',
                ])
            );

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function (){}
            );

        $experiment = $this->getExperiment();

        $this
            ->assertEquals(
                'test',
                $experiment->experiment_recommend
            );
    }

    /**
     * This is to ensure that a "test" candidate for
     * several experiments doesn't get set to control
     */
    public function testPresetExperiments()
    {
        $request = new Request();
        // this ensures control always get chosen
        mt_srand(0);

        $request
            ->cookies
            ->set(
                'experiments',
                json_encode([
                    'experiment_recommend' => 'test',
                    'experiment_recommend_2' => 'test',
                ])
            );

        resolve(SetExperiment::class)
            ->handle(
                $request,
                function (){}
            );

        $experiment = $this->getExperiment();

        $this
            ->assertEquals(
                'test',
                $experiment->experiment_recommend
            );

        $this
            ->assertEquals(
                'test',
                $experiment->experiment_recommend_2
            );
    }

    protected function getExperiment()
    {
        /** @var CookieObject $cookie */
        $cookie = array_first(Cookie::getQueuedCookies('experiments'));

        return json_decode($cookie->getValue());
    }
}
