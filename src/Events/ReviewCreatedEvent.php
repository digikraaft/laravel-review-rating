<?php


namespace Digikraaft\ReviewRating\Events;

use Digikraaft\ReviewRating\Models\Review;

class ReviewCreatedEvent
{
    public Review $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }
}
