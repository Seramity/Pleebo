<?php

namespace App\Controllers\Explore;

use App\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;

class ExploreController extends Controller
{
    public function index($request, $response)
    {
        $new_questions = Question::where('created_at', '>=', Carbon::now()->subDay())
            ->where('created_at', '<=', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // filter blocked
        $new_questions = Question::removeBlocked($new_questions);

        $new_users = User::where('created_at', '>=', Carbon::now()->subDay())
            ->where('created_at', '<=', Carbon::now())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $data = ['new_questions' => $new_questions, 'new_users' => $new_users];

        return $this->view->render($response, 'explore/explore.twig', $data);
    }

    public function random($request, $response)
    {
        $random_questions = Question::inRandomOrder()
            ->take(5)
            ->get();

        // filter blocked
        $random_questions = Question::removeBlocked($random_questions);

        $random_users = User::inRandomOrder()
            ->take(5)
            ->get();

        $data = ['random_questions' => $random_questions, 'random_users' => $random_users];

        return $this->view->render($response, 'explore/random.twig', $data);
    }

    public function popular($request, $response, $args)
    {

        if(!isset($args['time'])) {
            $time = 'week';
        } else {
            $time = $args['time'];
        }

        $questions = new Question;
        $questions = $questions->popularQuestions($time, 12);

        // filter blocked
        $questions = Question::removeBlocked($questions);

        $data = ['questions' => $questions, 'time' => $time];

        return $this->view->render($response, 'explore/popular.twig', $data);
    }
}