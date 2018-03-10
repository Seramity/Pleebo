<?php

$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};

// AUTH CONTROLLERS
$container['SignInController'] = function ($container) {
    return new \App\Controllers\Auth\SignInController($container);
};
$container['SignUpController'] = function ($container) {
    return new \App\Controllers\Auth\SignUpController($container);
};
$container['SignOutController'] = function ($container) {
    return new \App\Controllers\Auth\SignOutController($container);
};
$container['ActivateController'] = function ($container) {
    return new \App\Controllers\Auth\ActivateController($container);
};
$container['ChangePasswordController'] = function ($container) {
    return new \App\Controllers\Auth\Account\ChangePasswordController($container);
};
$container['RecoverController'] = function ($container) {
    return new \App\Controllers\Auth\RecoverController($container);
};
$container['ProfileSettingsController'] = function ($container) {
    return new \App\Controllers\Auth\Account\ProfileSettingsController($container);
};
$container['PictureSettingsController'] = function ($container) {
    return new \App\Controllers\Auth\Account\PictureSettingsController($container);
};
$container['BlockedUsersController'] = function ($container) {
  return new \App\Controllers\Auth\Account\BlockedUsersController($container);
};
$container['DeleteAccountController'] = function ($container) {
    return new \App\Controllers\Auth\Account\DeleteAccountController($container);
};


// USER CONTROLLERS
$container['ProfileController'] = function ($container) {
    return new \App\Controllers\User\ProfileController($container);
};
$container['BlockUserController'] = function ($container) {
    return new \App\Controllers\User\BlockUserController($container);
};


// QUESTION CONTROLLERS
$container['NewQuestionController'] = function ($container) {
  return new \App\Controllers\Question\NewQuestionController($container);
};
$container['DeleteQuestionController'] = function ($container) {
    return new \App\Controllers\Question\DeleteQuestionController($container);
};
$container['FavoriteController'] = function ($container) {
  return new \App\Controllers\Question\FavoriteController($container);
};

// INBOX CONTROLLERS
$container['InboxController'] = function ($container) {
    return new \App\Controllers\Inbox\InboxController($container);
};


// EXPLORE CONTROLLERS
$container['ExploreController'] = function($container) {
    return new \App\Controllers\Explore\ExploreController($container);
};

// GENERAL PAGES CONTROLLERS
$container['AboutController'] = function ($container) {
    return new \App\Controllers\Pages\About\AboutController($container);
};
$container['TermsController'] = function ($container) {
    return new \App\Controllers\Pages\About\TermsController($container);
};
$container['PrivacyController'] = function ($container) {
    return new \App\Controllers\Pages\About\PrivacyController($container);
};
$container['GuidelinesController'] = function ($container) {
    return new \App\Controllers\Pages\About\GuidelinesController($container);
};