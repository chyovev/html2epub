<?php

namespace Map;

use \Chapter;
use \ChapterQuery;
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
 * This class defines the structure of the 'chapters' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ChapterTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.Map.ChapterTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'html2epub';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'chapters';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\Chapter';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'Chapter';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 10;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 10;

    /**
     * the column name for the id field
     */
    const COL_ID = 'chapters.id';

    /**
     * the column name for the book_id field
     */
    const COL_BOOK_ID = 'chapters.book_id';

    /**
     * the column name for the title field
     */
    const COL_TITLE = 'chapters.title';

    /**
     * the column name for the slug field
     */
    const COL_SLUG = 'chapters.slug';

    /**
     * the column name for the body field
     */
    const COL_BODY = 'chapters.body';

    /**
     * the column name for the updated_at field
     */
    const COL_UPDATED_AT = 'chapters.updated_at';

    /**
     * the column name for the tree_left field
     */
    const COL_TREE_LEFT = 'chapters.tree_left';

    /**
     * the column name for the tree_right field
     */
    const COL_TREE_RIGHT = 'chapters.tree_right';

    /**
     * the column name for the tree_level field
     */
    const COL_TREE_LEVEL = 'chapters.tree_level';

    /**
     * the column name for the created_at field
     */
    const COL_CREATED_AT = 'chapters.created_at';

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
        self::TYPE_PHPNAME       => array('Id', 'BookId', 'Title', 'Slug', 'Body', 'UpdatedAt', 'TreeLeft', 'TreeRight', 'TreeLevel', 'CreatedAt', ),
        self::TYPE_CAMELNAME     => array('id', 'bookId', 'title', 'slug', 'body', 'updatedAt', 'treeLeft', 'treeRight', 'treeLevel', 'createdAt', ),
        self::TYPE_COLNAME       => array(ChapterTableMap::COL_ID, ChapterTableMap::COL_BOOK_ID, ChapterTableMap::COL_TITLE, ChapterTableMap::COL_SLUG, ChapterTableMap::COL_BODY, ChapterTableMap::COL_UPDATED_AT, ChapterTableMap::COL_TREE_LEFT, ChapterTableMap::COL_TREE_RIGHT, ChapterTableMap::COL_TREE_LEVEL, ChapterTableMap::COL_CREATED_AT, ),
        self::TYPE_FIELDNAME     => array('id', 'book_id', 'title', 'slug', 'body', 'updated_at', 'tree_left', 'tree_right', 'tree_level', 'created_at', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'BookId' => 1, 'Title' => 2, 'Slug' => 3, 'Body' => 4, 'UpdatedAt' => 5, 'TreeLeft' => 6, 'TreeRight' => 7, 'TreeLevel' => 8, 'CreatedAt' => 9, ),
        self::TYPE_CAMELNAME     => array('id' => 0, 'bookId' => 1, 'title' => 2, 'slug' => 3, 'body' => 4, 'updatedAt' => 5, 'treeLeft' => 6, 'treeRight' => 7, 'treeLevel' => 8, 'createdAt' => 9, ),
        self::TYPE_COLNAME       => array(ChapterTableMap::COL_ID => 0, ChapterTableMap::COL_BOOK_ID => 1, ChapterTableMap::COL_TITLE => 2, ChapterTableMap::COL_SLUG => 3, ChapterTableMap::COL_BODY => 4, ChapterTableMap::COL_UPDATED_AT => 5, ChapterTableMap::COL_TREE_LEFT => 6, ChapterTableMap::COL_TREE_RIGHT => 7, ChapterTableMap::COL_TREE_LEVEL => 8, ChapterTableMap::COL_CREATED_AT => 9, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'book_id' => 1, 'title' => 2, 'slug' => 3, 'body' => 4, 'updated_at' => 5, 'tree_left' => 6, 'tree_right' => 7, 'tree_level' => 8, 'created_at' => 9, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, )
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
        $this->setName('chapters');
        $this->setPhpName('Chapter');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Chapter');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('book_id', 'BookId', 'INTEGER', 'books', 'id', true, null, 0);
        $this->addColumn('title', 'Title', 'VARCHAR', true, 255, null);
        $this->addColumn('slug', 'Slug', 'BINARY', true, 16, null);
        $this->addColumn('body', 'Body', 'CLOB', false, null, null);
        $this->addColumn('updated_at', 'UpdatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('tree_left', 'TreeLeft', 'INTEGER', false, null, null);
        $this->addColumn('tree_right', 'TreeRight', 'INTEGER', false, null, null);
        $this->addColumn('tree_level', 'TreeLevel', 'INTEGER', false, null, null);
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Book', '\\Book', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':book_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
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
            'nested_set' => array('left_column' => 'tree_left', 'right_column' => 'tree_right', 'level_column' => 'tree_level', 'use_scope' => 'true', 'scope_column' => 'book_id', 'method_proxies' => 'false', ),
            'timestampable' => array('create_column' => 'created_at', 'update_column' => 'updated_at', 'disable_created_at' => 'false', 'disable_updated_at' => 'true', ),
            'validate' => array('title_notnull' => array ('column' => 'title','validator' => 'NotBlank','options' => array ('allowNull' => false,),), 'title_maxlength' => array ('column' => 'title','validator' => 'Length','options' => array ('max' => 255,'allowEmptyString' => false,),), 'slug_unique' => array ('column' => 'slug','validator' => 'Unique','options' => array ('message' => 'A chapter with this slug already exists.',),), 'body_maxlength' => array ('column' => 'body','validator' => 'Length','options' => array ('max' => 16777215,),), ),
        );
    } // getBehaviors()

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
        return $withPrefix ? ChapterTableMap::CLASS_DEFAULT : ChapterTableMap::OM_CLASS;
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
     * @return array           (Chapter object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = ChapterTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ChapterTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ChapterTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ChapterTableMap::OM_CLASS;
            /** @var Chapter $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ChapterTableMap::addInstanceToPool($obj, $key);
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
            $key = ChapterTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ChapterTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Chapter $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ChapterTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ChapterTableMap::COL_ID);
            $criteria->addSelectColumn(ChapterTableMap::COL_BOOK_ID);
            $criteria->addSelectColumn(ChapterTableMap::COL_TITLE);
            $criteria->addSelectColumn(ChapterTableMap::COL_SLUG);
            $criteria->addSelectColumn(ChapterTableMap::COL_BODY);
            $criteria->addSelectColumn(ChapterTableMap::COL_UPDATED_AT);
            $criteria->addSelectColumn(ChapterTableMap::COL_TREE_LEFT);
            $criteria->addSelectColumn(ChapterTableMap::COL_TREE_RIGHT);
            $criteria->addSelectColumn(ChapterTableMap::COL_TREE_LEVEL);
            $criteria->addSelectColumn(ChapterTableMap::COL_CREATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.book_id');
            $criteria->addSelectColumn($alias . '.title');
            $criteria->addSelectColumn($alias . '.slug');
            $criteria->addSelectColumn($alias . '.body');
            $criteria->addSelectColumn($alias . '.updated_at');
            $criteria->addSelectColumn($alias . '.tree_left');
            $criteria->addSelectColumn($alias . '.tree_right');
            $criteria->addSelectColumn($alias . '.tree_level');
            $criteria->addSelectColumn($alias . '.created_at');
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
        return Propel::getServiceContainer()->getDatabaseMap(ChapterTableMap::DATABASE_NAME)->getTable(ChapterTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(ChapterTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(ChapterTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new ChapterTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a Chapter or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or Chapter object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Chapter) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ChapterTableMap::DATABASE_NAME);
            $criteria->add(ChapterTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ChapterQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ChapterTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ChapterTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the chapters table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return ChapterQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Chapter or Criteria object.
     *
     * @param mixed               $criteria Criteria or Chapter object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Chapter object
        }

        if ($criteria->containsKey(ChapterTableMap::COL_ID) && $criteria->keyContainsValue(ChapterTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ChapterTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = ChapterQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // ChapterTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
ChapterTableMap::buildTableMap();
