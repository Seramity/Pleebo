<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

// GENERAL PAGES
$app->get('/', 'HomeController:index')->setName('home');

// EXPLORE PAGES
$app->get('/explore', 'ExploreController:index')->setName('explore');
$app->get('/explore/random', 'ExploreController:random')->setName('explore.random');
$app->get('/explore/popular[/{time}[/{page}]]', 'ExploreController:popular')->setName('explore.popular');


// PROFILE ROUTES
$app->get('/~{user}','ProfileController:index')->setName('userProfile');
$app->get('/~{user}/favorites[/{page}]', 'ProfileController:favorites')->setName('userProfile.favorites'); // [{ARG}] = optional

// ABOUT PAGES
$app->get('/a/about', 'AboutController:index')->setName('page.about');
$app->get('/a/terms', 'TermsController:index')->setName('page.terms');
$app->get('/a/privacy', 'PrivacyController:index')->setName('page.privacy');
$app->get('/a/guidelines', 'GuidelinesController:index')->setName('page.guidelines');


// AUTH REQUIRED PAGES
$app->group('', function () use ($app) {

    $app->get('/auth/signout', 'SignOutController:getSignOut')->setName('auth.signout');

    // ACCOUNT ROUTES
    $app->get('/account/profile', 'ProfileSettingsController:getProfileSettings')->setName('account.profile');
    $app->post('/account/profile', 'ProfileSettingsController:postProfileSettings');
    $app->get('/account/picture', 'PictureSettingsController:getPictureSettings')->setName('account.picture');
    $app->post('/account/picture', 'PictureSettingsController:postPictureSettings');
    $app->post('/account/picture/delete', 'PictureSettingsController:deletePicture')->setName('account.picture.delete');
    $app->post('/account/picture/change', 'PictureSettingsController:changePicture')->setName('account.picture.change');
    $app->get('/account/password', 'ChangePasswordController:getChangePassword')->setName('account.password');
    $app->post('/account/password', 'ChangePasswordController:postChangePassword');
    $app->get('/account/blocked', 'BlockedUsersController:getBlockedUsers')->setName('account.blocked');
    $app->get('/account/delete', 'DeleteAccountController:getDeleteAccount')->setName('account.delete');
    $app->post('/account/delete', 'DeleteAccountController:postDeleteAccount');

    // USER MANAGEMENT ROUTES
    $app->get('/user/block/u/{uid}', 'BlockUserController:getBlockUser')->setName('user.block');
    $app->get('/user/block/q/{qid}', 'BlockUserController:getBlockUserByQuestion')->setName('user.block.question');
    $app->get('/user/unblock/{uid}', 'BlockUserController:getUnblockUser')->setName('user.unblock');

    // INBOX ROUTES
    $app->get('/inbox', 'InboxController:index')->setName('inbox');
    $app->get('/inbox/sent', 'InboxController:getSentQuestions')->setName('inbox.sent');
    $app->post('/inbox/reply', 'InboxController:postReplyQuestion')->setName('inbox.reply');
    $app->get('/inbox/delete/{id}', 'InboxController:getDeleteQuestion')->setName('inbox.delete');

    // COMMENT ROUTES
    $app->post('/comment/list/{id}', 'CommentController:createListComment')->setName('comment.list');
    //$app->post('/comment/profile/{id}', 'CommentController:createProfileComment')->setName('comment.profile');
    //$app->post('/comment/reply/{id}', 'CommentController:createReplyComment')->setName('comment.reply');
    $app->get('/comment/delete/{id}', 'CommentController:deleteComment')->setName('comment.delete');

    // QUESTION ROUTES
    $app->post('/question/new', 'NewQuestionController:postNewQuestion')->setName('question.new');
    //$app->get('/question/delete/{id}', 'DeleteQuestionController:getDeleteQuestion')->setName('question.delete');
    $app->get('/question/favorite/{id}', 'FavoriteController:getFavorite')->setName('question.favorite');
    $app->get('/question/unfavorite/{id}', 'FavoriteController:getDeleteFavorite')->setName('question.unfavorite');


})->add(new AuthMiddleware($container));



// GUEST REQUIRED PAGES
$app->group('', function () use ($app) {

    // AUTH ROUTES
    $app->get('/auth/signup', 'SignUpController:getSignUp')->setName('auth.signup');
    $app->post('/auth/signup', 'SignUpController:postSignUp');

    $app->get('/auth/signin', 'SignInController:getSignIn')->setName('auth.signin');
    $app->post('/auth/signin', 'SignInController:postSignIn');

    $app->get('/auth/recover', 'RecoverController:getRecover')->setName('auth.recover');
    $app->post('/auth/recover', 'RecoverController:postRecover');
    $app->get('/auth/reset/{identifier}', 'RecoverController:getReset')->setName('auth.reset');
    $app->post('/auth/reset/{identifier}', 'RecoverController:postReset');


    $app->get('/auth/activate', 'ActivateController:getActivate')->setName('auth.activate');

})->add(new GuestMiddleware($container));
