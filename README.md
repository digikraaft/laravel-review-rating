# Add Review and Rating Feature to your Laravel application
![tests](https://github.com/digikraaft/laravel-review-rating/workflows/tests/badge.svg?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/digikraaft/laravel-review-rating/badges/build.png?b=master)](https://scrutinizer-ci.com/g/digikraaft/laravel-model-suspension/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/digikraaft/laravel-review-rating/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/digikraaft/laravel-model-suspension/?branch=master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/digikraaft/laravel-review-rating/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

## Review and Rating System for Laravel
This package provides a simple review and rating system for Laravel. It supports
Laravel 5.8 an up. Here is a quick demonstration of how it can be used:

```php
//create a review
$author = User::find(1);
$review = "Awesome package! I highly recommend it!!";

$model->review($review, $author);

//write a review and include a rating
$model->review($review, $author, 5);

//write a review and include a rating and a title
$model->review($review, $author, 5, "Lovely packages");

//get the last review
$model->latestReview(); //returns an instance of \Digikraaft\ReviewRating\Review

//get the review content of the last review
$model->latestReview()->review; //returns 'Awesome package! I highly recommend it!!'

//get the rating of the last review
$model->latestReview()->rating; //return 5

//get the title of the last review
$model->latestReview()->title; //returns 'Lovely packages'
```

## Installation

You can install the package via composer:

```bash
composer require digikraaft/laravel-review-rating
```
You must publish the migration with:
```bash
php artisan vendor:publish --provider="Digikraaft\ReviewRating\ReviewRatingServiceProvider" --tag="migrations"
```
Run the migration to publish the `reviews` table with:
```bash
php artisan migrate
```
You can optionally publish the config-file with:
```bash
php artisan vendor:publish --provider="Digikraaft\ReviewRating\ReviewRatingServiceProvider" --tag="config"
```
The content of the file that will be published to `config/review-rating.php`:
```php
return [
    /*
      * The class name of the review model that holds all reviews.
      *
      * The model must be or extend `Digikraaft\ReviewRating\Review`.
      */
    'review_model' => Digikraaft\ReviewRating\Models\Review::class,

    /*
     * The name of the column which holds the ID of the model related to the reviews.
     *
     * Only change this value if you have set a different name in the migration for the reviews table.
     */
    'model_primary_key_attribute' => 'model_id',

];
```
## Usage
Add the `HasReviewRating` trait to the model:
```php
use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{
    use HasReviewRating;
}
```

### Create a review
To create a review, use the `review` function of the trait.
Like this:
```php
$author = User::find(1);
$review = "Awesome package! I highly recommend it!!";

$model->review($review, $author);
```
The first argument is the content of the review while the second argument is the author.
This can be any Eloquent model.

To create a review with rating, pass in the rating value as the third argument of
the `review` function. Valid values are `int`s and `float`s:
```php
$author = User::find(1);
$review = "Awesome package! I highly recommend it!!";

$model->review($review, $author, 5);
```

To create a review with rating and title, add the title as the fourth argument
of the `review` function:
```php
$author = User::find(1);
$review = "Awesome package! I highly recommend it!!";

$model->review($review, $author, 5, "Lovely packages");
```

You can also check if user has reviewed the model by using the `hasReviewed` function:
```php
    if ($model->hasReviewed(auth()->user())) {
        // user has reviewed the model
    }
```

### Retrieving reviews
You can get the last review like this:
```php
$model->latestReview(); //returns the latest instance of Digikraaft\ReviewRating\Review
```
The content of the review can be gotten like this:
```php
$model->latestReview()->review;
```
To get the rating for the review, do this:
```php
$model->latestReview()->rating;
```
To get the title of the review:
```php
$model->latestReview()->title;
```
All reviews can be retrieved like this:
```php
$model->reviews;
```
To access each review from the reviews retrieved, do this:
```php
$reviews = $model->reviews;

foreach($reviews as $review){
    echo $review->review . "<br>";
}
```
The `allReviews` scope can be used to retrieve all the reviews for all instances of a model:
```php
$allReviews = EloquentModel::allReviews();
```
### Retrieving basic Review Stats
You can get the number of reviews a model has:
```php
$model->numberOfReviews();
```
To get the number of reviews a model has received over a period,
pass in a `Carbon` formatted `$from` and `$to` dates as the first and second
arguments respectively:
```php
//get the number of reviews a model has received over the last month
$from = now()->subMonth();
$to = now();

$model->numberOfReviews($from, $to);
```
Note that an `InvalidDate` exception will be thrown if the `$from` date is later than the `$to`

You can get the number of ratings a model has:
```php
$model->numberOfRatings();
```
To get the number of ratings a model has received over a period,
pass in a `Carbon` formatted `$from` and `$to` dates as the first and second
arguments respectively:
```php
//get the number of reviews a model has received over the last month
$from = now()->subMonth();
$to = now();

$model->numberOfRatings($from, $to);
```
To get the average rating a model has received:
```php
$model->averageRating();
```
The average rating that is returned is by default not rounded.
If you would like to `round` the returned result, pass an integer value of the
decimal place you want it rounded to.
```php
//round up to 2 decimal places
$model->averageRating(2);
```
To get the average rating a model has received over a period,
pass in a `Carbon` formatted `$from` and `$to` dates as the first and second
arguments respectively:
```php
//get the average rating a model has received over the last month, rounded to 2 decimal places:
$from = now()->subMonth();
$to = now();

$model->averageRating(2, $from, $to);
```
The `withRatings` scope can be used to retrieve all the reviews that have a rating for all instances of a model:
```php
$allReviewsWithRating = EloquentModel::withRatings();
```

### Check if model has review
You can check if a model has at least one review:
```php
$model->hasReview();
```
### Check if model has rating
You can check if a model has at least one rating:
```php
$model->hasRating();
```

### Events
The `Digikraaft\ReviewRating\Events\ReviewCreatedEvent` event will be dispatched when 
a review has been created. You can listen to this event and take necessary actions.
An instance of the review will be passed to the event class and can be accessed for use:
```php
namespace Digikraaft\ReviewRating\Events;

use Digikraaft\ReviewRating\Models\Review;
use Illuminate\Database\Eloquent\Model;

class ReviewCreatedEvent
{
    /** @var \Digikraaft\ReviewRating\Models\Review */
    public Review $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }
}
```

### Custom model and migration
You can change the model used by specifying a different class name in the 
`review_model` key of the `review-rating` config file.

You can also change the column name used in the `reviews` table 
(default is `model_id`) when using a custom migration. If this is the case,
also change the `model_primary_key_attribute` key of the `review-rating` config file.

## Testing
Use the command below to run your tests:
```bash
composer test
```

## More Good Stuff
Check [here](https://github.com/digikraaft) for more awesome free stuff!

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email hello@digikraaft.ng instead of using the issue tracker.

## Credits
- [Tim Oladoyinbo](https://github.com/timoladoyinbo)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
