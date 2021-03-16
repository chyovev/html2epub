<?php
class HomeController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function index() {
        // temporarily redirect to books
        // until users functionality gets implemented
        Router::redirect(['controller' => 'books', 'action' => 'index'], 302);

        $this->displayFullPage('home');
    }
}