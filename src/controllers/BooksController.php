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

        $this->_throw404OnEmpty($book);

        $this->_setTOC($book);

        $this->_setLanguages();

        $this->_saveBook($book);

        $viewVars = [
            'book'       => $book->toArray(TableMap::TYPE_FIELDNAME),
            'metaTitle'  => $book->getTitle(),
            'title'      => $book->getTitle(),
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
        $chapter     = ChapterQuery::create()->findOneBySlug($chapterSlug);

        $this->_throw404OnEmpty($book && $chapter);

        $this->_setTOC($book);

        $this->_saveChapter($book, $chapter);

        $viewVars = [
            'book'        => $book->toArray(TableMap::TYPE_FIELDNAME),
            'metaTitle'   => $chapter->getTitle() . ' | ' . $book->getTitle(),
            'title'       => $book->getTitle(),
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
    protected function _setTOC(Book $book) {
        $toc = BookQuery::getChaptersAsNestedSet($book);
        $this->twig->addGlobal('toc', $toc);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _saveBook(Book &$book): void {
        if (isRequest('POST')) {
            $book->fromArray($_POST, TableMap::TYPE_FIELDNAME);

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
            $chapter->fromArray($_POST, TableMap::TYPE_FIELDNAME);
            if ($chapter->isModified()) {
                $chapter->setUpdatedAt(new \DateTime());
            }

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
    protected function _setLanguages() {
        $languages = LanguageQuery::create()->orderByLanguage();
        $this->_setViewVars(['languages' => $languages]);
    }

}