<?php

namespace App\Models;

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
        // TODO: delete question and add functionality to delete all favorites associated with it
    }


    /**
     * Gets all unanswered questions that a user has received and returns them.
     * Also paginates based on the provided number.
     *
     * @return Question
     */
    public function getReceivedQuestions($paginate)
    {
        return $this->where('receiver_id', Auth::user()->id)->where('answered', NULL)->orderBy('created_at', 'desc')->paginate($paginate);
    }

    /**
     * Gets all questions sent by a user and returns them.
     * Also paginates based on the provided number.
     *
     * @return Question
     */
    public function getSentQuestions($paginate)
    {
        return $this->where('sender_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate($paginate);
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
     * @return string
     */
    public function timestamp($field)
    {
        return Carbon::createFromTimeStamp(strtotime($this->{$field}))->format('j F Y h:i A T');
    }


    /**
     * Creates relation with Answer and returns the model.
     * (Basically, it returns the only answer that is associated with this question.)
     */
    public function answer() {
        return $this->hasOne('App\Models\Answer', 'question_id');
    }
}