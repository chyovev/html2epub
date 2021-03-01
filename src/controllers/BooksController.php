<?php
use Propel\Runtime\Map\TableMap;

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
        $this->_saveBook($book);

        $viewVars = [
            'book'       => $book->toArray(TableMap::TYPE_FIELDNAME),
            'metaTitle'  => 'Add new book',
            'wideHeader' => true,
        ];

        $this->_setView('books/add', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function edit() {
        $slug     = getGetRequestVar('slug');
        $book     = BookQuery::create()->findOneBySlug($slug);
        $chapters = BookQuery::getChaptersAsNestedSet($book);

        $this->_throw404OnEmpty($book);

        $this->_setLanguages();

        $this->_saveBook($book);

        $viewVars = [
            'book'       => $book->toArray(TableMap::TYPE_FIELDNAME),
            'metaTitle'  => $book->getTitle(),
            'chapters'   => $chapters,
            'wideHeader' => true,
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
    protected function _saveBook(Book &$book): void {
        if (isRequest('POST')) {
            $book->fromArray($_POST, TableMap::TYPE_FIELDNAME);

            $chapterData = $_POST['chapters'];
            $chapters    = $book->getChapters();

            // iterate through all chapters
            // and set new values for the tree properties using the POST request
            foreach ($chapters as $chapter) {
                $id = $chapter->getId();

                if (isset($chapterData[$id])) {
                    $chapter->setTreeLeft($chapterData[$id]['tree_left']);
                    $chapter->setTreeRight($chapterData[$id]['tree_right']);
                    $chapter->setTreeLevel($chapterData[$id]['tree_level']);
                }
            }

            $book->setChapters($chapters);


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
        $this->_setViewVars(['languages' => $languages]);
    }

}