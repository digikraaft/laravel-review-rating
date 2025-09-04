<?php

namespace Digikraaft\ReviewRating\Tests;

use Digikraaft\ReviewRating\ReviewRatingServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [ReviewRatingServiceProvider::class];
    }

    protected function setUpDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_model_key_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('review');
            $table->integer('rating')->nullable();
            $table->morphs('author');

            $table->string('model_type');
            $table->unsignedBigInteger('model_custom_fk');
            $table->index(['model_type', 'model_custom_fk']);

            $table->timestamps();
            $table->softDeletes();
        });

        include_once __DIR__ . '/../database/migrations/create_reviews_table.php.stub';

        (new \CreateReviewsTable)->up();
    }
}
