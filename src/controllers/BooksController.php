<?php

class BooksController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function index() {
        $books = BookQuery::create()->orderById()->find();
        $this->displayFullPage('books/index', ['books' => $books]);
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

        $this->displayFullPage('books/add', $viewVars);
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
            'toc'        => $book->getChaptersAsNestedSet(),
            'wideHeader' => true,
            'breadcrumbs' => $breadcrumbs,
        ];

        $this->displayFullPage('books/add', $viewVars);
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
        if (Request::isAjax()) {
            $response = [
                'status' => $status,
                'url'    => $booksIndexUrl,
            ];
            $this->renderJSONContent($response);
        }

        // regular requests should be redirected to books index page
        else {
            header("Location: " . $booksIndexUrl);
            exit;
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function deleteImage() {
        if ( ! Request::isAjax()) {
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
        
        $this->renderJSONContent($response);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _setLanguages() {
        $languages = LanguageQuery::create()->orderByLanguage();
        $this->_setViewVars(['languages' => $languages]);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxGetRequest(Book $book, array $breadcrumbs): void {
        if ( ! (Request::isAjax() && Request::isGet())) {
            return;
        }

        $this->_setLanguages();

        $viewVars = [
            'metaTitle'   => $book->getTitle() . META_SUFFIX,
            'html'        => $this->renderTemplate('books/book-details.twig',  ['book'        => $book]),
            'breadcrumbs' => $this->renderTemplate('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]),
        ];

        $this->renderJSONContent($viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxPostRequest(Book $book, bool $isNew = false): void {
        if ( ! (Request::isAjax() && Request::isPost())) {
            return;
        }

        $oldSlug = $book->getSlug();
        $oldImg  = $book->getCoverImageSrc();

        $book->fromArray(Request::getVars('POST'));

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
            $response['image'] = $this->renderTemplate('elements/book-cover-image.twig', ['book' => $book]);
        }

        // when editing, the title may change which should be applied to the breadcrumb
        if ( ! $isNew) {
            $breadcrumbs             = [['Books', Router::url(['controller' => 'books', 'action' => 'index'])], [$book->getTitle(), NULL], 'Edit'];
            $response['breadcrumbs'] = $this->renderTemplate('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]);
        }

        $this->renderJSONContent($response);
    }

}