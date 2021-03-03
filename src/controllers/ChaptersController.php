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
        $slug     = getGetRequestVar('slug');
        $book     = BookQuery::create()->findOneBySlug($slug);

        // if the request is not POST or there's no such book, abort
        $this->_throw404OnEmpty(isRequest('POST') && $book);

        $chapters = $book->getChapters();
        $postData = getPostRequestVar('chapters');


        // iterate through all chapters
        // and set new values for the tree properties using the POST request
        foreach ($chapters as $chapter) {
            $id = $chapter->getId();

            if (isset($postData[$id])) {
                $chapter->setTreeLeft($postData[$id]['tree_left']);
                $chapter->setTreeRight($postData[$id]['tree_right']);
                $chapter->setTreeLevel($postData[$id]['tree_level']);
            }
        }

        $book->setChapters($chapters);

        try {
            $status = (bool) $book->save();
        }
        catch (Exception $e) {
            // TODO: Log error
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
            'metaTitle'   => $chapter->getTitle() . ' | ' . $book->getTitle(),
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
        if ($chapter->isModified()) {
            $chapter->setUpdatedAt(new \DateTime());
        }


        $status      = (bool) $chapter->saveWithValidation();
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