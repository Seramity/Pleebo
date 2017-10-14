<?php

namespace App\Controllers\User;

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

        $questions = Question::where('receiver_id', $user->id)->where('answered', true)->orderBy('answered_at', 'desc')->get();

        $data = ['user' => $user, 'questions' => $questions, 'isUserProfile' => TRUE];
        return $this->view->render($response, 'user/profile.twig', $data);
    }

    public function favorites($request, $response, $args)
    {

    }
}