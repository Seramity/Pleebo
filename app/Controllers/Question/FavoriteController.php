<?php

namespace App\Controllers\Question;

use App\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionFavorite;

class FavoriteController extends Controller
{
    public function getFavorite($request, $response, $args)
    {
        $question = Question::where("id", $args["id"])->first();

        // Throw errors
        if(!$this->auth) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "noauth",
                "code" => 5,
                "msg" => "Authentication is required"
            ));
        } else if(!$question) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "nodata",
                "code" => 10,
                "msg" => "That question does not exist"
            ));
        }

        $questionFavorite = QuestionFavorite::where('user_id', $this->auth->user()->id)->where('question_id', $question->id)->first();
        if($questionFavorite) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "alreadyExists",
                "code" => 12,
                "msg" => "That question is already favorited"
            ));
        }


        // Create new favorite and return success message
        $favorite = new QuestionFavorite();
        $favorite->create([
            "question_id" => $question->id,
            "user_id" => $this->auth->user()->id
        ]);

        return json_encode(array(
            "result" => "success",
            "code" => 100,
            "msg" => "Question favorited"
        ));
    }

    public function getDeleteFavorite($request, $response, $args)
    {
        $question = Question::where("id", $args["id"])->first();

        // Throw errors
        if(!$this->auth) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "noauth",
                "code" => 5,
                "msg" => "Authentication is required"
            ));
        } else if(!$question) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "nodata",
                "code" => 10,
                "msg" => "That question does not exist"
            ));
        }

        $questionFavorite = QuestionFavorite::where('user_id', $this->auth->user()->id)->where('question_id', $question->id)->first();
        if(!$questionFavorite) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "noFavorite",
                "code" => 12,
                "msg" => "That question is not favorited"
            ));
        }

        $questionFavorite->delete();

        return json_encode(array(
            "result" => "success",
            "code" => 100,
            "msg" => "Question unfavorited"
        ));
    }
}