<?php

namespace Base;

use \Chapter as ChildChapter;
use \ChapterQuery as ChildChapterQuery;
use \Exception;
use \PDO;
use Map\ChapterTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;

/**
 * Base class that represents a query for the 'chapters' table.
 *
 *
 *
 * @method     ChildChapterQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildChapterQuery orderByBookId($order = Criteria::ASC) Order by the book_id column
 * @method     ChildChapterQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildChapterQuery orderBySlug($order = Criteria::ASC) Order by the slug column
 * @method     ChildChapterQuery orderByBody($order = Criteria::ASC) Order by the body column
 * @method     ChildChapterQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 * @method     ChildChapterQuery orderByTreeLeft($order = Criteria::ASC) Order by the tree_left column
 * @method     ChildChapterQuery orderByTreeRight($order = Criteria::ASC) Order by the tree_right column
 * @method     ChildChapterQuery orderByTreeLevel($order = Criteria::ASC) Order by the tree_level column
 * @method     ChildChapterQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 *
 * @method     ChildChapterQuery groupById() Group by the id column
 * @method     ChildChapterQuery groupByBookId() Group by the book_id column
 * @method     ChildChapterQuery groupByTitle() Group by the title column
 * @method     ChildChapterQuery groupBySlug() Group by the slug column
 * @method     ChildChapterQuery groupByBody() Group by the body column
 * @method     ChildChapterQuery groupByUpdatedAt() Group by the updated_at column
 * @method     ChildChapterQuery groupByTreeLeft() Group by the tree_left column
 * @method     ChildChapterQuery groupByTreeRight() Group by the tree_right column
 * @method     ChildChapterQuery groupByTreeLevel() Group by the tree_level column
 * @method     ChildChapterQuery groupByCreatedAt() Group by the created_at column
 *
 * @method     ChildChapterQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildChapterQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildChapterQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildChapterQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildChapterQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildChapterQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildChapterQuery leftJoinBook($relationAlias = null) Adds a LEFT JOIN clause to the query using the Book relation
 * @method     ChildChapterQuery rightJoinBook($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Book relation
 * @method     ChildChapterQuery innerJoinBook($relationAlias = null) Adds a INNER JOIN clause to the query using the Book relation
 *
 * @method     ChildChapterQuery joinWithBook($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Book relation
 *
 * @method     ChildChapterQuery leftJoinWithBook() Adds a LEFT JOIN clause and with to the query using the Book relation
 * @method     ChildChapterQuery rightJoinWithBook() Adds a RIGHT JOIN clause and with to the query using the Book relation
 * @method     ChildChapterQuery innerJoinWithBook() Adds a INNER JOIN clause and with to the query using the Book relation
 *
 * @method     \BookQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildChapter findOne(ConnectionInterface $con = null) Return the first ChildChapter matching the query
 * @method     ChildChapter findOneOrCreate(ConnectionInterface $con = null) Return the first ChildChapter matching the query, or a new ChildChapter object populated from the query conditions when no match is found
 *
 * @method     ChildChapter findOneById(int $id) Return the first ChildChapter filtered by the id column
 * @method     ChildChapter findOneByBookId(int $book_id) Return the first ChildChapter filtered by the book_id column
 * @method     ChildChapter findOneByTitle(string $title) Return the first ChildChapter filtered by the title column
 * @method     ChildChapter findOneBySlug(string $slug) Return the first ChildChapter filtered by the slug column
 * @method     ChildChapter findOneByBody(string $body) Return the first ChildChapter filtered by the body column
 * @method     ChildChapter findOneByUpdatedAt(string $updated_at) Return the first ChildChapter filtered by the updated_at column
 * @method     ChildChapter findOneByTreeLeft(int $tree_left) Return the first ChildChapter filtered by the tree_left column
 * @method     ChildChapter findOneByTreeRight(int $tree_right) Return the first ChildChapter filtered by the tree_right column
 * @method     ChildChapter findOneByTreeLevel(int $tree_level) Return the first ChildChapter filtered by the tree_level column
 * @method     ChildChapter findOneByCreatedAt(string $created_at) Return the first ChildChapter filtered by the created_at column *

 * @method     ChildChapter requirePk($key, ConnectionInterface $con = null) Return the ChildChapter by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOne(ConnectionInterface $con = null) Return the first ChildChapter matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildChapter requireOneById(int $id) Return the first ChildChapter filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByBookId(int $book_id) Return the first ChildChapter filtered by the book_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByTitle(string $title) Return the first ChildChapter filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneBySlug(string $slug) Return the first ChildChapter filtered by the slug column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByBody(string $body) Return the first ChildChapter filtered by the body column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByUpdatedAt(string $updated_at) Return the first ChildChapter filtered by the updated_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByTreeLeft(int $tree_left) Return the first ChildChapter filtered by the tree_left column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByTreeRight(int $tree_right) Return the first ChildChapter filtered by the tree_right column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByTreeLevel(int $tree_level) Return the first ChildChapter filtered by the tree_level column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildChapter requireOneByCreatedAt(string $created_at) Return the first ChildChapter filtered by the created_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildChapter[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildChapter objects based on current ModelCriteria
 * @method     ChildChapter[]|ObjectCollection findById(int $id) Return ChildChapter objects filtered by the id column
 * @method     ChildChapter[]|ObjectCollection findByBookId(int $book_id) Return ChildChapter objects filtered by the book_id column
 * @method     ChildChapter[]|ObjectCollection findByTitle(string $title) Return ChildChapter objects filtered by the title column
 * @method     ChildChapter[]|ObjectCollection findBySlug(string $slug) Return ChildChapter objects filtered by the slug column
 * @method     ChildChapter[]|ObjectCollection findByBody(string $body) Return ChildChapter objects filtered by the body column
 * @method     ChildChapter[]|ObjectCollection findByUpdatedAt(string $updated_at) Return ChildChapter objects filtered by the updated_at column
 * @method     ChildChapter[]|ObjectCollection findByTreeLeft(int $tree_left) Return ChildChapter objects filtered by the tree_left column
 * @method     ChildChapter[]|ObjectCollection findByTreeRight(int $tree_right) Return ChildChapter objects filtered by the tree_right column
 * @method     ChildChapter[]|ObjectCollection findByTreeLevel(int $tree_level) Return ChildChapter objects filtered by the tree_level column
 * @method     ChildChapter[]|ObjectCollection findByCreatedAt(string $created_at) Return ChildChapter objects filtered by the created_at column
 * @method     ChildChapter[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class ChapterQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Base\ChapterQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'html2epub', $modelName = '\\Chapter', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildChapterQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildChapterQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildChapterQuery) {
            return $criteria;
        }
        $query = new ChildChapterQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildChapter|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ChapterTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ChapterTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildChapter A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, book_id, title, slug, body, updated_at, tree_left, tree_right, tree_level, created_at FROM chapters WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildChapter $obj */
            $obj = new ChildChapter();
            $obj->hydrate($row);
            ChapterTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildChapter|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ChapterTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ChapterTableMap::COL_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ChapterTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ChapterTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the book_id column
     *
     * Example usage:
     * <code>
     * $query->filterByBookId(1234); // WHERE book_id = 1234
     * $query->filterByBookId(array(12, 34)); // WHERE book_id IN (12, 34)
     * $query->filterByBookId(array('min' => 12)); // WHERE book_id > 12
     * </code>
     *
     * @see       filterByBook()
     *
     * @param     mixed $bookId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByBookId($bookId = null, $comparison = null)
    {
        if (is_array($bookId)) {
            $useMinMax = false;
            if (isset($bookId['min'])) {
                $this->addUsingAlias(ChapterTableMap::COL_BOOK_ID, $bookId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($bookId['max'])) {
                $this->addUsingAlias(ChapterTableMap::COL_BOOK_ID, $bookId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_BOOK_ID, $bookId, $comparison);
    }

    /**
     * Filter the query on the title column
     *
     * Example usage:
     * <code>
     * $query->filterByTitle('fooValue');   // WHERE title = 'fooValue'
     * $query->filterByTitle('%fooValue%', Criteria::LIKE); // WHERE title LIKE '%fooValue%'
     * </code>
     *
     * @param     string $title The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByTitle($title = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($title)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_TITLE, $title, $comparison);
    }

    /**
     * Filter the query on the slug column
     *
     * Example usage:
     * <code>
     * $query->filterBySlug('fooValue');   // WHERE slug = 'fooValue'
     * $query->filterBySlug('%fooValue%', Criteria::LIKE); // WHERE slug LIKE '%fooValue%'
     * </code>
     *
     * @param     string $slug The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterBySlug($slug = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($slug)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_SLUG, $slug, $comparison);
    }

    /**
     * Filter the query on the body column
     *
     * Example usage:
     * <code>
     * $query->filterByBody('fooValue');   // WHERE body = 'fooValue'
     * $query->filterByBody('%fooValue%', Criteria::LIKE); // WHERE body LIKE '%fooValue%'
     * </code>
     *
     * @param     string $body The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByBody($body = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($body)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_BODY, $body, $comparison);
    }

    /**
     * Filter the query on the updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE updated_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(ChapterTableMap::COL_UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(ChapterTableMap::COL_UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query on the tree_left column
     *
     * Example usage:
     * <code>
     * $query->filterByTreeLeft(1234); // WHERE tree_left = 1234
     * $query->filterByTreeLeft(array(12, 34)); // WHERE tree_left IN (12, 34)
     * $query->filterByTreeLeft(array('min' => 12)); // WHERE tree_left > 12
     * </code>
     *
     * @param     mixed $treeLeft The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByTreeLeft($treeLeft = null, $comparison = null)
    {
        if (is_array($treeLeft)) {
            $useMinMax = false;
            if (isset($treeLeft['min'])) {
                $this->addUsingAlias(ChapterTableMap::COL_TREE_LEFT, $treeLeft['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($treeLeft['max'])) {
                $this->addUsingAlias(ChapterTableMap::COL_TREE_LEFT, $treeLeft['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_TREE_LEFT, $treeLeft, $comparison);
    }

    /**
     * Filter the query on the tree_right column
     *
     * Example usage:
     * <code>
     * $query->filterByTreeRight(1234); // WHERE tree_right = 1234
     * $query->filterByTreeRight(array(12, 34)); // WHERE tree_right IN (12, 34)
     * $query->filterByTreeRight(array('min' => 12)); // WHERE tree_right > 12
     * </code>
     *
     * @param     mixed $treeRight The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByTreeRight($treeRight = null, $comparison = null)
    {
        if (is_array($treeRight)) {
            $useMinMax = false;
            if (isset($treeRight['min'])) {
                $this->addUsingAlias(ChapterTableMap::COL_TREE_RIGHT, $treeRight['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($treeRight['max'])) {
                $this->addUsingAlias(ChapterTableMap::COL_TREE_RIGHT, $treeRight['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_TREE_RIGHT, $treeRight, $comparison);
    }

    /**
     * Filter the query on the tree_level column
     *
     * Example usage:
     * <code>
     * $query->filterByTreeLevel(1234); // WHERE tree_level = 1234
     * $query->filterByTreeLevel(array(12, 34)); // WHERE tree_level IN (12, 34)
     * $query->filterByTreeLevel(array('min' => 12)); // WHERE tree_level > 12
     * </code>
     *
     * @param     mixed $treeLevel The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByTreeLevel($treeLevel = null, $comparison = null)
    {
        if (is_array($treeLevel)) {
            $useMinMax = false;
            if (isset($treeLevel['min'])) {
                $this->addUsingAlias(ChapterTableMap::COL_TREE_LEVEL, $treeLevel['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($treeLevel['max'])) {
                $this->addUsingAlias(ChapterTableMap::COL_TREE_LEVEL, $treeLevel['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_TREE_LEVEL, $treeLevel, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(ChapterTableMap::COL_CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(ChapterTableMap::COL_CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ChapterTableMap::COL_CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query by a related \Book object
     *
     * @param \Book|ObjectCollection $book The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildChapterQuery The current query, for fluid interface
     */
    public function filterByBook($book, $comparison = null)
    {
        if ($book instanceof \Book) {
            return $this
                ->addUsingAlias(ChapterTableMap::COL_BOOK_ID, $book->getId(), $comparison);
        } elseif ($book instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ChapterTableMap::COL_BOOK_ID, $book->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByBook() only accepts arguments of type \Book or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Book relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function joinBook($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Book');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Book');
        }

        return $this;
    }

    /**
     * Use the Book relation Book object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \BookQuery A secondary query class using the current class as primary query
     */
    public function useBookQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBook($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Book', '\BookQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildChapter $chapter Object to remove from the list of results
     *
     * @return $this|ChildChapterQuery The current query, for fluid interface
     */
    public function prune($chapter = null)
    {
        if ($chapter) {
            $this->addUsingAlias(ChapterTableMap::COL_ID, $chapter->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the chapters table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ChapterTableMap::clearInstancePool();
            ChapterTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ChapterTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ChapterTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ChapterTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // nested_set behavior

    /**
     * Filter the query to restrict the result to root objects
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function treeRoots()
    {
        return $this->addUsingAlias(ChildChapter::LEFT_COL, 1, Criteria::EQUAL);
    }

    /**
     * Returns the objects in a certain tree, from the tree scope
     *
     * @param     int $scope        Scope to determine which objects node to return
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function inTree($scope = null)
    {
        return $this->addUsingAlias(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);
    }

    /**
     * Filter the query to restrict the result to descendants of an object
     *
     * @param     ChildChapter $chapter The object to use for descendant search
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function descendantsOf(ChildChapter $chapter)
    {
        return $this
            ->inTree($chapter->getScopeValue())
            ->addUsingAlias(ChildChapter::LEFT_COL, $chapter->getLeftValue(), Criteria::GREATER_THAN)
            ->addUsingAlias(ChildChapter::LEFT_COL, $chapter->getRightValue(), Criteria::LESS_THAN);
    }

    /**
     * Filter the query to restrict the result to the branch of an object.
     * Same as descendantsOf(), except that it includes the object passed as parameter in the result
     *
     * @param     ChildChapter $chapter The object to use for branch search
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function branchOf(ChildChapter $chapter)
    {
        return $this
            ->inTree($chapter->getScopeValue())
            ->addUsingAlias(ChildChapter::LEFT_COL, $chapter->getLeftValue(), Criteria::GREATER_EQUAL)
            ->addUsingAlias(ChildChapter::LEFT_COL, $chapter->getRightValue(), Criteria::LESS_EQUAL);
    }

    /**
     * Filter the query to restrict the result to children of an object
     *
     * @param     ChildChapter $chapter The object to use for child search
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function childrenOf(ChildChapter $chapter)
    {
        return $this
            ->descendantsOf($chapter)
            ->addUsingAlias(ChildChapter::LEVEL_COL, $chapter->getLevel() + 1, Criteria::EQUAL);
    }

    /**
     * Filter the query to restrict the result to siblings of an object.
     * The result does not include the object passed as parameter.
     *
     * @param     ChildChapter $chapter The object to use for sibling search
     * @param      ConnectionInterface $con Connection to use.
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function siblingsOf(ChildChapter $chapter, ConnectionInterface $con = null)
    {
        if ($chapter->isRoot()) {
            return $this->
                add(ChildChapter::LEVEL_COL, '1<>1', Criteria::CUSTOM);
        } else {
            return $this
                ->childrenOf($chapter->getParent($con))
                ->prune($chapter);
        }
    }

    /**
     * Filter the query to restrict the result to ancestors of an object
     *
     * @param     ChildChapter $chapter The object to use for ancestors search
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function ancestorsOf(ChildChapter $chapter)
    {
        return $this
            ->inTree($chapter->getScopeValue())
            ->addUsingAlias(ChildChapter::LEFT_COL, $chapter->getLeftValue(), Criteria::LESS_THAN)
            ->addUsingAlias(ChildChapter::RIGHT_COL, $chapter->getRightValue(), Criteria::GREATER_THAN);
    }

    /**
     * Filter the query to restrict the result to roots of an object.
     * Same as ancestorsOf(), except that it includes the object passed as parameter in the result
     *
     * @param     ChildChapter $chapter The object to use for roots search
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function rootsOf(ChildChapter $chapter)
    {
        return $this
            ->inTree($chapter->getScopeValue())
            ->addUsingAlias(ChildChapter::LEFT_COL, $chapter->getLeftValue(), Criteria::LESS_EQUAL)
            ->addUsingAlias(ChildChapter::RIGHT_COL, $chapter->getRightValue(), Criteria::GREATER_EQUAL);
    }

    /**
     * Order the result by branch, i.e. natural tree order
     *
     * @param     bool $reverse if true, reverses the order
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function orderByBranch($reverse = false)
    {
        if ($reverse) {
            return $this
                ->addDescendingOrderByColumn(ChildChapter::LEFT_COL);
        } else {
            return $this
                ->addAscendingOrderByColumn(ChildChapter::LEFT_COL);
        }
    }

    /**
     * Order the result by level, the closer to the root first
     *
     * @param     bool $reverse if true, reverses the order
     *
     * @return    $this|ChildChapterQuery The current query, for fluid interface
     */
    public function orderByLevel($reverse = false)
    {
        if ($reverse) {
            return $this
                ->addDescendingOrderByColumn(ChildChapter::LEVEL_COL)
                ->addDescendingOrderByColumn(ChildChapter::LEFT_COL);
        } else {
            return $this
                ->addAscendingOrderByColumn(ChildChapter::LEVEL_COL)
                ->addAscendingOrderByColumn(ChildChapter::LEFT_COL);
        }
    }

    /**
     * Returns a root node for the tree
     *
     * @param      int $scope        Scope to determine which root node to return
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     ChildChapter The tree root object
     */
    public function findRoot($scope = null, ConnectionInterface $con = null)
    {
        return $this
            ->addUsingAlias(ChildChapter::LEFT_COL, 1, Criteria::EQUAL)
            ->inTree($scope)
            ->findOne($con);
    }

    /**
     * Returns the root objects for all trees.
     *
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return    ChildChapter[]|ObjectCollection|mixed the list of results, formatted by the current formatter
     */
    public function findRoots(ConnectionInterface $con = null)
    {
        return $this
            ->treeRoots()
            ->find($con);
    }

    /**
     * Returns a tree of objects
     *
     * @param      int $scope        Scope to determine which tree node to return
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     ChildChapter[]|ObjectCollection|mixed the list of results, formatted by the current formatter
     */
    public function findTree($scope = null, ConnectionInterface $con = null)
    {
        return $this
            ->inTree($scope)
            ->orderByBranch()
            ->find($con);
    }

    /**
     * Returns the root nodes for the tree
     *
     * @param      Criteria $criteria    Optional Criteria to filter the query
     * @param      ConnectionInterface $con    Connection to use.
     * @return     ChildChapter[]|ObjectCollection|mixed the list of results, formatted by the current formatter
     */
    static public function retrieveRoots(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if (null === $criteria) {
            $criteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        }
        $criteria->add(ChildChapter::LEFT_COL, 1, Criteria::EQUAL);

        return ChildChapterQuery::create(null, $criteria)->find($con);
    }

    /**
     * Returns the root node for a given scope
     *
     * @param      int $scope        Scope to determine which root node to return
     * @param      ConnectionInterface $con    Connection to use.
     * @return     ChildChapter            Propel object for root node
     */
    static public function retrieveRoot($scope = null, ConnectionInterface $con = null)
    {
        $c = new Criteria(ChapterTableMap::DATABASE_NAME);
        $c->add(ChildChapter::LEFT_COL, 1, Criteria::EQUAL);
        $c->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);

        return ChildChapterQuery::create(null, $c)->findOne($con);
    }

    /**
     * Returns the whole tree node for a given scope
     *
     * @param      int $scope        Scope to determine which root node to return
     * @param      Criteria $criteria    Optional Criteria to filter the query
     * @param      ConnectionInterface $con    Connection to use.
     * @return     ChildChapter[]|ObjectCollection|mixed the list of results, formatted by the current formatter
     */
    static public function retrieveTree($scope = null, Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if (null === $criteria) {
            $criteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        }
        $criteria->addAscendingOrderByColumn(ChildChapter::LEFT_COL);
        $criteria->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);

        return ChildChapterQuery::create(null, $criteria)->find($con);
    }

    /**
     * Tests if node is valid
     *
     * @param      ChildChapter $node    Propel object for src node
     * @return     bool
     */
    static public function isValid(ChildChapter $node = null)
    {
        if (is_object($node) && $node->getRightValue() > $node->getLeftValue()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete an entire tree
     *
     * @param      int $scope        Scope to determine which tree to delete
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     int  The number of deleted nodes
     */
    static public function deleteTree($scope = null, ConnectionInterface $con = null)
    {
        $c = new Criteria(ChapterTableMap::DATABASE_NAME);
        $c->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);

        return ChapterTableMap::doDelete($c, $con);
    }

    /**
     * Adds $delta to all L and R values that are >= $first and <= $last.
     * '$delta' can also be negative.
     *
     * @param int $delta               Value to be shifted by, can be negative
     * @param int $first               First node to be shifted
     * @param int $last                Last node to be shifted (optional)
     * @param int $scope               Scope to use for the shift
     * @param ConnectionInterface $con Connection to use.
     */
    static public function shiftRLValues($delta, $first, $last = null, $scope = null, ConnectionInterface $con = null)
    {
        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        // Shift left column values
        $whereCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $criterion = $whereCriteria->getNewCriterion(ChildChapter::LEFT_COL, $first, Criteria::GREATER_EQUAL);
        if (null !== $last) {
            $criterion->addAnd($whereCriteria->getNewCriterion(ChildChapter::LEFT_COL, $last, Criteria::LESS_EQUAL));
        }
        $whereCriteria->add($criterion);
        $whereCriteria->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);

        $valuesCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $valuesCriteria->add(ChildChapter::LEFT_COL, array('raw' => ChildChapter::LEFT_COL . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

        $whereCriteria->doUpdate($valuesCriteria, $con);

        // Shift right column values
        $whereCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $criterion = $whereCriteria->getNewCriterion(ChildChapter::RIGHT_COL, $first, Criteria::GREATER_EQUAL);
        if (null !== $last) {
            $criterion->addAnd($whereCriteria->getNewCriterion(ChildChapter::RIGHT_COL, $last, Criteria::LESS_EQUAL));
        }
        $whereCriteria->add($criterion);
        $whereCriteria->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);

        $valuesCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $valuesCriteria->add(ChildChapter::RIGHT_COL, array('raw' => ChildChapter::RIGHT_COL . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

        $whereCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Adds $delta to level for nodes having left value >= $first and right value <= $last.
     * '$delta' can also be negative.
     *
     * @param      int $delta        Value to be shifted by, can be negative
     * @param      int $first        First node to be shifted
     * @param      int $last            Last node to be shifted
     * @param      int $scope        Scope to use for the shift
     * @param      ConnectionInterface $con        Connection to use.
     */
    static public function shiftLevel($delta, $first, $last, $scope = null, ConnectionInterface $con = null)
    {
        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        $whereCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $whereCriteria->add(ChildChapter::LEFT_COL, $first, Criteria::GREATER_EQUAL);
        $whereCriteria->add(ChildChapter::RIGHT_COL, $last, Criteria::LESS_EQUAL);
        $whereCriteria->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);

        $valuesCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $valuesCriteria->add(ChildChapter::LEVEL_COL, array('raw' => ChildChapter::LEVEL_COL . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

        $whereCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Reload all already loaded nodes to sync them with updated db
     *
     * @param      ChildChapter $prune        Object to prune from the update
     * @param      ConnectionInterface $con        Connection to use.
     */
    static public function updateLoadedNodes($prune = null, ConnectionInterface $con = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            $keys = array();
            /** @var $obj ChildChapter */
            foreach (ChapterTableMap::$instances as $obj) {
                if (!$prune || !$prune->equals($obj)) {
                    $keys[] = $obj->getPrimaryKey();
                }
            }

            if (!empty($keys)) {
                // We don't need to alter the object instance pool; we're just modifying these ones
                // already in the pool.
                $criteria = new Criteria(ChapterTableMap::DATABASE_NAME);
                $criteria->add(ChapterTableMap::COL_ID, $keys, Criteria::IN);
                $dataFetcher = ChildChapterQuery::create(null, $criteria)->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
                while ($row = $dataFetcher->fetch()) {
                    $key = ChapterTableMap::getPrimaryKeyHashFromRow($row, 0);
                    /** @var $object ChildChapter */
                    if (null !== ($object = ChapterTableMap::getInstanceFromPool($key))) {
                        $object->setScopeValue($row[1]);
                        $object->setLeftValue($row[6]);
                        $object->setRightValue($row[7]);
                        $object->setLevel($row[8]);
                        $object->clearNestedSetChildren();
                    }
                }
                $dataFetcher->close();
            }
        }
    }

    /**
     * Update the tree to allow insertion of a leaf at the specified position
     *
     * @param      int $left    left column value
     * @param      integer $scope    scope column value
     * @param      mixed $prune    Object to prune from the shift
     * @param      ConnectionInterface $con    Connection to use.
     */
    static public function makeRoomForLeaf($left, $scope, $prune = null, ConnectionInterface $con = null)
    {
        // Update database nodes
        ChildChapterQuery::shiftRLValues(2, $left, null, $scope, $con);

        // Update all loaded nodes
        ChildChapterQuery::updateLoadedNodes($prune, $con);
    }

    /**
     * Update the tree to allow insertion of a leaf at the specified position
     *
     * @param      integer $scope    scope column value
     * @param      ConnectionInterface $con    Connection to use.
     */
    static public function fixLevels($scope, ConnectionInterface $con = null)
    {
        $c = new Criteria();
        $c->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);
        $c->addAscendingOrderByColumn(ChildChapter::LEFT_COL);
        $dataFetcher = ChildChapterQuery::create(null, $c)->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);

        // set the class once to avoid overhead in the loop
        $cls = ChapterTableMap::getOMClass(false);
        $level = null;
        // iterate over the statement
        while ($row = $dataFetcher->fetch()) {

            // hydrate object
            $key = ChapterTableMap::getPrimaryKeyHashFromRow($row, 0);
            /** @var $obj ChildChapter */
            if (null === ($obj = ChapterTableMap::getInstanceFromPool($key))) {
                $obj = new $cls();
                $obj->hydrate($row);
                ChapterTableMap::addInstanceToPool($obj, $key);
            }

            // compute level
            // Algorithm shamelessly stolen from sfPropelActAsNestedSetBehaviorPlugin
            // Probably authored by Tristan Rivoallan
            if ($level === null) {
                $level = 0;
                $i = 0;
                $prev = array($obj->getRightValue());
            } else {
                while ($obj->getRightValue() > $prev[$i]) {
                    $i--;
                }
                $level = ++$i;
                $prev[$i] = $obj->getRightValue();
            }

            // update level in node if necessary
            if ($obj->getLevel() !== $level) {
                $obj->setLevel($level);
                $obj->save($con);
            }
        }
        $dataFetcher->close();
    }

    /**
     * Updates all scope values for items that has negative left (<=0) values.
     *
     * @param      mixed     $scope
     * @param      ConnectionInterface $con  Connection to use.
     */
    public static function setNegativeScope($scope, ConnectionInterface $con = null)
    {
        //adjust scope value to $scope
        $whereCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $whereCriteria->add(ChildChapter::LEFT_COL, 0, Criteria::LESS_EQUAL);

        $valuesCriteria = new Criteria(ChapterTableMap::DATABASE_NAME);
        $valuesCriteria->add(ChildChapter::SCOPE_COL, $scope, Criteria::EQUAL);

        $whereCriteria->doUpdate($valuesCriteria, $con);
    }

    // timestampable behavior

    /**
     * Order by create date desc
     *
     * @return     $this|ChildChapterQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(ChapterTableMap::COL_CREATED_AT);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     $this|ChildChapterQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(ChapterTableMap::COL_CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date asc
     *
     * @return     $this|ChildChapterQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(ChapterTableMap::COL_CREATED_AT);
    }

} // ChapterQuery
