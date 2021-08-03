<?php

use Ringierimu\Experiments\Facades\SdcExperiments;

if (!function_exists('sdc_search_config')) {
    /**
     * Get the SDC Search config merged from regular config and experiment config
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    function sdc_search_config(
        string $key,
        $default = null
    ) {
        $testGroup = SdcExperiments::getOrStartExperiment('sdc_search') ?: 'control';

        $testGroupConfigKey = sprintf(
            'experiments.tests.sdc_search.%s',
            $testGroup
        );

        return data_get(
            array_replace_recursive(
                config('sdc_search') ?: [],
                config($testGroupConfigKey) ?: []
            ),
            $key,
            $default
        );
    }
}
