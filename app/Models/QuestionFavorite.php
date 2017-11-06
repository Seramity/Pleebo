<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class QuestionFavorite
 *
 * QuestionFavorite model that extends off of the Eloquent package.
 * Deals with SQL tasks and other various functions for questions.
 *
 * @package App\Models
 */
class QuestionFavorite extends Model
{
    /**
     * Usable Lists database columns.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'question_id',
        'user_id'
    ];
}