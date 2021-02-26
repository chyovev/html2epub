<?php
require_once('../src/autoload.php');

class BooksController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function index() {
        $books = BookQuery::create()->orderByIdAsArray();
        $this->twig->view('books/index', ['books' => $books]);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function add() {
        $this->_setLanguages();

        $book = new Book();
        $this->_saveBook($book);

        $this->twig->view('books/add', ['book' => $book->toArray('fieldName')]);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function edit() {
        $slug = getGetRequestVar('slug');
        $book = BookQuery::create()->findOneBySlug($slug);

        $this->_throw404OnEmpty($book);

        $this->_setLanguages();

        $this->_saveBook($book);

        $this->twig->view('books/add', ['book' => $book->toArray('fieldName')]);

    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _saveBook(Book &$book): void {
        if (isRequest('POST')) {
            $book->fromArray($_POST, 'fieldName');

            if ( ! $book->saveWithValidation()) {
                $this->twig->addGlobalValidationFailures($book->getValidationFailures());
            }
            else {
                header("Location: " . Url::generateBookUrl($book->getSlug()));
                exit;
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _setLanguages() {
        $languages = LanguageQuery::create()->orderByLanguage();
        $this->twig->addGlobal('languages', $languages);
    }

}