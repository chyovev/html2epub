<?php

use Base\ChapterQuery as BaseChapterQuery;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

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
    // when filtering by slug, string needs to be converted to bytes
    public function findOneBySlug(string $slug): ?Chapter {
        
        // if the conversion from string fails,
        // just treat it as an empty result
        try {
            $uuid = Uuid::fromString($slug);

            return parent::findOneBySlug($uuid->getBytes());
        }
        catch (InvalidUuidStringException $e) {
            return NULL;
        }
    }
}
