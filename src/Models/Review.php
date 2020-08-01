<?php

namespace Digikraaft\ReviewRating\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $guarded = [];
    protected $table = 'reviews';

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
