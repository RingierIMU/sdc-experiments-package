# SDC Recommend Laravel Package 📈

## About The Project

A Laravel package to request recommendations and send interactions using the Search and Data Cube API

### Prerequisites
- Laravel 7+
- SDC API credentials supplied to you

### Installation
Add the following snippet to your project's `composer.json` file in the `repositories` node (this is in order to use the private repo as a composer package)

```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:RingierIMU/sdc-recommend-package.git"
    }
]
```
Then run the following in your project route:
```php
composer require ringierimu/recommend
```

After all the dependencies are brought down, install the recommend package by running

```php
php artisan recommend:install
```
This will import the `config/recommend.php` file into your application

Update `api_key` in `config/recommend.php` to the API key supplied to you. You will receive staging and production
API keys, so for production, if you have a environment config override, set `recommend.api_key` to the production
API Key.

## Usage

### Requesting recommendations
```php
$recommendations = get_recommendations($listing);
```

The default recipe for recommendation is `related_items` which is the only recipe available as of writing.
If another recipe comes about down the line and is available to you, you can request the recipe using:

```php
$recommendations = get_recommendations($listing, 'other_recipe');
```

This will return an array of recommended entity IDs. Please note sometimes these entities may have changed status
on your platform so you will need to filter out the ones you want to display on your platform. We suggest setting up an
AJAX endpoint which calls this function that your frontend can consume to display these recommendations.

### Sending interactions

We require user behaviour interactions to train models for your recommendations.

There are 2 ways to go about this. You can send us these interactions, eg listing_view directly to our API or
we can attempt to decipher these from your Google Analytics properties.

#### Directly to our API
```php
send_recommendation_interaction($listing, 'listing_view');
```

This will queue a job to send an interaction for the given entity for a specific event type to our API. 
This is the preferred way for us to gather the interactions. We do understand interactions like listing 
views could clog up job queues or perhaps have other side effects, so if you don't feel comfortable using 
this approach then the Google Analytics approach must be used.

### Google Analytics

Unless, it is very easy to definitively obtain the interactions from Google Analytics (there is a already custom 
dimension or event category we can use), we would require a special event category or custom dimension to be set up, 
which your application must then set at the time of these interactions on your platform.

### Optional Config

`enabled` - To toggle off the recommendation module.
`interactions_queue` - If you are sending interactions to our API and have set up a special queue for these sorts of 
jobs, you can configure this queue in the config.
