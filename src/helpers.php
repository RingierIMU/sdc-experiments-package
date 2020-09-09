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

        Collection::make((array) json_decode($experiments))
            ->each(
                function ($value, $key) {
                    GoogleTagManagerFacade::set('sdc_' . $key, $value);
                }
            );
    }
}
