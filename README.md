# SDC Experiments Laravel Package 🧪

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
when running an experiment, you need to present the user with a particular experience. To check whether the user is in `test` or `control`cohorts use the helper `experiment_group` 
```php
if (experiment_group('my-experiment') == 'control') {
    // the control experience
} else {
    // the test experience
}
```

### Track the User's Group
In your tracking datalayer you need to send through an extra dimension that represents the user's experiment groups.
To do this use the `track_experiments` helper.
```php
track_experiments();
```
This will send through experiment data to GTM for reporting.
