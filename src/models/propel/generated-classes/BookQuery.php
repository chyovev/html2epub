<?php

use Base\BookQuery as BaseBookQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for performing query and update operations on the 'books' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class BookQuery extends BaseBookQuery
{

    ///////////////////////////////////////////////////////////////////////////
    public function orderByIdAsArray() {
        $data  = $this->orderById();
        $array = [];

        foreach ($data as $item) {
            $array[] = $item->toArray('fieldName');
        }

        return $array;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getChaptersAsNestedSet(?Book $book): array {
        if ( ! isset($book)) {
            return [];
        }

        // chapters need to be ordered by column «tree_left»
        // in order for the recursive tree generation to work properly
        $criteria = new Criteria();
        $criteria->addAscendingOrderByColumn('tree_left');

        $chapters = $book->getChapters($criteria);

        return self::createTree($chapters);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function createTree($array, $left = 0, $right = null) {
        $tree = [];

        foreach ($array as $key => $node) {
            $nodeLeft  = $node->getTreeLeft();
            $nodeRight = $node->getTreeRight();

            // if current node’s left is equal to previous node’s right + 1
            // and there is no previous node’s right OR it is greater than current node’s right
            // add node to tree and check for children 
            if ( ($nodeLeft == $left + 1 && (is_null($right) || $nodeRight < $right))
                 || ($left == 0 && is_null($right)) ) { // if root doesn't start from 1, but it's a root call

                $tree[$key] = $node->toArray(TableMap::TYPE_FIELDNAME);

                // when there are no children, the difference between
                // node’s right and node’s left is equal to 0
                if ($nodeRight - $nodeLeft > 1) {
                    $tree[$key]['children'] = self::createTree($array, $nodeLeft, $nodeRight);
                }

                // update left to current node’s right’s value
                // in order to skip readding children
                $left = $nodeRight;
            }
        }

        return $tree;
    }
}
