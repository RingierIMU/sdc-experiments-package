<?php

namespace Ringierimu\Experiments;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class Experiments
{
    /**
     * Don't reprocess the old cookie withing the same request
     *
     * @var array
     */
    protected $cookieUserExperiments;

    /**
     * Contains the current canonical user test assignments
     *
     * @var array
     */
    protected $userExperiments;

    /**
     * Experiments the user is currently enrolled in
     *
     * @return string[]
     */
    public function runningExperiments(): array
    {
        return array_keys(
            $this->userExperiments()
        );
    }

    /**
     * Get the available experiments the user is currently enrolled in and their assignments for each one respectively
     *
     * @return array
     */
    public function userExperiments(): array
    {
        if (!isset($this->userExperiments)) {
            $this->userExperiments = $this->getCookieUserAssignments();
        }

        return $this->userExperiments;
    }

    /**
     * @return array
     */
    protected function getCookieUserAssignments(): array
    {
        if (!isset($this->cookieUserExperiments)) {
            $experiments = (array) json_decode(Cookie::get('experiments') ?: '');

            $this->cookieUserExperiments = Collection::make($experiments)
                ->mapWithKeys(
                    function ($assignment, $experiment) {
                        return [
                            Str::after($experiment, 'experiment_') => $assignment,
                        ];
                    }
                )
                ->only($this->availableExperiments())
                ->filter(
                    function ($assignment) {
                        return in_array(
                            $assignment,
                            [
                                'control',
                                'test',
                                'internal',
                            ]
                        );
                    }
                )
                ->filter()
                ->all();
        }

        return $this->cookieUserExperiments;
    }

    /**
     * Get the experiments available for users to be enrolled in
     *
     * @return string[]
     */
    public function availableExperiments(): array
    {
        return array_keys(
            config('experiments') ?: []
        );
    }

    /**
     * String value of the experiment if it's an available experiment
     * null is the experiment is not running
     *
     * @param string $experiment
     *
     * @return string|null
     */
    public function getOrStartExperiment(
        string $experiment
    ): ?string {
        if (!in_array($experiment, $this->availableExperiments())) {
            return null;
        }

        $assignment = $this->getExperiment($experiment);
        if (is_null($assignment)) {
            $assignment = $this->getRandomAssignment();

            $this->setExperimentAssignment($experiment, $assignment);
        }

        return $assignment;
    }

    /**
     * String value of the experiment if it's an available experiment AND the user is CURRENTLY assigned
     * null is the experiment is not running OR the user has not been assigned to it
     *
     * @param string $experiment
     *
     * @return string|null
     */
    public function getExperiment(
        string $experiment
    ): ?string {
        return $this->userExperiments()[$experiment] ?? null;
    }

    /**
     * @return string
     */
    protected function getRandomAssignment(): string
    {
        return mt_rand(0, 1)
            ? 'test'
            : 'control';
    }

    /**
     * @param string $experiment
     * @param string $assignment
     */
    protected function setExperimentAssignment(
        string $experiment,
        string $assignment
    ) {
        $this->userExperiments[$experiment] = $assignment;
    }

    public function queueRequestCookie()
    {
        $data = Collection::make($this->userExperiments())
            ->flatMap(
                function ($assignment, $experiment) {
                    return ['experiment_' . $experiment => $assignment];
                }
            )
            ->all();

        Cookie::queue(
            'experiments',
            json_encode($data),
            2628000 // forever
        );
    }

    public function googleTagManagerSetTrackingVars()
    {
        GoogleTagManagerFacade::set(
            'experiments',
            $this->userExperiments()
        );
    }
}
