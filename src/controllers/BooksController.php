<?php

class BooksController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function index() {
        $books = BookQuery::create()->orderByIdAsArray();
        $this->_setView('books/index', ['books' => $books]);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function add() {
        $this->_setLanguages();

        $book = new Book();

        // try to save the book if there was an ajax request
        $this->_processAjaxPostRequest($book);

        $viewVars = [
            'book'       => $book->toArray(),
            'metaTitle'  => 'Add new book',
            'wideHeader' => true,
            'breadcrumbs' => [['Books', Url::generateBooksIndexUrl()], ['Add new book', NULL], $book->getTitle()],
        ];

        $this->_setView('books/add', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function edit() {
        $slug     = getGetRequestVar('slug');
        $book     = BookQuery::create()->findOneBySlug($slug);

        $this->_throw404OnEmpty($book);

        $breadcrumbs = [['Books', Url::generateBooksIndexUrl()], [$book->getTitle(), NULL], 'Edit'];

        // AJAX GET request  → load book as JSON
        // AJAX POST request → try to save book
        $this->_processAjaxGetRequest($book, $breadcrumbs);
        $this->_processAjaxPostRequest($book);

        $this->_setLanguages();

        $viewVars = [
            'book'       => $book->toArray(),
            'metaTitle'  => $book->getTitle(),
            'title'      => $book->getTitle(),
            'toc'        => BookQuery::getChaptersAsNestedSet($book),
            'wideHeader' => true,
            'breadcrumbs' => $breadcrumbs,
        ];

        $this->_setView('books/add', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function delete() {
        $slug = getGetRequestVar('slug');
        $book = BookQuery::create()->findOneBySlug($slug);

        $this->_throw404OnEmpty($book);

        try {
            $book->delete();
            FlashMessage::setFlashMessage(true, 'Item successfully deleted!');
        }
        catch (Exception $e) {
            FlashMessage::setFlashMessage(false, 'Item could not be deleted!');
            // TODO: log error
        }

        header("Location: " . Url::generateBooksIndexUrl());
        exit;
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _setLanguages() {
        $languages = LanguageQuery::create()->orderByLanguage();
        $this->_setViewVars(['languages' => $languages]);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxGetRequest(Book $book, array $breadcrumbs): void {
        if ( ! (isRequestAjax() && isRequest('GET'))) {
            return;
        }

        $this->_setLanguages();

        $viewVars = [
            'metaTitle'   => $book->getTitle(),
            'html'        => $this->twig->render('books/book-details.twig',  ['book'        => $book->toArray()]),
            'breadcrumbs' => $this->twig->render('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]),
        ];

        $this->twig->renderJSONContent($viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxPostRequest(Book $book): void {
        if ( ! (isRequestAjax() && isRequest('POST'))) {
            return;
        }

        // if book object is newly created, there will be no ID
        // therefore it will be considered as new
        $isNew = ( ! $book->getId());

        $book->fromArray(getRequestVariables('POST'));

        $status = (bool) $book->saveWithValidation();
        $errors = $this->reorganizeValidationErrors($book->getValidationFailures());
        
        $response = [
            'status'      => $status,
            'errors'      => $errors,
            'flash'       => $this->twig->render('elements/flash.message.twig', ['flash' => FlashMessage::getFlashMessage(), 'hidden' => true]),
            'url'         => Url::generateBookUrl($book->getSlug()),
            'redirect'    => $isNew,
        ];

        // when editing, the title may change which should be applied to the breadcrumb
        if ( ! $isNew) {
            $breadcrumbs             = [['Books', Url::generateBooksIndexUrl()], [$book->getTitle(), NULL], 'Edit'];
            $response['breadcrumbs'] = $this->twig->render('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]);
        }

        $this->twig->renderJSONContent($response);
    }

}