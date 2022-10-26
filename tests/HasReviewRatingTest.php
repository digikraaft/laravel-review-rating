<?php


namespace Digikraaft\ReviewRating\Tests;

use Digikraaft\ReviewRating\Events\ReviewCreatedEvent;
use Digikraaft\ReviewRating\Exceptions\InvalidDate;
use Digikraaft\ReviewRating\Tests\Models\AlternativeReviewModel;
use Digikraaft\ReviewRating\Tests\Models\CustomModelKeyReviewModel;
use Digikraaft\ReviewRating\Tests\Models\TestAuthorModel;
use Digikraaft\ReviewRating\Tests\Models\TestModel;
use Illuminate\Support\Facades\Event;

class HasReviewRatingTest extends TestCase
{
    /** @var TestModel */
    protected TestModel $testModel;
    protected TestAuthorModel $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testModel = TestModel::create([
            'name' => 'name',
        ]);

        $this->author = TestAuthorModel::create([
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function it_can_create_review_without_rating_and_title()
    {
        Event::fake();
        $review = $this->testModel
            ->review(
                'Some lovely review here',
                $this->author
            );
        $review = $review->latestReview()->review;

        $this->assertEquals('Some lovely review here', $review);
        $this->assertTrue($this->testModel->hasReview());
        Event::assertDispatched(ReviewCreatedEvent::class);
    }

    /** @test */
    public function it_can_create_review_with_rating()
    {
        $review = $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                5
            );
        $review = $review->latestReview();

        $this->assertEquals('Some lovely review here', $review->review);
        $this->assertEquals(5, $review->rating);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertTrue($this->testModel->hasRating());
    }

    /** @test */
    public function it_can_create_review_with_rating_and_title()
    {
        $review = $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );
        $review = $review->latestReview();

        $this->assertEquals('Some lovely review here', $review->review);
        $this->assertEquals(4, $review->rating);
        $this->assertEquals('Test title', $review->title);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertTrue($this->testModel->hasRating());
    }

    /** @test */
    public function it_can_handle_getting_a_review_when_there_are_none_set()
    {
        $this->assertNull($this->testModel->latestReview());
    }

    /** @test */
    public function it_can_return_the_latest_review()
    {
        $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );
        $this->assertEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );

        $this->testModel
            ->review(
                'Some lovely review here again',
                $this->author,
                5,
                'Test title 2'
            );
        $this->assertNotEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );

        $this->assertEquals(
            'Some lovely review here again',
            $this->testModel->latestReview()->review
        );
    }

    /** @test */
    public function it_can_return_all_reviews()
    {
        $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );
        $this->assertEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );

        $this->testModel
            ->review(
                'Some lovely review here again',
                $this->author,
                5,
                'Test title 2'
            );
        $this->assertNotEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );
        $this->assertEquals(2, $this->testModel->reviews()->count());
    }
    /** @test */
    public function it_can_use_a_different_review_model()
    {
        $this->app['config']->set(
            'review-rating.review_model',
            AlternativeReviewModel::class
        );

        $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );
        $this->assertInstanceOf(AlternativeReviewModel::class, $this->testModel->latestReview());
    }

    /** @test */
    public function it_can_use_a_custom_name_for_the_relationship_id_column()
    {
        $this->app['config']->set(
            'review-rating.review_model',
            CustomModelKeyReviewModel::class
        );

        $this->app['config']->set(
            'review-rating.model_primary_key_attribute',
            'model_custom_fk'
        );

        $review = $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );

        $this->assertEquals($review->id, $review->latestReview()->model_custom_fk);
        $this->assertTrue($review->latestReview()->is(CustomModelKeyReviewModel::first()));
    }

    /** @test */
    public function it_uses_the_default_relationship_id_column_when_configuration_value_is_no_present()
    {
        $this->app['config']->offsetUnset('review-rating.model_primary_key_attribute');

        $review = $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );

        $this->assertEquals('Some lovely review here', $review->latestReview()->review);
        $this->assertEquals($review->id, $review->latestReview()->model_id);
    }

    /** @test */
    public function it_can_return_number_of_reviews()
    {
        $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );
        $this->assertEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );

        $this->testModel
            ->review(
                'Some lovely review here again',
                $this->author,
                5,
                'Test title 2'
            );
        $this->assertNotEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );

        $this->assertEquals(2, $this->testModel->numberOfReviews());
    }

    /** @test */
    public function it_throws_error_when_from_date_is_later_than_to_date()
    {
        $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );
        $this->assertEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );

        $this->testModel
            ->review(
                'Some lovely review here again',
                $this->author,
                5,
                'Test title 2'
            );
        $this->assertNotEquals(
            'Some lovely review here',
            $this->testModel->latestReview()->review
        );

        $this->expectException(InvalidDate::class);
        $this->testModel->numberOfReviews(now(), now()->subDays(2));
    }

    /** @test */
    public function it_can_return_number_of_reviews_over_a_period()
    {
        $this->testModel->reviews()->create([
            'review' => 'Lovely review 1',
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonths(2),
        ]);
        $this->assertTrue($this->testModel->hasReview());

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 2',
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonth(),
        ]);
        $this->assertTrue($this->testModel->hasReview());

        $this->assertEquals(
            2,
            $this->testModel->numberOfReviews(now()->subMonths(2), now()->subMonth())
        );

        $this->assertEquals(
            1,
            $this->testModel->numberOfReviews(now()->subMonth(), now())
        );
    }

    /** @test */
    public function it_can_get_scoped_reviews()
    {
        $model = TestModel::create(['name' => 'Tim O']);
        $model->review(
            'Some lovely review 1',
            $this->author,
            5,
            'Test title 1'
        );
        $this->assertEquals('Some lovely review 1', $model->latestReview()->review);
        $this->assertEquals(1, TestModel::allReviews()->count());

        $model = TestModel::create(['name' => 'Digikraaft']);
        $model->review(
            'Some lovely review 2',
            $this->author,
            5,
            'Test title 2'
        );
        $this->assertEquals('Some lovely review 2', $model->latestReview()->review);
        $this->assertEquals(2, TestModel::allReviews()->count());

        $model = TestModel::create(['name' => 'Digikraaft NG']);
        $model->review(
            'Some lovely review 3',
            $this->author,
            5,
            'Test title 3'
        );
        $this->assertEquals('Some lovely review 3', $model->latestReview()->review);
        $this->assertEquals(3, TestModel::allReviews()->count());
    }

    /** @test */
    public function it_can_return_number_of_ratings()
    {
        $this->testModel->reviews()->create([
            'review' => 'Lovely review 1',
            'rating' => 5,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonths(2),
        ]);
        $this->assertTrue($this->testModel->hasReview());

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 2',
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonth(),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(1, $this->testModel->numberOfRatings());

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 3',
            'rating' => 4,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonth(),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(2, $this->testModel->numberOfRatings());
    }

    /** @test */
    public function it_can_return_number_of_ratings_over_a_period()
    {
        $this->testModel->reviews()->create([
            'review' => 'Lovely review 1',
            'rating' => 5,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonths(2),
        ]);
        $this->assertTrue($this->testModel->hasReview());

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 2',
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonth(),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(1, $this->testModel->numberOfRatings());

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 3',
            'rating' => 4,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now(),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(2, $this->testModel->numberOfRatings());

        $this->assertEquals(
            1,
            $this->testModel->numberOfRatings(now()->subMonths(2), now()->subMonth())
        );

        $this->assertEquals(
            1,
            $this->testModel->numberOfRatings(now()->subMonth(), now())
        );
    }

    /** @test */
    public function it_can_get_scoped_reviews_with_rating()
    {
        $model = TestModel::create(['name' => 'Tim O']);
        $model->review(
            'Some lovely review 1',
            $this->author,
            5,
            'Test title 1'
        );
        $this->assertEquals('Some lovely review 1', $model->latestReview()->review);
        $this->assertEquals(1, TestModel::allReviews()->count());
        $this->assertEquals(1, TestModel::withRatings()->count());

        $model = TestModel::create(['name' => 'Digikraaft']);
        $model->review(
            'Some lovely review 2',
            $this->author,
            5,
            'Test title 2'
        );
        $this->assertEquals('Some lovely review 2', $model->latestReview()->review);
        $this->assertEquals(2, TestModel::allReviews()->count());
        $this->assertEquals(2, TestModel::withRatings()->count());

        $model = TestModel::create(['name' => 'Digikraaft NG']);
        $model->review(
            'Some lovely review 3',
            $this->author,
        );
        $this->assertEquals('Some lovely review 3', $model->latestReview()->review);
        $this->assertEquals(2, TestModel::withRatings()->count());
    }

    /** @test */
    public function it_can_get_average_rating()
    {
        $this->testModel->reviews()->create([
            'review' => 'Lovely review 1',
            'rating' => 5,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(5, $this->testModel->averageRating());

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 2',
            'rating' => 4,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(2, $this->testModel->numberOfRatings());
        $this->assertEquals(4.5, $this->testModel->averageRating());

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 3',
            'rating' => 5,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now(),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(3, $this->testModel->numberOfRatings());
        $this->assertEquals(4.667, $this->testModel->averageRating(3));
        $this->assertEquals(4.67, $this->testModel->averageRating(2));
    }

    /** @test */
    public function it_can_get_average_rating_over_a_period()
    {
        $this->testModel->reviews()->create([
            'review' => 'Lovely review 1',
            'rating' => 5,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonths(2),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(
            5,
            $this->testModel->averageRating(
                null,
                now()->subMonths(2),
                now()
            )
        );

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 2',
            'rating' => 4,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now()->subMonth(),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(2, $this->testModel->numberOfRatings());
        $this->assertEquals(
            4.5,
            $this->testModel->averageRating(
                null,
                now()->subMonths(2),
                now()->subMonth()
            )
        );

        $this->testModel->reviews()->create([
            'review' => 'Lovely review 3',
            'rating' => 5,
            'author_type' => $this->author,
            'author_id' => $this->author->id,
            'created_at' => now(),
        ]);
        $this->assertTrue($this->testModel->hasReview());
        $this->assertEquals(3, $this->testModel->numberOfRatings());
        $this->assertEquals(
            4.5,
            $this->testModel->averageRating(
                null,
                now()->subMonth(),
                now()
            )
        );
        $this->assertEquals(
            4.67,
            $this->testModel->averageRating(
                2,
                now()->subMonths(2),
                now()
            )
        );
    }

    /** @test */
    public function it_can_check_if_user_has_reviewed()
    {
        $this->testModel
            ->review(
                'Some lovely review here',
                $this->author,
                4,
                'Test title'
            );
        $this->assertTrue($this->testModel->hasReviewed($this->author));
    }
}
