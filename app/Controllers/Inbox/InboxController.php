<?php

namespace App\Controllers\Inbox;

use App\Controllers\Controller;
use App\Helpers\Image;
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
            'reply' => v::length(NULL, $answer->MAX_TEXT_CHAR),
        ]);

        // separate validator for the uploaded image
        $validation = $this->validator->Validate($request, [
            'image' => v::optional(v::image())->optional(v::size(NULL, $this->container['settings']['app']['max_file_size']))
        ], true);

        if($validation->failed()) {
            $this->flash->addMessage('global_error', 'There was a problem sending your answer');
            return $response->withRedirect($this->router->pathFor('inbox'));
        }

        $answer = $answer->create([
            'user_id' => $this->auth->user()->id,
            'question_id' => $request->getParam('qid'),
            'text' => $request->getParam('reply')
        ]);

        // if an image is uploaded with the answer
        if($request->getUploadedFiles()['image']->file !== "") {
            $uploaded_img = $request->getUploadedFiles()['image'];
            $img_ext = pathinfo($request->getUploadedFiles()['image']->getClientFilename(), PATHINFO_EXTENSION);
            $file_name = $answer->id . "-" . mt_rand() . "." . $img_ext;

            $image = new Image();
            $image->directUpload($uploaded_img, $file_name);

            $answer->update([
                'uploaded_image' => $file_name
            ]);
        }

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

        // Helps redirect to profile if a question with an answer is deleted
        // So it will not annoyingly redirect to the inbox when it is not necessary
        $profile_redirect = false;
        $receiver_username = $question->receiver()->username;
        if($question->answer) $profile_redirect = true;

        $question->deleteQuestion();

        $this->flash->addMessage('global_success', 'Question successfully deleted');
        if($profile_redirect) return $response->withRedirect($this->router->pathFor('userProfile', ['user' => $receiver_username]));
        return $response->withRedirect($this->router->pathFor('inbox'));

    }
}