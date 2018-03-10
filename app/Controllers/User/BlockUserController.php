<?php

namespace App\Controllers\User;

use App\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use App\Models\UserBlock;

class BlockUserController extends Controller
{
    public function getBlockUser($request, $response, $args)
    {
        $user = User::where("id", $args["uid"])->first();

        if (!$this->auth) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "noauth",
                "code" => 5,
                "msg" => "Authentication is required"
            ));
        } else if (!$user) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "nouser",
                "code" => 11,
                "msg" => "That user does not exist"
            ));
        }

        if ($user && $user->id == $this->auth->user()->id) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "sameUser",
                "code" => 13,
                "msg" => "You cannot block yourself"
            ));
        }

        $userBlock = UserBlock::where('user_id', $this->auth->user()->id)->where('blocked_id', $user->id)->first();
        if ($userBlock) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "alreadyExists",
                "code" => 12,
                "msg" => "That user is already blocked"
            ));
        }

        // Create new block and return success message
        $block = new UserBlock();
        $block->create([
            'user_id' => $this->auth->user()->id,
            'blocked_id' => $user->id
        ]);

        return json_encode(array(
            "result" => "success",
            "code" => 100,
            "msg" => "User blocked"
        ));
    }

    public function getBlockUserByQuestion($request, $response, $args)
    {
        $question = Question::where("id", $args["qid"])->first();

        if (!$this->auth) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "noauth",
                "code" => 5,
                "msg" => "Authentication is required"
            ));
        } else if (!$question) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "nodata",
                "code" => 10,
                "msg" => "That question does not exist"
            ));
        }

        $user = User::where("id", $question->sender_id)->first();
        if (!$user) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "nouser",
                "code" => 11,
                "msg" => "That user does not exist"
            ));
        } else if ($user && $user->id == $this->auth->user()->id) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "sameUser",
                "code" => 13,
                "msg" => "You cannot block yourself"
            ));
        }

        $userBlock = UserBlock::where('user_id', $this->auth->user()->id)->where('blocked_id', $user->id)->first();
        if ($userBlock) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "alreadyExists",
                "code" => 12,
                "msg" => "That user is already blocked"
            ));
        }

        // Create new block and return success message
        $block = new UserBlock();
        $block->create([
            'user_id' => $this->auth->user()->id,
            'blocked_id' => $user->id
        ]);

        return json_encode(array(
            "result" => "success",
            "code" => 100,
            "msg" => "User blocked"
        ));
    }

    public function getUnblockUser($response, $request, $args)
    {
        $user = User::where("id", $args["uid"])->first();

        if (!$this->auth) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "noauth",
                "code" => 5,
                "msg" => "Authentication is required"
            ));
        } else if (!$user) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "nouser",
                "code" => 11,
                "msg" => "That user does not exist"
            ));
        }

        if ($user && $user->id == $this->auth->user()->id) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "sameUser",
                "code" => 13,
                "msg" => "You cannot unblock yourself"
            ));
        }

        $userBlock = UserBlock::where('user_id', $this->auth->user()->id)->where('blocked_id', $user->id)->first();
        if (!$userBlock) {
            return json_encode(array(
                "result" => "error",
                "errorType" => "alreadyExists",
                "code" => 12,
                "msg" => "That user is not blocked"
            ));
        }

        // Delete block
        $userBlock->delete();

        return json_encode(array(
            "result" => "success",
            "code" => 100,
            "msg" => "User unblocked"
        ));
    }
}