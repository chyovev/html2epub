<?php

use Base\Chapter as BaseChapter;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Map\TableMap;
use Ramsey\Uuid\Uuid;

/**
 * Skeleton subclass for representing a row from the 'chapters' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Chapter extends BaseChapter
{
    use SaveTrait;

    // overwrite toArray function from trait
    use FormatDataTrait {
        toArray as public toArrayTrait;
    }

    ///////////////////////////////////////////////////////////////////////////
    // before creating a new record, a random UUID slug needs to be generated
    public function preInsert(ConnectionInterface $con = null) {
        $this->generateSlugUuid();
        return parent::preInsert($con);
    }

    ///////////////////////////////////////////////////////////////////////////
    public function generateSlugUuid() {
        if ( ! $this->getSlug()) {
            $uuid = Uuid::uuid4();
            $this->setSlug($uuid->getBytes());
        }
    }

    ///////////////////////////////////////////////////////////////////////////
    public function toArray(
        $keyType                = TableMap::TYPE_FIELDNAME,
        $includeLazyLoadColumns = true,
        $alreadyDumpedObjects   = [],
        $includeForeignObjects  = false
    ): array {
        // call the trait function, convert the UUID to string
        // and overwrite the binary value
        $result         = $this->toArrayTrait();
        $uuid           = Uuid::fromBytes($result['slug']);
        $result['slug'] = $uuid->toString();

        return $result;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getSlugAsString(): string {
        $slug = $this->getSlug();
        $uuid = Uuid::fromBytes($slug);

        return $uuid->toString();
    }
}
