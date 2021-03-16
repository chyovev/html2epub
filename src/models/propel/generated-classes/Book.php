<?php

use Base\Book as BaseBook;
use FileSystem as FS;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;
use Ramsey\Uuid\Uuid;

/**
 * Skeleton subclass for representing a row from the 'books' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Book extends BaseBook
{
    // used for generating UUIDv3 of Book
    const HTML2EPUB_BOOK = '4bdbe8ec-5cb5-11ea-bc55-0242ac130003';

    use FormatDataTrait;

    private $depth        = 0; // how deep the chapters go, used in toc.ncx
    private $chapterCount = 0; // how many chapters a book has, used for playOrder in toc.ncx

    ///////////////////////////////////////////////////////////////////////////
    // when a book has been deleted, delete its generated epub content, too
    public function postDelete(ConnectionInterface $con = null) {
        $path = BOOKS_PATH . '/generated/' . $this->getId();
        FS::deleteFolder($path);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getIdAsUuid(): Uuid {
        $id   = $this->getId();

        return Uuid::uuid3(self::HTML2EPUB_BOOK, $id);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getIdAsUuidString(): string {
        $uuid = $this->getIdAsUuid();

        return $uuid->toString();
    }

    ///////////////////////////////////////////////////////////////////////////
    // override default behavior to sort chapters by tree_left field
    public function getChapters(Criteria $criteria = null, ConnectionInterface $con = null) {
        if ( ! $criteria) {
            $criteria = new Criteria();
            $criteria->addAscendingOrderByColumn('tree_left');
        }

        $chapters = parent::getChapters($criteria, $con);
        $this->setChapterProperties($chapters);

        $this->chapterCount = \count($chapters);

        return $chapters;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getChaptersAsNestedSet(): array {
        // chapters need to be ordered by column «tree_left»
        // in order for the recursive tree generation to work properly
        $chapters = $this->getChapters();

        return $this->createTree($chapters);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getDepth(): int {
        return $this->depth;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getChapterCount(): int {
        return $this->chapterCount;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function createTree($chapters, $left = 0, $right = null) {
        $tree = [];

        foreach ($chapters as $key => $node) {
            $nodeLeft  = $node->getTreeLeft();
            $nodeRight = $node->getTreeRight();

            // if current node’s left is equal to previous node’s right + 1
            // and there is no previous node’s right OR it is greater than current node’s right
            // add node to tree and check for children 
            if ( ($nodeLeft == $left + 1 && (is_null($right) || $nodeRight < $right))
                 || ($left == 0 && is_null($right)) ) { // if root doesn't start from 1, but it's a root call

                $tree[$key] = $node;

                // when there are no children, the difference between
                // node’s right and node’s left is equal to 0
                if ($nodeRight - $nodeLeft > 1) {
                    // increase depth every time the function is called
                    $this->depth++;

                    $tree[$key]->setChildren($this->createTree($chapters, $nodeLeft, $nodeRight));
                }

                // update left to current node’s right’s value
                // in order to skip readding children
                $left = $nodeRight;
            }
        }

        return $tree;
    }

    ///////////////////////////////////////////////////////////////////////////
    private function setChapterProperties($chapters): void {
        $i = 1;
        foreach($chapters as $item) {
            $item->setPartNumber(sprintf("part%04d", $i));
            $item->setPlayOrder($i);
            $i++;
        }
    }
}
