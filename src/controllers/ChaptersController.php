<?php
use Propel\Runtime\Map\TableMap;

class ChaptersController extends AppController {

    ///////////////////////////////////////////////////////////////////////////
    public function edit() {
        $bookSlug    = getGetRequestVar('book_slug');
        $chapterSlug = getGetRequestVar('slug');
        $book        = BookQuery::create()->findOneBySlug($bookSlug);
        $chapter     = ChapterQuery::create()->findOneBySlug($chapterSlug);

        $this->_throw404OnEmpty($book && $chapter);

        $breadcrumbs = [['Books', Url::generateBooksIndexUrl()], [$book->getTitle(), Url::generateBookUrl($book->getSlug())], [$chapter->getTitle(), NULL], 'Edit'];

        // AJAX GET request  → load book as JSON
        // AJAX POST request → try to save book
        $this->_processAjaxGetRequest($book, $chapter, $breadcrumbs);
        $this->_processAjaxPostRequest($book, $chapter);

        $viewVars = [
            'book'        => $book->toArray(),
            'metaTitle'   => $chapter->getTitle() . ' | ' . $book->getTitle(),
            'title'       => $book->getTitle(),
            'chapter'     => $chapter->toArray(),
            'toc'         => BookQuery::getChaptersAsNestedSet($book),
            'wideHeader'  => true,
            'breadcrumbs' => $breadcrumbs,
        ];

        $this->_setView('books/add', $viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function updateToc() {
        $bookSlug = getGetRequestVar('book_slug');
        $book     = BookQuery::create()->findOneBySlug($bookSlug);

        // if the request is not POST or there's no such book, abort
        $this->_throw404OnEmpty(isRequest('POST') && $book);

        $chapters = $book->getChapters();
        $postData = getPostRequestVar('chapters');

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

        try {
            $status = (bool) $book->save();
        }
        catch (Exception $e) {
            $this->addError('TOC arrangement not saved: ' . $e->getMessage());
            $status = false;
        }

        $this->twig->renderJSONContent(['status' => $status]);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function add() {
        $bookSlug = getGetRequestVar('book_slug');
        $book     = BookQuery::create()->findOneBySlug($bookSlug);

        // if the request is not AJAX POST or there's no such book, abort
        $this->_throw404OnEmpty(isRequestAjax() && isRequest('POST') && $book);

        $chapter = new Chapter();
        $chapter->setTitle('New chapter')
                ->setTreeLeft(1);

        $book->addChapter($chapter);
        try {
            $response = [
                'status'     => (bool) $book->save(),
                'id'         => $chapter->getSlugAsString(),
                'title'      => $chapter->getTitle(),
                'edit_url'   => Url::generateChapterUrl($book->getSlug(), $chapter->getSlugAsString()),
                'delete_url' => Url::generateDeleteChapterUrl($book->getSlug(), $chapter->getSlugAsString()),
            ];
        }
        catch (Exception $e) {
            $this->addCriticalError('Chapter not added: ' . $e->getMessage());
            $response = [
                'status' => false,
                'errors' => 'An error occurred. Please try again later.',
            ];
        }

        $this->twig->renderJSONContent($response);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function delete() {
        if ( ! (isRequestAjax() && isRequest('GET'))) {
            return;
        }

        $bookSlug    = getGetRequestVar('book_slug');
        $chapterSlug = getGetRequestVar('slug');
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

        $this->twig->renderJSONContent(['status' => $status]);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxGetRequest(Book $book, Chapter $chapter, array $breadcrumbs = []): void {
        if ( ! (isRequestAjax() && isRequest('GET'))) {
            return;
        }

        $viewVars = [
            'metaTitle'   => $chapter->getTitle() . ' | ' . $book->getTitle() . META_SUFFIX,
            'html'        => $this->twig->render('books/chapter-details.twig', ['chapter'     => $chapter->toArray()]),
            'breadcrumbs' => $this->twig->render('elements/breadcrumb.twig',   ['breadcrumbs' => $breadcrumbs]),
        ];
        $this->twig->renderJSONContent($viewVars);
    }

    ///////////////////////////////////////////////////////////////////////////
    protected function _processAjaxPostRequest(Book $book, Chapter $chapter): void {
        if ( ! (isRequestAjax() && isRequest('POST'))) {
            return;
        }

        $chapter->fromArray(getRequestVariables('POST'));
        // updated_at field is excluded from timestampable behavior
        // as TOC updates should not have exert on it
        // (this is also why it's not set in the preUpdate model listener)
        if ($chapter->isModified()) {
            $chapter->setUpdatedAt(new \DateTimeImmutable());
        }

        $status      = (bool) $this->saveWithValidation($chapter);
        $errors      = $this->reorganizeValidationErrors($chapter->getValidationFailures());
        $breadcrumbs = [['Books', Url::generateBooksIndexUrl()], [$book->getTitle(), Url::generateBookUrl($book->getSlug())], [$chapter->getTitle(), NULL], 'Edit'];
        
        $response = [
            'status'      => $status,
            'errors'      => $errors,
            'flash'       => $this->twig->render('elements/flash.message.twig', ['flash' => FlashMessage::getFlashMessage(), 'hidden' => true]),
            'breadcrumbs' => $this->twig->render('elements/breadcrumb.twig', ['breadcrumbs' => $breadcrumbs]),
        ];

        $this->twig->renderJSONContent($response);
    }

}