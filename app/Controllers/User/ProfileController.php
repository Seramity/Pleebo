<?php

namespace App\Controllers\User;

use App\Models\QuestionFavorite;
use Illuminate\Pagination\Paginator;
use App\Controllers\Controller;
use App\Models\User;
use App\Models\Question;

class ProfileController extends Controller
{
    public function index($request, $response, $args)
    {
        $user = User::where('username', $args['user'])->first();

        if(!$user) return $this->view->render($response, 'errors/404.twig')->withStatus(404);

        // Handle user blocks
        if ($this->auth->check() && $user->hasBlocked($this->auth->user()->id))
            return $this->view->render($response, 'templates/user/blocked.twig', ['username' => $user->username]);
        if ($this->auth->check() && $this->auth->user()->hasBlocked($user->id))
            return $this->view->render($response, 'templates/user/hasBlocked.twig', ['user' => $user]);

        $questions = new Question();
        $questions = $questions->getUserQuestions($user->id, 10);

        // filter blocked
        $questions = Question::removeBlocked($questions);

        $data = ['user' => $user, 'questions' => $questions, 'isUserProfile' => TRUE];
        return $this->view->render($response, 'user/profile.twig', $data);
    }

    public function favorites($request, $response, $args)
    {
        $user = User::where('username', $args['user'])->first();

        if(!$user) return $this->view->render($response, 'errors/404.twig')->withStatus(404);

        if ($this->auth->check() && $user->hasBlocked($this->auth->user()->id))
            return $this->view->render($response, 'errors/blocked.twig', ['username' => $user->username]);

        $questions = new Question();
        $questions = $questions->getUserFavorites($user->id, 10);

        // filter blocked
        $questions = Question::removeBlocked($questions);

        $data = ['user' => $user, 'questions' => $questions, 'isUserProfile' => TRUE];
        return $this->view->render($response, 'user/favorites.twig', $data);
    }
}