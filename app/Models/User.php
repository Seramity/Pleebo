<?php

namespace App\Models;

use App\Models\UserBlock;
use App\Helpers\Image;
use Illuminate\Database\Eloquent\Model;
use App\Auth\Auth;
use Carbon\Carbon;

/**
 * Class User
 *
 * User model that extends off of the Eloquent package.
 * Deals with SQL tasks and other various functions for Users.
 *
 * @package App\Models
 */
class User extends Model
{
    /**
     * Table name for Eloquent to search the database.
     *
     * @var string $table
     */
    protected $table = 'users';

    /**
     * Usable User database columns.
     *
     * @var array $fillable
     */
    protected $fillable = [
        'auth_id',
        'username',
        'email',
        'password',
        'active',
        'active_hash',
        'recover_hash',
        'remember_identifier',
        'remember_token',
        'name',
        'bio',
        'gravatar',
        'uploaded_avatar',
        'bg_color',
        'box_color',
        'text_color',
        'placeholder'
    ];

    /**
     * Variables used for validation.
     *
     * @var int $MAX_USERNAME_CHAR
     * @var int $MAX_EMAIL_CHAR
     * @var int $MAX_NAME_CHAR
     * @var int $MAX_BIO_CHAR
     * @var int $MIN_PASSWORD_CHAR
     * @var int $MAX_PLACEHOLDER_CHAR
     */
    public $MAX_USERNAME_CHAR = 15;
    public $MAX_EMAIL_CHAR = 128;
    public $MAX_NAME_CHAR = 64;
    public $MAX_BIO_CHAR = 1000;
    public $MIN_PASSWORD_CHAR = 6;
    public $MAX_PLACEHOLDER_CHAR = 64;

    /**
     * Password strength for password_hash().
     *
     * @var array $PASS_STRENGTH
     */
    public $PASS_STRENGTH = ['cost' => 12];

    /**
     * Sets/changes a user's password.
     *
     * @param string $password
     * @param bool   $reset
     */
    public function setPassword($password, $reset)
    {
        $query = ['password' => password_hash($password, PASSWORD_BCRYPT, $this->PASS_STRENGTH)];
        if ($reset) $query += array('recover_hash' => NULL); // For password resets

        $this->update($query);
    }


    public function activateAccount()
    {
        $this->update([
            'active' => true,
            'active_hash' => NULL
        ]);
    }

    /**
     * Set remember me identifier and hash for user sign in.
     * Keeps user signed in if asked.
     *
     * @param $identifier
     * @param $hash
     */
    public function updateRemember($identifier, $hash)
    {
        $this->update([
            'remember_identifier' => $identifier,
            'remember_token' => $hash
        ]);
    }

    /**
     * Removes user remember login.
     */
    public function removeRemember()
    {
        $this->update([
            'remember_identifier' => NULL,
            'remember_token' => NULL
        ]);
    }

    /**
     * Creates a random 32 character string for user auth.
     *
     * @return string
     */
    public function generateAuthId()
    {
        $factory = new \RandomLib\Factory;
        $securitylib = new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM);

        $generator = $factory->getGenerator($securitylib);
        $string = $generator->generateString(32, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

        return $string;
    }


    /**
     * Returns HTML string for user avatar.
     *
     * @param array $options
     *
     * @return string
     */
    public function getAvatar($options = [])
    {
        $container = new \Slim\Container;
        $size = isset($options['size']) ? $options['size'] : 45;
        $profile = isset($options['profile']) ? $options['profile'] : false;
        $adminClass = "";

        if($this->gravatar) {
            $email = md5(strtolower($this->email));
            $src = 'https://www.gravatar.com/avatar/' . $email . '?s=' . $size . '&amp;d=identicon';
        } elseif(!$this->gravatar && !$this->uploaded_avatar) {
            $src = $container->request->getUri()->getBaseUrl() . "/assets/default_avatar.png";
        } else {
            $src = $container->request->getUri()->getBaseUrl() . "/assets/uploads/avatars/" . $this->uploaded_avatar;
        }
        if($this->isAdmin()) $adminClass = 'admin_avatar';

        if($profile) {
            return '<img class="card-img-top img-fluid" src="'.$src.'" alt="'.$this->username.'">';
        } else {
            return '<img id="user-avatar" class="'.$adminClass.'" src="'.$src.'" alt="'.$this->username.'" style="width:'.$size.'px;">';
        }

    }

    /**
     * Returns the number of unanswered questions a user has.
     *
     * @return int
     */
    public function countReceivedQuestions()
    {
        return Question::removeBlocked(Question::where('receiver_id', $this->id)->where('answered', NULL)->get())->count();
    }

    /**
     * Returns the number of answered questions a user has.
     *
     * @return int
     */
    public function countAnsweredQuestions()
    {
        $questions = Question::where('receiver_id', $this->id)->where('answered', true)->get();
        return Question::removeBlocked($questions)->count();
    }

    /**
     * Returns the number of question favorites a user has.
     *
     * @return int
     */
    public function countQuestionFavorites()
    {
        $favorites = new QuestionFavorite();
        return $favorites->countFavorites($this);
    }


    /**
     * Deletes a user's account.
     * This goes through all of the content associated with the account and deletes them.
     *
     * @return bool
     */
    public function deleteAccount()
    {
        $auth = new Auth();

        if(!$auth->user()) {
            return false;
        }

        $lists = new Lists();
        $lists->deleteUserLists($auth->user()->id);

        $comments = new Comment();
        $comments->deleteUserComments($auth->user()->id);

        $favorites = ListFavorite::where('user_id', $auth->user()->id)->get();
        foreach ($favorites as $favorite) {
            $favorite->delete();
        }

        $user_permissions = UserPermission::where('user_id', $auth->user()->id)->firstOrFail();
        $user_permissions->delete();


        if($auth->user()->uploaded_avatar) {
            $avatar = new Image();
            $avatar->deleteAvatar($auth->user()->uploaded_avatar);
        }

        $auth->user()->delete();


        return true;
    }

    /*
     * Returns the date when a user joined in a readable difference (Ex: Joined 2 weeks ago).
     */
    public function joinedDate()
    {
        return Carbon::createFromTimeStamp(strtotime($this->created_at))->diffForHumans();
    }

    /**
     * Checks if a user block exist and return a boolean
     *
     * @param int $blocked_id
     *
     * @return bool
     */
    public function hasBlocked($blocked_id)
    {
        $block = UserBlock::where('user_id', $this->id)->where('blocked_id', $blocked_id)->first();

        return ($block) ? true : false;
    }

    /**
     * Checks if user has certain permission.
     * Uses the UserPermission model.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        return (bool) $this->permissions->{$permission};
    }
    public function isAdmin()
    {
        return $this->hasPermission('is_admin');
    }
    public function isSubscriber()
    {
        return $this->hasPermission('is_subscriber');
    }


    public function permissions()
    {
        return $this->hasOne('App\Models\UserPermission', 'user_id');
    }
}