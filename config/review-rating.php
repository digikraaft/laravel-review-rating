<?php

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
