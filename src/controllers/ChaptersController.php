<?php
use Propel\Runtime\Map\TableMap;

class ChaptersController extends AppController {

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

}