<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index($request, $response)
    {
        if(!$this->auth->user()) {
            return $this->view->render($response, 'welcome.twig');
        } else {
            return $this->view->render($response, 'home.twig');
        }
    }
}