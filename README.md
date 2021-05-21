# SDC Experiments Laravel Package 🧪
![Tests](https://github.com/RingierIMU/sdc-experiments-package/workflows/Tests/badge.svg) ![Lint Code Base](https://github.com/RingierIMU/sdc-experiments-package/workflows/Lint%20Code%20Base/badge.svg)
## About The Project

A Laravel package to determine a user's experiment group and track that user's group in Google Tag Manager

### Prerequisites
- Laravel 7+

### Installation
Run the following in your project route:
```php
composer require ringierimu/experiments
```

After all the dependencies have been brought down, install the experiments package by running

```php
php artisan experiments:install
```
This will import the `config/experiments.php` file into your application

Update your config `config/experiments.php` to include your running experiments. For example, for a `recommend` experiment your config will look like this
```php
<?php

return [
    'recommend' => [
        'control' => 'personalize',
        'test' => 'alice',
    ],
];
```

The array keys are your running experiments, the array value is the `varaints` (optional). If you want to map a particular user group to a key. In the case of a recommendation engine, you can map user groups to engine variants. If you don't need variants, you can just leave a blank array
```php
return [
    'recommend' => [],
]
```

## Usage
### Add the middleware
Add this to your `web` Http Kernel middleware groups.

```php
 protected $middlewareGroups = [
        'web' => [
            Ringierimu\Experiments\Http\Middleware\SetExperiment::class
```
This will ensure the user's cookie has the correct experiment groups set.

### Check experiment group
when running an experiment, you need to present the user with a particular experience. To check whether the user is in `test` or `control`cohorts use the helper `SdcExperiments::getOrStartExperiment`
```php
if (SdcExperiments::getOrStartExperiment('my-experiment') == 'test') {
    // the test experience
} else {
    // the control experience OR the experiment is not running
}
```

### Track the User's Group
In your tracking datalayer you need to send through an extra dimension that represents the user's experiment groups.
To do this use the `track_experiments` helper.
```php
SdcExperiments::googleTagManagerSetTrackingVars();
```
This will send through experiment data to GTM for reporting.

### Optionally setup Google Optimize Server Side tracking
As an additional extra you can also track your experiment via Google Optimize with their Server Side tracking configuration.
More information about the required experiment settings can be found in the official [Google Optimize docs](https://developers.google.com/optimize/devguides/experiments).

Assuming you have followed the steps and have an experiment setup and ready to run you can now define how the A/B tests you have setup should be grouped and reported.

Only 2 buckets are supported at this tim for each a/b test, 0=control, 1=test

#### Scenario - A/B test

in `config/experiments.php` define a new group with a readable key (this can be the same key that you used for the a/b test itself)

```php
[
    'tests' => [
        'ab_test' => [],
    ],
    'groups' => [
        'ab_test_nice_group_name' => [
            'id' => 'GOOGLE_OPTIMIZE_EXPERIMENT_ID',
            'variations' => [
                'ab_test',
            ],
        ],
    ],
]
```

Assuming the user is in the control group
The GTM tracking vars created for this will appear as follows (other vars snipped for clarity):
```js
window.dataLayer = [
    {
        "experiments": {
            "ab_test": "control"
        },
        "ga_optimize_exp": "GOOGLE_OPTIMIZE_EXPERIMENT_ID.0",
        "experiments_running": "ab_test_nice_group_name.0"
    }
];
```

#### Scenario - Multivariant test

building upon the previous example, assuming we want to run a multivariant test (multiple a/b tests simultaneously with similar or connected changes)

```php
[
    'tests' => [
        'ab_test' => [],
        'large_font' => [],
        'obnoxious_colours' => [],
    ],
    'groups' => [
        'ab_test_nice_group_name' => [
            'id' => 'GOOGLE_OPTIMIZE_EXPERIMENT_ID',
            'variations' => [
                'ab_test',
            ],
        ],
        'style_test' => [
            'id' => 'MULTIVARIANT_GOOGLE_OPTIMIZE_EXPERIMENT_ID',
            'variations' => [
                'large_font',
                'obnoxious_colours',
            ],
        ],
    ],
]
```

Assuming the user is in the `control` group for `ab_test`, `test` group for `large_font`, and `control` group for `obnoxious_colours`  
The GTM tracking vars created for this will appear as follows (other vars snipped for clarity):
```js
window.dataLayer = [
    {
        "experiments": {
            "ab_test": "control",
            "large_font": "test",
            "obnoxious_colours": "control"
        },
        "ga_optimize_exp": "GOOGLE_OPTIMIZE_EXPERIMENT_ID.0!MULTIVARIANT_GOOGLE_OPTIMIZE_EXPERIMENT_ID.1-0",
        "experiments_running": "ab_test_nice_group_name.0!style_test.1-0"
    }
];
```

#### GTM Setup
GA needs the value of `ga_optimize_exp` from the dataLayer to be set to the `exp` field.
Running GA directly it would look a bit like `ga('set', 'exp', 'GOOGLE_OPTIMIZE_EXPERIMENT_ID.0!MULTIVARIANT_GOOGLE_OPTIMIZE_EXPERIMENT_ID.1-0');'`
Via GTM, in the Google Analytics settings var, under `Fields to Set`, set the field name `exp` to the value of the dataLayer variable `ga_optimize_exp`  
The other vars pushed to the dataLayer are convenience vars that you could use to segment user traffic, call additional GTM scripts, etc
Access specific experiment values via `experiments.obnoxious_colours` (for example)
