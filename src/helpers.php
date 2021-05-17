<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

if (!function_exists('experiment_group')) {
    /**
     * Return what user group the current
     * user is for a given experiment.
     * If none set return null
     *
     * @param string $experimentName
     *
     * @return string|null
     */
    function experiment_group(
        string $experimentName
    ): ?string {
        $experiments = $_COOKIE['experiments'] ?? '';
        $experiment = Arr::get(
            (array) json_decode($experiments),
            $experimentName
        );

        if (!$experiment) {
            return null;
        }

        if (mb_strstr($experiment, 'test')) {
            return 'test';
        }

        if (mb_strstr($experiment, 'internal')) {
            return 'internal';
        }

        return 'control';
    }
}

if (!function_exists('track_experiments')) {
    /**
     * Set experiment values in the form of
     *
     * {
     *      "experiments": {
     *          "experiment-1": "test",
     *          "experiment-2": "control"
     *      }
     * }
     */
    function track_experiments()
    {
        $experiments = $_COOKIE['experiments'] ?? null;

        if (!$experiments) {
            return;
        }

        $runningExperiments = get_running_experiments();

        Collection::make((array) json_decode($experiments))
            ->filter(
                function ($value, $key) use ($runningExperiments) {
                    return in_array(
                        ltrim($key, 'experiment_'),
                        $runningExperiments
                    );
                }
            )
            ->each(
                function ($value, $key) {
                    GoogleTagManagerFacade::set('sdc_' . $key, $value);
                }
            );
    }
}

if (!function_exists('get_running_experiments')) {
    /**
     * Get all running sdc experiments
     */
    function get_running_experiments(): array
    {
        return array_keys(
            config(
                'experiments',
                []
            )
        );
    }
}
