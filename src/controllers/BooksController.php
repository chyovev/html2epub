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
            'breadcrumbs' => [['Books', Url::generateBooksIndexUrl()], ['Add new book', NULL], $book->getTitle()],
        ];

        $this->_setView('books/add', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function edit() {
        $slug     = getGetRequestVar('slug');
        $book     = BookQuery::create()->findOneBySlug($slug);
        $toc      = BookQuery::getChaptersAsNestedSet($book);

        $this->_throw404OnEmpty($book);

        $this->_setLanguages();

        $this->_saveBook($book);

        $viewVars = [
            'book'       => $book->toArray(TableMap::TYPE_FIELDNAME),
            'metaTitle'  => $book->getTitle(),
            'title'      => $book->getTitle(),
            'toc'        => $toc,
            'wideHeader' => true,
            'breadcrumbs' => [['Books', Url::generateBooksIndexUrl()], [$book->getTitle(), NULL], 'Edit'],
        ];

        $this->_setView('books/add', $viewVars);

    }

    ///////////////////////////////////////////////////////////////////////////
    public function chapter() {
        $bookSlug    = getGetRequestVar('book_slug');
        $chapterSlug = getGetRequestVar('slug');
        $book        = BookQuery::create()->findOneBySlug($bookSlug);
        $toc         = BookQuery::getChaptersAsNestedSet($book);

        $chapter     = ChapterQuery::create()->findOneBySlug($chapterSlug);

        $this->_throw404OnEmpty($book && $chapter);

        $this->_saveChapter($book, $chapter);

        $viewVars = [
            'book'        => $book->toArray(TableMap::TYPE_FIELDNAME),
            'metaTitle'   => $chapter->getTitle() . ' | ' . $book->getTitle(),
            'title'       => $book->getTitle(),
            'toc'         => $toc,
            'chapter'     => $chapter->toArray(TableMap::TYPE_FIELDNAME),
            'wideHeader'  => true,
            'breadcrumbs' => [['Books', Url::generateBooksIndexUrl()], [$book->getTitle(), Url::generateBookUrl($book->getSlug())], [$chapter->getTitle(), NULL], 'Edit']
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

            $this->_saveToc($book);

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
    protected function _saveChapter(Book $book, Chapter &$chapter): void {
        if (isRequest('POST')) {
            $this->_saveToc($book, true);

            $chapter->fromArray($_POST, TableMap::TYPE_FIELDNAME);

            if ( ! $chapter->saveWithValidation()) {
                $this->twig->addGlobalValidationFailures($chapter->getValidationFailures());
            }
            else {
                header("Location: " . Url::generateChapterUrl($book->getSlug(), $chapter->getSlug()));
                exit;
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _saveToc(Book &$book, bool $directSave = false): void {
        $chapterData = $_POST['chapters'] ?? NULL;
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

        // TOC rearrangements are saved when a book gets saved
        // which doesn't happen on chapter edit unless $directSave is set to true
        if ($directSave) {
            $book->save();
        }
    }


    ///////////////////////////////////////////////////////////////////////////
    protected function _setLanguages() {
        $languages = LanguageQuery::create()->orderByLanguage();
        $this->_setViewVars(['languages' => $languages]);
    }

}