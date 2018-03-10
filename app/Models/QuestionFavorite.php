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

    /**
     * Counts the number of favorites a user has. Helps with filtering out any questions
     * from blocked users.
     *
     * @param User $user
     *
     * @return int
     */
    public function countFavorites($user)
    {
        if (!$user)
            return null;

        $favorites = $this->where('user_id', $user->id)->get();
        $questionIds = array();
        foreach ($favorites as $favorite) {
            $questionIds[] = $favorite->question_id;
        }

        $questions = Question::removeBlocked(Question::whereIn('id', $questionIds)->get());

        return $questions->count();
    }
}