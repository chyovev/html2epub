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
        $this->_processAjaxPostRequest($book, true);

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
            $status = true;
            FlashMessage::setFlashMessage(true, 'Item successfully deleted!');
        }
        catch (Exception $e) {
            $this->addCriticalError('Book not deleted: ' . $e->getMessage());
            FlashMessage::setFlashMessage(false, 'Item could not be deleted!');
            $status = false;
        }

        $booksIndexUrl = Url::generateBooksIndexUrl();

        // ajax requests to delete a book should receive a JSON response
        if (isRequestAjax()) {
            $response = [
                'status' => $status,
                'url'    => $booksIndexUrl,
            ];
            $this->twig->renderJSONContent($response);
        }

        // regular requests should be redirected to books index page
        else {
            header("Location: " . $booksIndexUrl);
            exit;
        }
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
            'metaTitle'   => $book->getTitle() . META_SUFFIX,
            'html'        => $this->twig->render('books/book-details.twig',  ['book'        => $book->toArray()]),
            'breadcrumbs' => $this->twig->render('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]),
        ];

        $this->twig->renderJSONContent($viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxPostRequest(Book $book, bool $isNew = false): void {
        if ( ! (isRequestAjax() && isRequest('POST'))) {
            return;
        }

        $oldSlug = $book->getSlug();

        $book->fromArray(getRequestVariables('POST'));

        $status = (bool) $this->saveWithValidation($book);
        $errors = $this->reorganizeValidationErrors($book->getValidationFailures());
        
        $response = [
            'status'      => $status,
            'errors'      => $errors,
            'flash'       => $this->twig->render('elements/flash.message.twig', ['flash' => FlashMessage::getFlashMessage(), 'hidden' => true]),
            'old_url'     => $oldSlug && $oldSlug != $book->getSlug() ? Url::generateBookUrl($oldSlug) : false,
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