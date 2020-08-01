<?php

namespace Digikraaft\ReviewRating;

use Digikraaft\ReviewRating\Exceptions\InvalidReviewModel;
use Digikraaft\ReviewRating\Models\Review;
use Illuminate\Support\ServiceProvider;

class ReviewRatingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/review-rating.php', 'review-rating');
    }

    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        }

        if (! class_exists('CreateReviewsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_reviews_table.php.stub' => database_path('migrations/'.$timestamp.'_create_reviews_table.php'),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . '/../config/review-rating.php' => config_path('review-rating.php'),
        ], 'config');

        $this->guardAgainstInvalidReviewModel();
    }

    public function guardAgainstInvalidReviewModel()
    {
        $modelClassName = config('review-rating.review_model');

        if (! is_a($modelClassName, Review::class, true)) {
            throw InvalidReviewModel::create($modelClassName);
        }
    }
}
