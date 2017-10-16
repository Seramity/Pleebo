<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Answer
 *
 * Answer model that extends off of the Eloquent package.
 * Deals with SQL tasks and other various functions for answers.
 *
 * @package App\Models
 */
class Answer extends Model
{
    /**
     * Usable Lists database columns.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'question_id',
        'text',
        'uploaded_image'
    ];

    /**
     * Variables used for validation.
     *
     * @var int $MAX_TEXT_CHAR
     */
    public $MAX_TEXT_CHAR = 280;

    /**
     * Finds the answer owner and then allows access through the Answer model.
     *
     * @return User
     */
    public function user() {
        return User::where('id', $this->user_id)->first();
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
}