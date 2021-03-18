<?php
use Propel\Runtime\Map\TableMap;

class ChaptersController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function edit() {
        $bookSlug    = Router::getRequestParam('book');
        $chapterSlug = Router::getRequestParam('chapter');
        $book        = BookQuery::create()->findOneBySlug($bookSlug);
        $chapter     = ChapterQuery::create()->findOneBySlug($chapterSlug);

        $this->_throw404OnEmpty($book && $chapter);

        $ancestors   = $chapter->getAncestorsLinks($bookSlug, 'Edit');
        $breadcrumbs = [['Books', Router::url(['controller' => 'books', 'action' => 'index'])], [$book->getTitle(), Router::url(['controller' => 'books', 'action' => 'edit', 'book' => $book->getSlug()])]];
        $breadcrumbs = array_merge($breadcrumbs, $ancestors);

        // AJAX GET request  → load book as JSON
        // AJAX POST request → try to save book
        $this->_processAjaxGetRequest($book, $chapter, $breadcrumbs);
        $this->_processAjaxPostRequest($book, $chapter);

        $viewVars = [
            'book'        => $book->toArray(),
            'metaTitle'   => $chapter->getTitle() . ' | ' . $book->getTitle(),
            'title'       => $book->getTitle(),
            'chapter'     => $chapter->toArray(),
            'slug'        => $chapter->getSlugAsString(),
            'toc'         => $book->getChaptersAsNestedSet(),
            'wideHeader'  => true,
            'breadcrumbs' => $breadcrumbs,
        ];

        $this->displayFullPage('books/add', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function updateToc() {
        $bookSlug = Router::getRequestParam('book');
        $book     = BookQuery::create()->findOneBySlug($bookSlug);

        // if the request is not POST or there's no such book, abort
        $this->_throw404OnEmpty(Request::isPost() && $book);

        $chapters = $book->getChapters();
        $postData = Request::getPostVar('chapters');

        // iterate through all chapters
        // and set new values for the tree properties using the POST request
        foreach ($chapters as $chapter) {
            // for security reasons, slugs are used instead of IDs in the TOC
            $slug = $chapter->getSlugAsString();

            if (isset($postData[$slug])) {
                $chapter->setTreeLeft($postData[$slug]['tree_left']);
                $chapter->setTreeRight($postData[$slug]['tree_right']);
                $chapter->setTreeLevel($postData[$slug]['tree_level']);
            }
        }

        $book->setChapters($chapters);
        $book->setUpdatedAt(new \DateTimeImmutable());

        try {
            $status = (bool) $book->save();
        }
        catch (Exception $e) {
            $this->addError('TOC arrangement not saved: ' . $e->getMessage());
            $status = false;
        }

        $this->renderJSONContent(['status' => $status]);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function add() {
        $bookSlug = Router::getRequestParam('book');
        $book     = BookQuery::create()->findOneBySlug($bookSlug);

        // if the request is not AJAX or there's no such book, abort
        $this->_throw404OnEmpty(Request::isAjax() && $book);

        $chapter = new Chapter();
        $chapter->setTitle('New chapter')
                ->setTreeLeft(1);

        $book->addChapter($chapter);
        try {
            $response = [
                'status'     => (bool) $book->save(),
                'id'         => $chapter->getSlugAsString(),
                'title'      => $chapter->getTitle(),
                'edit_url'   => Router::url(['controller' => 'chapters', 'action' => 'edit', 'book' => $book->getSlug(), 'chapter' => $chapter->getSlugAsString()]),
                'delete_url' => Router::url(['controller' => 'chapters', 'action' => 'delete', 'book' => $book->getSlug(), 'chapter' => $chapter->getSlugAsString()]),
            ];
        }
        catch (Exception $e) {
            $this->addCriticalError('Chapter not added: ' . $e->getMessage());
            $response = [
                'status' => false,
                'errors' => 'An error occurred. Please try again later.',
            ];
        }

        $this->renderJSONContent($response);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function delete() {
        if ( ! (Request::isAjax() && Request::isGet())) {
            return;
        }

        $bookSlug    = Router::getRequestParam('book');
        $chapterSlug = Router::getRequestParam('chapter');
        $book        = BookQuery::create()->findOneBySlug($bookSlug);
        $chapter     = ChapterQuery::create()->findOneBySlug($chapterSlug);

        $this->_throw404OnEmpty($book && $chapter);

        try {
            $chapter->delete();
            $status = true;
        }
        catch (Exception $e) {
            $this->addCriticalError('Chapter not deleted: ' . $e->getMessage());
            $status = false;
        }

        $this->renderJSONContent(['status' => $status]);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxGetRequest(Book $book, Chapter $chapter, array $breadcrumbs = []): void {
        if ( ! (Request::isAjax() && Request::isGet())) {
            return;
        }

        $viewVars = [
            'metaTitle'   => $chapter->getTitle() . ' | ' . $book->getTitle() . META_SUFFIX,
            'html'        => $this->renderTemplate('books/chapter-details.twig', ['chapter'     => $chapter->toArray()]),
            'breadcrumbs' => $this->renderTemplate('elements/breadcrumb.twig',   ['breadcrumbs' => $breadcrumbs]),
        ];
        $this->renderJSONContent($viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxPostRequest(Book $book, Chapter $chapter): void {
        if ( ! (Request::isAjax() && Request::isPost())) {
            return;
        }

        $chapter->fromArray(Request::getVars('POST'));
        // updated_at field is excluded from timestampable behavior
        // as TOC updates should not have exert on it
        // (this is also why it's not set in the preUpdate model listener)
        if ($chapter->isModified()) {
            $chapter->setUpdatedAt(new \DateTimeImmutable());

            // update book's updated_at field on chapter update as well
            $book->setUpdatedAt(new \DateTimeImmutable());
            $chapter->setBook($book);
        }

        $status      = (bool) $this->saveWithValidation($chapter);
        $errors      = $this->reorganizeValidationErrors($chapter->getValidationFailures());
        $breadcrumbs = [['Books', Router::url(['controller' => 'books', 'action' => 'index'])], [$book->getTitle(), Router::url(['controller' => 'books', 'action' => 'edit', 'book' => $book->getSlug()])], [$chapter->getTitle(), NULL], 'Edit'];
        
        $response = [
            'status'      => $status,
            'errors'      => $errors,
            'flash'       => $this->generateFlashHtml(),
            'breadcrumbs' => $this->renderTemplate('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]),
        ];

        $this->renderJSONContent($response);
    }

}