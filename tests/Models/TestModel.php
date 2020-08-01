<?php

namespace Digikraaft\ReviewRating\Tests\Models;

use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasReviewRating;
    protected $table = 'designs';
    protected $guarded = [];
}
