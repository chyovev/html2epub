<?php

use Base\Chapter as BaseChapter;
use Propel\Runtime\ActiveQuery\Criteria;
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
    // overwrite toArray function from trait
    use FormatDataTrait {
        toArray as public toArrayTrait;
    }

    // children are set in the creatTree() method of Book model
    private $children;

    // used on epub generation for toc.ncx and content.opf,
    // set in setChapterProperties() method of Book model
    private $partNumber;
    private $playOrder;
    

    ///////////////////////////////////////////////////////////////////////////
    // before creating a new record, a random UUID slug needs to be generated
    public function preInsert(ConnectionInterface $con = null) {
        $this->generateSlugUuid();
        return parent::preInsert($con);
    }

    ///////////////////////////////////////////////////////////////////////////
    // if the body is going to be empty, just set it to NULL
    public function preSave(ConnectionInterface $con = null) {
        if ( ! $this->getBody()) {
            $this->setBody(NULL);
        }

        return true;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function generateSlugUuid() {
        if ( ! $this->getSlug()) {
            // avoid possible collision by checking whether value exists
            do {
                $uuid = Uuid::uuid4();
            }
            while (ChapterQuery::slugExists($uuid));

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
    // NB! children are set in the createTree method of Book,
    // so the default getChildren method of the NestedSetBehavior should be overwritten
    // just the return the property, no DB data fetch
    public function getChildren(Criteria $criteria = null, ConnectionInterface $con = null) {
        return $this->children;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    public function setChildren($children): self {
        $this->children = $children;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getPartNumber(): string {
        return $this->partNumber;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setPartNumber(string $number): self {
        $this->partNumber = $number;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getPlayOrder(): string {
        return $this->playOrder;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function setPlayOrder(string $number): self {
        $this->playOrder = $number;

        return $this;
    }

    ///////////////////////////////////////////////////////////////////////////
    public function getSlugAsString(): string {
        $slug = $this->getSlug();
        $uuid = Uuid::fromBytes($slug);

        return $uuid->toString();
    }

    ///////////////////////////////////////////////////////////////////////////
    // NB! there's no one single tree root; all zero-depth items are practically roots,
    // deleting the first zero-depth chapter should delete only its children,
    // but it throws an exception as it is considered "root"
    // this little hack takes care of that, even if it's cringeworthy
    public function isRoot() {
        return false;
    }

    ///////////////////////////////////////////////////////////////////////////
    // used only for the breadcrumb
    public function getAncestorsLinks(string $bookSlug, string $action): array {
        $links     = [];
        $ancestors = $this->getAncestors();

        foreach ($ancestors as $item) {
            $links[] = [$item->getTitle(), Router::url(['controller' => 'chapters', 'action' => 'edit', 'book' => $bookSlug, 'chapter' => $item->getSlugAsString()])];
        }

        // add self at the end
        $links[] = [$this->getTitle(), NULL];

        $links[] = [$action, NULL];

        return $links;
    }
}
