<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Userblock
 *
 * UserBlock model that extends off of the Eloquent package.
 * Related to User model to determine if a user has blocked another user.
 *
 * @package App\Models
 */
class UserBlock extends Model
{
    /**
     * Usable UserBlock database columns.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'user_id',
        'blocked_id'
    ];

}