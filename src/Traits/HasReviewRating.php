<?php

namespace Digikraaft\ReviewRating\Traits;

use Carbon\Carbon;
use Digikraaft\ReviewRating\Events\ReviewCreatedEvent;
use Digikraaft\ReviewRating\Exceptions\InvalidDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

trait HasReviewRating
{
    public function reviews(): MorphMany
    {
        return $this->morphMany($this->getReviewModelClassName(), 'model', 'model_type', $this->getReviewKeyColumnName())
            ->latest('id');
    }

    public function latestReview()
    {
        return $this->reviews()->first();
    }

    /**
     * @param string $review
     * @param Model $author
     * @param float|null $rating
     * @param string|null $title
     * @return $this
     */
    public function review(string $review, Model $author, ?float $rating = null, ?string $title = null): self
    {
        return $this->createReview($review, $author, $rating, $title);
    }

    private function createReview($review, $author, $rating, $title)
    {
        $keyName = $author->getKeyName();
        $this->reviews()->create([
            'review' => $review,
            'author_id' => $author->$keyName,
            'author_type' => $author->getMorphClass(),
            'rating' => $rating,
            'title' => $title,
        ]);

        event(new ReviewCreatedEvent($this->latestReview()));

        return $this;
    }

    public function hasReview(): bool
    {
        return $this->reviews()->count() > 0;
    }

    public function hasReviewed(Model $author): bool
    {
        $keyName = $author->getKeyName();

        return $this->reviews()
            ->where('author_id', $author->$keyName)
            ->where('author_type', $author->getMorphClass())
            ->count() > 0;
    }

    public function hasRating(): bool
    {
        return $this->reviews()
            ->whereNotNull('rating')
            ->count() > 0;
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return int
     * @throws InvalidDate
     */
    public function numberOfReviews(?Carbon $from = null, ?Carbon $to = null): int
    {
        if (! $from && ! $to) {
            return $this->reviews()->count();
        }

        if ($from->greaterThan($to)) {
            throw InvalidDate::from();
        }

        return $this->reviews()
            ->whereBetween(
                'created_at',
                [$from->toDateTimeString(), $to->toDateTimeString()]
            )->count();
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return int
     * @throws InvalidDate
     */
    public function numberOfRatings(?Carbon $from = null, ?Carbon $to = null): int
    {
        if (! $from && ! $to) {
            return $this->reviews()
                ->whereNotNull('rating')
                ->count();
        }

        if ($from->greaterThan($to)) {
            throw InvalidDate::from();
        }

        return $this->reviews()
            ->whereNotNull('rating')
            ->whereBetween(
                'created_at',
                [$from->toDateTimeString(), $to->toDateTimeString()]
            )->count();
    }

    public function averageRating(?int $round = null, ?Carbon $from = null, ?Carbon $to = null): ?float
    {
        if ($round) {
            if (! $from && ! $to) {
                return round($this->reviews()->avg('rating'), $round);
            }

            return round($this->reviews()
                ->whereBetween('created_at', [$from->toDateTimeString(), $to->toDateTimeString()])
                ->avg('rating'), $round);
        }

        if (! $from && ! $to) {
            return $this->reviews()->avg('rating');
        }

        return $this->reviews()
            ->whereBetween(
                'created_at',
                [$from->toDateTimeString(), $to->toDateTimeString()]
            )
            ->avg('rating');
    }

    protected function getReviewTableName(): string
    {
        $modelClass = $this->getReviewModelClassName();

        return (new $modelClass)->getTable();
    }

    protected function getReviewKeyColumnName(): string
    {
        return config('review-rating.model_primary_key_attribute') ?? 'model_id';
    }

    protected function getReviewModelClassName(): string
    {
        return config('review-rating.review_model');
    }

    protected function getReviewModelType(): string
    {
        return array_search(static::class, Relation::morphMap()) ?: static::class;
    }

    public function scopeAllReviews(Builder $builder)
    {
        $builder
            ->whereHas(
                'reviews',
                function (Builder $query) {
                    $query
                        ->whereIn(
                            'id',
                            function (QueryBuilder $query) {
                                $query
                                    ->select(DB::raw('max(id)'))
                                    ->from($this->getReviewTableName())
                                    ->where('model_type', $this->getReviewModelType())
                                    ->whereColumn($this->getReviewKeyColumnName(), $this->getQualifiedKeyName());
                            }
                        );
                }
            );
    }

    public function scopeWithRatings(Builder $builder)
    {
        $builder
            ->whereHas(
                'reviews',
                function (Builder $query) {
                    $query
                        ->whereIn(
                            'id',
                            function (QueryBuilder $query) {
                                $query
                                    ->select(DB::raw('max(id)'))
                                    ->from($this->getReviewTableName())
                                    ->where('model_type', $this->getReviewModelType())
                                    ->whereColumn($this->getReviewKeyColumnName(), $this->getQualifiedKeyName());
                            }
                        )->whereNotNull('rating');
                }
            );
    }
}
