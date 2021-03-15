<?php

namespace Map;

use \Book;
use \BookQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'books' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class BookTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.Map.BookTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'html2epub';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'books';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\Book';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'Book';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 15;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 15;

    /**
     * the column name for the id field
     */
    const COL_ID = 'books.id';

    /**
     * the column name for the title field
     */
    const COL_TITLE = 'books.title';

    /**
     * the column name for the subtitle field
     */
    const COL_SUBTITLE = 'books.subtitle';

    /**
     * the column name for the slug field
     */
    const COL_SLUG = 'books.slug';

    /**
     * the column name for the author field
     */
    const COL_AUTHOR = 'books.author';

    /**
     * the column name for the dedication field
     */
    const COL_DEDICATION = 'books.dedication';

    /**
     * the column name for the language_id field
     */
    const COL_LANGUAGE_ID = 'books.language_id';

    /**
     * the column name for the publisher field
     */
    const COL_PUBLISHER = 'books.publisher';

    /**
     * the column name for the year field
     */
    const COL_YEAR = 'books.year';

    /**
     * the column name for the isbn field
     */
    const COL_ISBN = 'books.isbn';

    /**
     * the column name for the extra_info field
     */
    const COL_EXTRA_INFO = 'books.extra_info';

    /**
     * the column name for the include_font field
     */
    const COL_INCLUDE_FONT = 'books.include_font';

    /**
     * the column name for the cover_image field
     */
    const COL_COVER_IMAGE = 'books.cover_image';

    /**
     * the column name for the created_at field
     */
    const COL_CREATED_AT = 'books.created_at';

    /**
     * the column name for the updated_at field
     */
    const COL_UPDATED_AT = 'books.updated_at';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'Title', 'Subtitle', 'Slug', 'Author', 'Dedication', 'LanguageId', 'Publisher', 'Year', 'Isbn', 'ExtraInfo', 'IncludeFont', 'CoverImage', 'CreatedAt', 'UpdatedAt', ),
        self::TYPE_CAMELNAME     => array('id', 'title', 'subtitle', 'slug', 'author', 'dedication', 'languageId', 'publisher', 'year', 'isbn', 'extraInfo', 'includeFont', 'coverImage', 'createdAt', 'updatedAt', ),
        self::TYPE_COLNAME       => array(BookTableMap::COL_ID, BookTableMap::COL_TITLE, BookTableMap::COL_SUBTITLE, BookTableMap::COL_SLUG, BookTableMap::COL_AUTHOR, BookTableMap::COL_DEDICATION, BookTableMap::COL_LANGUAGE_ID, BookTableMap::COL_PUBLISHER, BookTableMap::COL_YEAR, BookTableMap::COL_ISBN, BookTableMap::COL_EXTRA_INFO, BookTableMap::COL_INCLUDE_FONT, BookTableMap::COL_COVER_IMAGE, BookTableMap::COL_CREATED_AT, BookTableMap::COL_UPDATED_AT, ),
        self::TYPE_FIELDNAME     => array('id', 'title', 'subtitle', 'slug', 'author', 'dedication', 'language_id', 'publisher', 'year', 'isbn', 'extra_info', 'include_font', 'cover_image', 'created_at', 'updated_at', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Title' => 1, 'Subtitle' => 2, 'Slug' => 3, 'Author' => 4, 'Dedication' => 5, 'LanguageId' => 6, 'Publisher' => 7, 'Year' => 8, 'Isbn' => 9, 'ExtraInfo' => 10, 'IncludeFont' => 11, 'CoverImage' => 12, 'CreatedAt' => 13, 'UpdatedAt' => 14, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'title' => 1, 'subtitle' => 2, 'slug' => 3, 'author' => 4, 'dedication' => 5, 'languageId' => 6, 'publisher' => 7, 'year' => 8, 'isbn' => 9, 'extraInfo' => 10, 'includeFont' => 11, 'coverImage' => 12, 'createdAt' => 13, 'updatedAt' => 14, ),
        self::TYPE_COLNAME       => array(BookTableMap::COL_ID => 0, BookTableMap::COL_TITLE => 1, BookTableMap::COL_SUBTITLE => 2, BookTableMap::COL_SLUG => 3, BookTableMap::COL_AUTHOR => 4, BookTableMap::COL_DEDICATION => 5, BookTableMap::COL_LANGUAGE_ID => 6, BookTableMap::COL_PUBLISHER => 7, BookTableMap::COL_YEAR => 8, BookTableMap::COL_ISBN => 9, BookTableMap::COL_EXTRA_INFO => 10, BookTableMap::COL_INCLUDE_FONT => 11, BookTableMap::COL_COVER_IMAGE => 12, BookTableMap::COL_CREATED_AT => 13, BookTableMap::COL_UPDATED_AT => 14, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'title' => 1, 'subtitle' => 2, 'slug' => 3, 'author' => 4, 'dedication' => 5, 'language_id' => 6, 'publisher' => 7, 'year' => 8, 'isbn' => 9, 'extra_info' => 10, 'include_font' => 11, 'cover_image' => 12, 'created_at' => 13, 'updated_at' => 14, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('books');
        $this->setPhpName('Book');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Book');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('title', 'Title', 'VARCHAR', true, 255, null);
        $this->addColumn('subtitle', 'Subtitle', 'VARCHAR', false, 255, null);
        $this->addColumn('slug', 'Slug', 'VARCHAR', true, 255, null);
        $this->addColumn('author', 'Author', 'VARCHAR', false, 255, null);
        $this->addColumn('dedication', 'Dedication', 'VARCHAR', false, 255, null);
        $this->addForeignKey('language_id', 'LanguageId', 'INTEGER', 'languages', 'id', true, null, 0);
        $this->addColumn('publisher', 'Publisher', 'VARCHAR', false, 255, null);
        $this->addColumn('year', 'Year', 'INTEGER', false, null, null);
        $this->addColumn('isbn', 'Isbn', 'VARCHAR', false, 255, null);
        $this->addColumn('extra_info', 'ExtraInfo', 'LONGVARCHAR', false, null, null);
        $this->addColumn('include_font', 'IncludeFont', 'BOOLEAN', true, 1, false);
        $this->addColumn('cover_image', 'CoverImage', 'VARCHAR', false, 255, null);
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('updated_at', 'UpdatedAt', 'TIMESTAMP', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Language', '\\Language', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':language_id',
    1 => ':id',
  ),
), null, null, null, false);
        $this->addRelation('Chapter', '\\Chapter', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':book_id',
    1 => ':id',
  ),
), 'CASCADE', null, 'Chapters', false);
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'single_image_upload' => array('table_column' => 'cover_image', 'group' => 'image', 'path' => 'uploads/books', 'required' => '', 'max_size_mb' => '2', 'min_size_mb' => '0', ),
            'timestampable' => array('create_column' => 'created_at', 'update_column' => 'updated_at', 'disable_created_at' => 'false', 'disable_updated_at' => 'false', ),
            'validate' => array('title_notnull' => array ('column' => 'title','validator' => 'NotBlank','options' => array ('allowNull' => false,),), 'title_maxlength' => array ('column' => 'title','validator' => 'Length','options' => array ('max' => 255,'allowEmptyString' => false,),), 'slug_notnull' => array ('column' => 'slug','validator' => 'NotBlank','options' => array ('allowNull' => false,),), 'slug_maxlength' => array ('column' => 'slug','validator' => 'Length','options' => array ('max' => 255,'allowEmptyString' => false,),), 'slug_unique' => array ('column' => 'slug','validator' => 'Unique','options' => array ('message' => 'A book with this slug already exists.',),), 'slug_regex' => array ('column' => 'slug','validator' => 'Regex','options' => array ('pattern' => '/^[a-z0-9\\-]+$/','message' => 'Please use only lowercase latin letters and dashes.',),), 'slug_reserved' => array ('column' => 'slug','validator' => 'Regex','options' => array ('pattern' => '/^(?!add$)[a-z0-9\\-]+$/','message' => 'Reserved words are not allowed.',),), 'language_notnull' => array ('column' => 'language_id','validator' => 'NotBlank','options' => array ('allowNull' => false,'message' => 'Please select a language from the dropdown menu.',),), 'language_int' => array ('column' => 'language_id','validator' => 'GreaterThan','options' => array ('value' => 0,'message' => 'Please select a language from the dropdown menu.',),), 'author_maxlength' => array ('column' => 'author','validator' => 'Length','options' => array ('max' => 255,),), 'dedication_maxlength' => array ('column' => 'dedication','validator' => 'Length','options' => array ('max' => 255,),), 'publisher_maxlength' => array ('column' => 'publisher','validator' => 'Length','options' => array ('max' => 255,),), 'isbn_maxlength' => array ('column' => 'isbn','validator' => 'Length','options' => array ('max' => 255,),), 'extra_info_maxlength' => array ('column' => 'extra_info','validator' => 'Length','options' => array ('max' => 65535,),), 'isbn' => array ('column' => 'isbn','validator' => 'Isbn',), ),
        );
    } // getBehaviors()
    /**
     * Method to invalidate the instance pool of all tables related to books     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        ChapterTableMap::clearInstancePool();
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? BookTableMap::CLASS_DEFAULT : BookTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (Book object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = BookTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = BookTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + BookTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = BookTableMap::OM_CLASS;
            /** @var Book $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            BookTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = BookTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = BookTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Book $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                BookTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(BookTableMap::COL_ID);
            $criteria->addSelectColumn(BookTableMap::COL_TITLE);
            $criteria->addSelectColumn(BookTableMap::COL_SUBTITLE);
            $criteria->addSelectColumn(BookTableMap::COL_SLUG);
            $criteria->addSelectColumn(BookTableMap::COL_AUTHOR);
            $criteria->addSelectColumn(BookTableMap::COL_DEDICATION);
            $criteria->addSelectColumn(BookTableMap::COL_LANGUAGE_ID);
            $criteria->addSelectColumn(BookTableMap::COL_PUBLISHER);
            $criteria->addSelectColumn(BookTableMap::COL_YEAR);
            $criteria->addSelectColumn(BookTableMap::COL_ISBN);
            $criteria->addSelectColumn(BookTableMap::COL_EXTRA_INFO);
            $criteria->addSelectColumn(BookTableMap::COL_INCLUDE_FONT);
            $criteria->addSelectColumn(BookTableMap::COL_COVER_IMAGE);
            $criteria->addSelectColumn(BookTableMap::COL_CREATED_AT);
            $criteria->addSelectColumn(BookTableMap::COL_UPDATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.title');
            $criteria->addSelectColumn($alias . '.subtitle');
            $criteria->addSelectColumn($alias . '.slug');
            $criteria->addSelectColumn($alias . '.author');
            $criteria->addSelectColumn($alias . '.dedication');
            $criteria->addSelectColumn($alias . '.language_id');
            $criteria->addSelectColumn($alias . '.publisher');
            $criteria->addSelectColumn($alias . '.year');
            $criteria->addSelectColumn($alias . '.isbn');
            $criteria->addSelectColumn($alias . '.extra_info');
            $criteria->addSelectColumn($alias . '.include_font');
            $criteria->addSelectColumn($alias . '.cover_image');
            $criteria->addSelectColumn($alias . '.created_at');
            $criteria->addSelectColumn($alias . '.updated_at');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(BookTableMap::DATABASE_NAME)->getTable(BookTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(BookTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(BookTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new BookTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a Book or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or Book object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Book) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(BookTableMap::DATABASE_NAME);
            $criteria->add(BookTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = BookQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            BookTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                BookTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the books table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return BookQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Book or Criteria object.
     *
     * @param mixed               $criteria Criteria or Book object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Book object
        }

        if ($criteria->containsKey(BookTableMap::COL_ID) && $criteria->keyContainsValue(BookTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.BookTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = BookQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // BookTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BookTableMap::buildTableMap();
