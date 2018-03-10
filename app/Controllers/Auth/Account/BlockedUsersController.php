<?php

namespace App\Controllers\Auth\Account;

use App\Controllers\Controller;
use App\Models\UserBlock;
use App\Models\User;

class BlockedUsersController extends Controller
{
    public function getBlockedUsers($request, $response)
    {
        $userBlocks = UserBlock::where('user_id', $this->auth->user()->id)->get();
        $blockedIds = array();
        foreach ($userBlocks as $userBlock) {
            $blockedIds[] = $userBlock->blocked_id;
        }

        $blockedUsers = User::whereIn('id', $blockedIds)->paginate(10);

        return $this->view->render($response, 'account/blocked.twig', ['blockedUsers' => $blockedUsers]);
    }
}