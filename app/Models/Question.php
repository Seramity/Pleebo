<?php

namespace App\Models;

use App\Helpers\Image;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Auth\Auth;

/**
 * Class Question
 *
 * Question model that extends off of the Eloquent package.
 * Deals with SQL tasks and other various functions for questions.
 *
 * @package App\Models
 */
class Question extends Model
{
    /**
     * Usable Lists database columns.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'text',
        'anonymous',
        'answered',
        'answered_at'
    ];

    /**
     * Variables used for validation.
     *
     * @var int $MAX_TEXT_CHAR
     */
    public $MAX_TEXT_CHAR = 280;

    /**
     * Deletes an answered question and all its favorites
     */
    public function deleteQuestion()
    {
        if($this->answer) {
            // delete image upload if it exists
            $image = new Image();
            $image->deleteAnswerImage($this->answer->uploaded_image);

            $this->answer()->delete();

            // TODO: add favorite delete function
        }

        $this->delete();
    }


    /**
     * Gets all unanswered questions that a user has received and returns them.
     * Also paginates based on the provided number.
     *
     * @param int $paginate
     *
     * @return Question
     */
    public function getReceivedQuestions($paginate)
    {
        return Question::removeBlocked($this->where('receiver_id', Auth::user()->id)
            ->where('answered', NULL)
            ->orderBy('created_at', 'desc')
            ->paginate($paginate));
    }

    /**
     * Gets all questions sent by a user and returns them.
     * Also paginates based on the provided number.
     *
     * @param int $paginate
     *
     * @return Question
     */
    public function getSentQuestions($paginate)
    {
        return $this->where('sender_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($paginate);
    }

    /**
     * Gets all answered questions a user has and returns them.
     * Also paginates based on the provided number.
     *
     * @param $user_id
     * @param $paginate
     *
     * @return Question
     */
    public function getUserQuestions($user_id, $paginate)
    {
        return $this->where('receiver_id', $user_id)
            ->where('answered', true)
            ->orderBy('answered_at', 'desc')
            ->simplePaginate($paginate);
    }

    /**
     * Gets all questions that a user has favorited and returns them.
     * Also paginates based on the provided number.
     *
     * @param $user_id
     * @param $paginate
     *
     * @return Question
     */
    public function getUserFavorites($user_id, $paginate) {
        $favorites = QuestionFavorite::where('user_id', $user_id)->get();
        $favoriteIds = array();
        foreach($favorites as $favorite) {
            $favoriteIds[] = $favorite->question_id;
        }

        return $this->whereIn('id', $favoriteIds)
            ->orderBy('answered_at', 'desc')
            ->simplePaginate($paginate);
    }

    /**
     * @param string $time
     * @param int $paginate
     *
     * Finds popular questions based on a provided time (week, month, etc)
     * and returns them.
     *
     * @return Question
     */
    public function popularQuestions($time, $paginate)
    {
        switch($time) {
            case 'week':
                $timeDiff =  Carbon::now()->subWeek();
                break;
            case 'month':
                $timeDiff =  Carbon::now()->subMonth();
                break;
            case 'year':
                $timeDiff =  Carbon::now()->subYear();
                break;
            case 'all-time':
                $timeDiff =  Carbon::now()->subYear(40); // A little trick to get all questions
                break;
            default:
                $timeDiff =  Carbon::now()->subWeek();
                break;
        }

        $questions = $this->leftJoin('question_favorites','questions.id','=','question_favorites.question_id')
            ->selectRaw('questions.*, count(question_favorites.question_id) AS `count`')
            ->where('questions.created_at', '>=', $timeDiff)
            ->where('questions.created_at', '<=', Carbon::now())
            ->groupBy('questions.id')
            ->orderBy('count', 'desc')
            ->simplePaginate($paginate);

        // REMOVE ALL QUESTIONS THAT HAVE ZERO FAVORITES
        foreach ($questions as $key => $question) {
            if ($question->favorites()->count() == 0) {
                $questions->forget($key);
            }
        }

        return $questions;
    }

    /**
     * Removes any questions from users that are blocked by the auth user, or questions from users that have blocked the auth user.
     *
     * @param Question $questions
     * @return Question
     */
    public static function removeBlocked($questions)
    {
        $auth = Auth::user();
        if (!$auth)
            return $questions;

        // Users that the auth user has blocked
        $userBlocks = UserBlock::where('user_id', $auth->id)->get();
        // Users that the auth user has been blocked by
        $blockedBy = UserBlock::where('blocked_id', $auth->id)->get();

        $blockedIds = array();
        foreach ($userBlocks as $userBlock) {
            $blockedIds[] = $userBlock->blocked_id;
        }
        $blockedByIds = array();
        foreach ($blockedBy as $userBlock) {
            $blockedByIds[] = $userBlock->user_id;
        }

        $blockedUsers = User::whereIn('id', $blockedIds)->get();
        $blockedByUsers = User::whereIn('id', $blockedByIds)->get();

        // REMOVE ALL QUESTIONS FROM BLOCKED USERS
        foreach ($questions as $key => $question) {
            foreach ($blockedUsers as $blockedUser) {
                if ($question->sender()->id == $blockedUser->id || $question->receiver()->id == $blockedUser->id) {
                    $questions->forget($key);
                }
            }

            foreach ($blockedByUsers as $blockedByUser) {
                if ($question->sender()->id == $blockedByUser->id || $question->receiver()->id == $blockedByUser->id && $blockedByUser->id != $auth->id) {
                    $questions->forget($key);
                }
            }
        }

        return $questions;
    }

    /**
     * Checks whether a user has favorited a question and returns a boolean.
     *
     * @return boolean
     */
    public function favorited() {
        $favorite = QuestionFavorite::where("question_id", $this->id)->where("user_id", Auth::user()->id)->first();

        return ($favorite) ? true : false;
    }

    /**
     * Finds the question owner and then allows access through the Question model.
     *
     * @return User
     */
    public function sender()
    {
        return User::where('id', $this->sender_id)->first();
    }

    /**
     * Finds the question receiver and then allows access through the Question model.
     *
     * @return User
     */
    public function receiver()
    {
        return User::where('id', $this->receiver_id)->first();
    }

    /**
     * Takes a timestamp and converts it to a user friendly timestamp.
     * Ex: "2 hours ago"
     *
     * @param string $field
     *
     * @return string
     */
    public function readableTime($field)
    {
        return Carbon::createFromTimeStamp(strtotime($this->{$field}))->diffForHumans();
    }

    /**
     * Takes a timestamp and converts it to a organized timestamp.
     * Ex: "25 May 2017 08:00 PM UTC"
     *
     * @param string $field
     *
     * @return string
     */
    public function timestamp($field)
    {
        return Carbon::createFromTimeStamp(strtotime($this->{$field}))->format('j F Y h:i A T');
    }


    /**
     * Creates relation with Answer and returns the model.
     * (Basically, it returns the only answer that is associated with this question.)
     *
     * @return Answer
     */
    public function answer() {
        return $this->hasOne('App\Models\Answer', 'question_id');
    }

    /**
     * Creates relation with QuestionFavorite and returns the model.
     */
    public function favorites()
    {
        return $this->hasMany('App\Models\QuestionFavorite', 'question_id');
    }
}