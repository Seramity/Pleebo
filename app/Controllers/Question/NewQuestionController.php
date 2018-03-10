<?php

namespace App\Controllers\Question;

use App\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use Respect\Validation\Validator as v;

class NewQuestionController extends Controller
{
    public function postNewQuestion($request, $response)
    {
        $question = new Question();
        $user_receiver = User::where('id', $request->getParam('uid'))->first();

        if ($user_receiver->hasBlocked($this->auth->user()->id)) {
            $this->flash->addMessage('global_error', 'You cannot send this user a question');
            return $response->withRedirect($this->router->pathFor('userProfile', ['user' => $user_receiver->username]));
        }

        $validation = $this->validator->validate($request, [
            'question' => v::notEmpty()->length(4, $question->MAX_TEXT_CHAR)
        ]);

        if($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('userProfile', ['user' => $user_receiver->username]));
        }

        if($this->auth->user()->id == $user_receiver->id && $request->getParam('anonymous') == "on") {
            $this->flash->addMessage('global_error', 'You cannot send yourself an anonymous question');
            return $response->withRedirect($this->router->pathFor('userProfile', ['user' => $user_receiver->username]));
        }

        // convert stupid checkbox value to actual booleans
        $is_anonymous = ($request->getParam('anonymous') == "on") ? true : false;

        $question = $question->create([
            'sender_id' => $this->auth->user()->id,
            'receiver_id' => $request->getParam('uid'),
            'text' => $request->getParam('question'),
            'anonymous' => $is_anonymous
        ]);


        $this->flash->addMessage('global_success', 'Question sent');
        return $response->withRedirect($this->router->pathFor('userProfile', ['user' => $user_receiver->username]));
    }
}