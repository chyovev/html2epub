<?php

class BooksController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function index() {
        $books = BookQuery::create()->orderById();
        $this->_setView('books/index', ['books' => $books]);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function add() {
        $this->_setLanguages();

        $book = new Book();

        // try to save the book if there was an ajax request
        $this->_processAjaxPostRequest($book, true);

        $viewVars = [
            'book'       => $book,
            'metaTitle'  => 'Add new book',
            'wideHeader' => true,
            'breadcrumbs' => [['Books', Router::url(['controller' => 'books', 'action' => 'index'])], ['Add new book', NULL], $book->getTitle()],
        ];

        $this->_setView('books/add', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function edit() {
        $slug     = Router::getRequestParam('book');
        $book     = BookQuery::create()->findOneBySlug($slug);

        $this->_throw404OnEmpty($book);

        $breadcrumbs = [['Books', Router::url(['controller' => 'books', 'action' => 'index'])], [$book->getTitle(), NULL], 'Edit'];

        // AJAX GET request  → load book as JSON
        // AJAX POST request → try to save book
        $this->_processAjaxGetRequest($book, $breadcrumbs);
        $this->_processAjaxPostRequest($book);

        $this->_setLanguages();

        $viewVars = [
            'book'       => $book,
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
        $slug = Router::getRequestParam('book');
        $book = BookQuery::create()->findOneBySlug($slug);

        $this->_throw404OnEmpty($book);

        try {
            $book->delete();
            $status = true;
            $this->setSuccessFlash('Item successfully deleted!');
        }
        catch (Exception $e) {
            $this->addCriticalError('Book not deleted: ' . $e->getMessage());
            $status = false;
            $this->setErrorFlash('Item could not be deleted!');
        }

        $booksIndexUrl = Router::url(['controller' => 'books', 'action' => 'index']);

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
    public function deleteImage() {
        if ( ! isRequestAjax()) {
            return;
        }

        $slug = Router::getRequestParam('book');
        $book = BookQuery::create()->findOneBySlug($slug);

        $this->_throw404OnEmpty($book);

        // if the file was deleted, delete the field value, too
        if ($book->deleteImage()) {
            $book->setCoverImage(NULL);
            $book->save();
            $status = true;
            $this->setSuccessFlash('Item successfully deleted!');
        }
        else {
            $status = false;
            $this->setErrorFlash('Item could not be deleted!');
        }

        $response = [
            'status' => $status,
            'flash'  => $this->generateFlashHtml(),
        ];
        
        $this->twig->renderJSONContent($response);
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
            'html'        => $this->twig->render('books/book-details.twig',  ['book'        => $book]),
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
        $oldImg  = $book->getCoverImageSrc();

        $book->fromArray(getRequestVariables('POST'));

        $status = (bool) $this->saveWithValidation($book);
        $errors = $this->reorganizeValidationErrors($book->getValidationFailures());

        $response = [
            'status'      => $status,
            'errors'      => $errors,
            // if the status is true on new record, the page will be redirected: don't render flash, it will autorender on page load
            'flash'       => ($isNew  && $status) ? false : $this->generateFlashHtml(),
            'old_url'     => $oldSlug && $oldSlug != $book->getSlug() ? Router::url(['controller' => 'books', 'action' => 'edit', 'book' => $oldSlug]) : false,
            'url'         => $book->getSlug() ? Router::url(['controller' => 'books', 'action' => 'edit', 'book' => $book->getSlug()]) : false,
            'redirect'    => $isNew,
        ];

        // send cover image HTML in case it was altered
        $newImg = $book->getCoverImageSrc();
        if ($oldImg !== $newImg) {
            $response['image'] = $this->twig->render('elements/book-cover-image.twig', ['book' => $book]);
        }

        // when editing, the title may change which should be applied to the breadcrumb
        if ( ! $isNew) {
            $breadcrumbs             = [['Books', Router::url(['controller' => 'books', 'action' => 'index'])], [$book->getTitle(), NULL], 'Edit'];
            $response['breadcrumbs'] = $this->twig->render('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]);
        }

        $this->twig->renderJSONContent($response);
    }

}