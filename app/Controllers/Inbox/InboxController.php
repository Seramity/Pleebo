<?php

namespace App\Controllers\Inbox;

use App\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use Respect\Validation\Validator as v;
use Carbon\Carbon;

class InboxController extends Controller
{
    public function index($request, $response)
    {
        $questions = new Question();
        $questions = $questions->getReceivedQuestions(10);

        return $this->view->render($response, 'inbox/inbox.twig', ['questions' => $questions]);
    }

    public function getSentQuestions($request, $response)
    {
        $questions = new Question();
        $questions = $questions->getSentQuestions(10);

        return $this->view->render($response, 'inbox/sent.twig', ['questions' => $questions]);
    }

    public function postReplyQuestion($request, $response)
    {
        $answer = new Answer();
        $question = Question::where('id', $request->getParam('qid'))->first();
        if(!$question) {
            $this->flash->addMessage('global_error', 'That question does not exist');
            return $response->withRedirect($this->router->pathFor('inbox'));
        }

        if($question->receiver_id !== $this->auth->user()->id) {
            $this->flash->addMessage('global_error', 'You do not own that question');
            return $response->withRedirect($this->router->pathFor('inbox'));
        }

        $validation = $this->validator->validate($request, [
            'reply' => v::length(NULL, $answer->MAX_TEXT_CHAR)
        ]);

        if($validation->failed()) {
            $this->flash->addMessage('global_error', 'Your reply must be under '. $answer->MAX_TEXT_CHAR .' characters');
            return $response->withRedirect($this->router->pathFor('inbox'));
        }

        $answer = $answer->create([
            'user_id' => $this->auth->user()->id,
            'question_id' => $request->getParam('qid'),
            'text' => $request->getParam('reply')
        ]);

        $question->update([
            'answered' => true,
            'answered_at' => Carbon::now()
        ]);

        $this->flash->addMessage('global_success', 'Question successfully answered');
        return $response->withRedirect($this->router->pathFor('inbox'));
    }

    public function getDeleteQuestion($request, $response, $args)
    {
        $question = Question::where('id', $args['id'])->first();

        if(!$question) {
            $this->flash->addMessage('global_error', 'That question does not exist');
            return $response->withRedirect($this->router->pathFor('inbox'));
        }

        if($question->receiver_id !== $this->auth->user()->id) {
            $this->flash->addMessage('global_error', 'You do not own that question');
            return $response->withRedirect($this->router->pathFor('inbox'));
        }

        // Deny sender to delete the question after it has been sent if the question is not sent to themselves
        if($this->auth->user()->id == $question->sender_id && $this->auth->user()->id !== $question->receiver_id) {
            $this->flash->addMessage('global_error', 'You cannot delete that question');
            return $response->withRedirect($this->router->pathFor('inbox'));
        }

        $question->delete();

        $this->flash->addMessage('global_success', 'Question successfully deleted');
        return $response->withRedirect($this->router->pathFor('inbox'));

    }
}