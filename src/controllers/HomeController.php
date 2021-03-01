<?php
class HomeController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function index() {
        $this->twig->view('home');
    }
}