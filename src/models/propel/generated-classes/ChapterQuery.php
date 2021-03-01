<?php

use Base\ChapterQuery as BaseChapterQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'chapters' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class ChapterQuery extends BaseChapterQuery
{

    ///////////////////////////////////////////////////////////////////////////
    public function getChaptersTreeByBook(Book $book = NULL): array {
        if ( ! isset($book)) {
            return [];
        }

        $root = $this->retrieveRoot($book->getId());

        return $this->nestTree($root);
    }

    ///////////////////////////////////////////////////////////////////////////
    private function nestTree($node = NULL): array {
        if ( ! isset($node)) {
            return [];
        }
        
        $id                    = $node->getId();
        $tree[$id]             = $node->toArray('fieldName');
        $tree[$id]['children'] = $this->nestNodeChildren($node);

        while ($sibling = $node->getNextSibling()) {
            $id                    = $sibling->getId();
            $tree[$id]             = $sibling->toArray('fieldName');
            $tree[$id]['children'] = $this->nestNodeChildren($sibling);
            
            $node = $sibling;
        }

        return $tree;
    }

    ///////////////////////////////////////////////////////////////////////////
    private function nestNodeChildren($node) {
        $children = [];

        foreach ($node->getChildren() as $child) {
            $i                        = $child->getId();
            $children[$i]             = $child->toArray('fieldName');
            $children[$i]['children'] = $this->nestNodeChildren($child);
        }

        return $children;
    }
}
