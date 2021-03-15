<?php

namespace Base;

use \Book as ChildBook;
use \BookQuery as ChildBookQuery;
use \Exception;
use \PDO;
use Map\BookTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'books' table.
 *
 *
 *
 * @method     ChildBookQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildBookQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildBookQuery orderBySubtitle($order = Criteria::ASC) Order by the subtitle column
 * @method     ChildBookQuery orderBySlug($order = Criteria::ASC) Order by the slug column
 * @method     ChildBookQuery orderByAuthor($order = Criteria::ASC) Order by the author column
 * @method     ChildBookQuery orderByDedication($order = Criteria::ASC) Order by the dedication column
 * @method     ChildBookQuery orderByLanguageId($order = Criteria::ASC) Order by the language_id column
 * @method     ChildBookQuery orderByPublisher($order = Criteria::ASC) Order by the publisher column
 * @method     ChildBookQuery orderByYear($order = Criteria::ASC) Order by the year column
 * @method     ChildBookQuery orderByIsbn($order = Criteria::ASC) Order by the isbn column
 * @method     ChildBookQuery orderByExtraInfo($order = Criteria::ASC) Order by the extra_info column
 * @method     ChildBookQuery orderByIncludeFont($order = Criteria::ASC) Order by the include_font column
 * @method     ChildBookQuery orderByCoverImage($order = Criteria::ASC) Order by the cover_image column
 * @method     ChildBookQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildBookQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildBookQuery groupById() Group by the id column
 * @method     ChildBookQuery groupByTitle() Group by the title column
 * @method     ChildBookQuery groupBySubtitle() Group by the subtitle column
 * @method     ChildBookQuery groupBySlug() Group by the slug column
 * @method     ChildBookQuery groupByAuthor() Group by the author column
 * @method     ChildBookQuery groupByDedication() Group by the dedication column
 * @method     ChildBookQuery groupByLanguageId() Group by the language_id column
 * @method     ChildBookQuery groupByPublisher() Group by the publisher column
 * @method     ChildBookQuery groupByYear() Group by the year column
 * @method     ChildBookQuery groupByIsbn() Group by the isbn column
 * @method     ChildBookQuery groupByExtraInfo() Group by the extra_info column
 * @method     ChildBookQuery groupByIncludeFont() Group by the include_font column
 * @method     ChildBookQuery groupByCoverImage() Group by the cover_image column
 * @method     ChildBookQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildBookQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildBookQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildBookQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildBookQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildBookQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildBookQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildBookQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildBookQuery leftJoinLanguage($relationAlias = null) Adds a LEFT JOIN clause to the query using the Language relation
 * @method     ChildBookQuery rightJoinLanguage($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Language relation
 * @method     ChildBookQuery innerJoinLanguage($relationAlias = null) Adds a INNER JOIN clause to the query using the Language relation
 *
 * @method     ChildBookQuery joinWithLanguage($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Language relation
 *
 * @method     ChildBookQuery leftJoinWithLanguage() Adds a LEFT JOIN clause and with to the query using the Language relation
 * @method     ChildBookQuery rightJoinWithLanguage() Adds a RIGHT JOIN clause and with to the query using the Language relation
 * @method     ChildBookQuery innerJoinWithLanguage() Adds a INNER JOIN clause and with to the query using the Language relation
 *
 * @method     ChildBookQuery leftJoinChapter($relationAlias = null) Adds a LEFT JOIN clause to the query using the Chapter relation
 * @method     ChildBookQuery rightJoinChapter($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Chapter relation
 * @method     ChildBookQuery innerJoinChapter($relationAlias = null) Adds a INNER JOIN clause to the query using the Chapter relation
 *
 * @method     ChildBookQuery joinWithChapter($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Chapter relation
 *
 * @method     ChildBookQuery leftJoinWithChapter() Adds a LEFT JOIN clause and with to the query using the Chapter relation
 * @method     ChildBookQuery rightJoinWithChapter() Adds a RIGHT JOIN clause and with to the query using the Chapter relation
 * @method     ChildBookQuery innerJoinWithChapter() Adds a INNER JOIN clause and with to the query using the Chapter relation
 *
 * @method     \LanguageQuery|\ChapterQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildBook findOne(ConnectionInterface $con = null) Return the first ChildBook matching the query
 * @method     ChildBook findOneOrCreate(ConnectionInterface $con = null) Return the first ChildBook matching the query, or a new ChildBook object populated from the query conditions when no match is found
 *
 * @method     ChildBook findOneById(int $id) Return the first ChildBook filtered by the id column
 * @method     ChildBook findOneByTitle(string $title) Return the first ChildBook filtered by the title column
 * @method     ChildBook findOneBySubtitle(string $subtitle) Return the first ChildBook filtered by the subtitle column
 * @method     ChildBook findOneBySlug(string $slug) Return the first ChildBook filtered by the slug column
 * @method     ChildBook findOneByAuthor(string $author) Return the first ChildBook filtered by the author column
 * @method     ChildBook findOneByDedication(string $dedication) Return the first ChildBook filtered by the dedication column
 * @method     ChildBook findOneByLanguageId(int $language_id) Return the first ChildBook filtered by the language_id column
 * @method     ChildBook findOneByPublisher(string $publisher) Return the first ChildBook filtered by the publisher column
 * @method     ChildBook findOneByYear(int $year) Return the first ChildBook filtered by the year column
 * @method     ChildBook findOneByIsbn(string $isbn) Return the first ChildBook filtered by the isbn column
 * @method     ChildBook findOneByExtraInfo(string $extra_info) Return the first ChildBook filtered by the extra_info column
 * @method     ChildBook findOneByIncludeFont(boolean $include_font) Return the first ChildBook filtered by the include_font column
 * @method     ChildBook findOneByCoverImage(string $cover_image) Return the first ChildBook filtered by the cover_image column
 * @method     ChildBook findOneByCreatedAt(string $created_at) Return the first ChildBook filtered by the created_at column
 * @method     ChildBook findOneByUpdatedAt(string $updated_at) Return the first ChildBook filtered by the updated_at column *

 * @method     ChildBook requirePk($key, ConnectionInterface $con = null) Return the ChildBook by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOne(ConnectionInterface $con = null) Return the first ChildBook matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBook requireOneById(int $id) Return the first ChildBook filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByTitle(string $title) Return the first ChildBook filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneBySubtitle(string $subtitle) Return the first ChildBook filtered by the subtitle column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneBySlug(string $slug) Return the first ChildBook filtered by the slug column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByAuthor(string $author) Return the first ChildBook filtered by the author column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByDedication(string $dedication) Return the first ChildBook filtered by the dedication column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByLanguageId(int $language_id) Return the first ChildBook filtered by the language_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByPublisher(string $publisher) Return the first ChildBook filtered by the publisher column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByYear(int $year) Return the first ChildBook filtered by the year column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByIsbn(string $isbn) Return the first ChildBook filtered by the isbn column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByExtraInfo(string $extra_info) Return the first ChildBook filtered by the extra_info column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByIncludeFont(boolean $include_font) Return the first ChildBook filtered by the include_font column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByCoverImage(string $cover_image) Return the first ChildBook filtered by the cover_image column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByCreatedAt(string $created_at) Return the first ChildBook filtered by the created_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildBook requireOneByUpdatedAt(string $updated_at) Return the first ChildBook filtered by the updated_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildBook[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildBook objects based on current ModelCriteria
 * @method     ChildBook[]|ObjectCollection findById(int $id) Return ChildBook objects filtered by the id column
 * @method     ChildBook[]|ObjectCollection findByTitle(string $title) Return ChildBook objects filtered by the title column
 * @method     ChildBook[]|ObjectCollection findBySubtitle(string $subtitle) Return ChildBook objects filtered by the subtitle column
 * @method     ChildBook[]|ObjectCollection findBySlug(string $slug) Return ChildBook objects filtered by the slug column
 * @method     ChildBook[]|ObjectCollection findByAuthor(string $author) Return ChildBook objects filtered by the author column
 * @method     ChildBook[]|ObjectCollection findByDedication(string $dedication) Return ChildBook objects filtered by the dedication column
 * @method     ChildBook[]|ObjectCollection findByLanguageId(int $language_id) Return ChildBook objects filtered by the language_id column
 * @method     ChildBook[]|ObjectCollection findByPublisher(string $publisher) Return ChildBook objects filtered by the publisher column
 * @method     ChildBook[]|ObjectCollection findByYear(int $year) Return ChildBook objects filtered by the year column
 * @method     ChildBook[]|ObjectCollection findByIsbn(string $isbn) Return ChildBook objects filtered by the isbn column
 * @method     ChildBook[]|ObjectCollection findByExtraInfo(string $extra_info) Return ChildBook objects filtered by the extra_info column
 * @method     ChildBook[]|ObjectCollection findByIncludeFont(boolean $include_font) Return ChildBook objects filtered by the include_font column
 * @method     ChildBook[]|ObjectCollection findByCoverImage(string $cover_image) Return ChildBook objects filtered by the cover_image column
 * @method     ChildBook[]|ObjectCollection findByCreatedAt(string $created_at) Return ChildBook objects filtered by the created_at column
 * @method     ChildBook[]|ObjectCollection findByUpdatedAt(string $updated_at) Return ChildBook objects filtered by the updated_at column
 * @method     ChildBook[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class BookQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Base\BookQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'html2epub', $modelName = '\\Book', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildBookQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildBookQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildBookQuery) {
            return $criteria;
        }
        $query = new ChildBookQuery();
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
     * @return ChildBook|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(BookTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = BookTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildBook A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, title, subtitle, slug, author, dedication, language_id, publisher, year, isbn, extra_info, include_font, cover_image, created_at, updated_at FROM books WHERE id = :p0';
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
            /** @var ChildBook $obj */
            $obj = new ChildBook();
            $obj->hydrate($row);
            BookTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildBook|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(BookTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(BookTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(BookTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(BookTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_ID, $id, $comparison);
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
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByTitle($title = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($title)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_TITLE, $title, $comparison);
    }

    /**
     * Filter the query on the subtitle column
     *
     * Example usage:
     * <code>
     * $query->filterBySubtitle('fooValue');   // WHERE subtitle = 'fooValue'
     * $query->filterBySubtitle('%fooValue%', Criteria::LIKE); // WHERE subtitle LIKE '%fooValue%'
     * </code>
     *
     * @param     string $subtitle The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterBySubtitle($subtitle = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($subtitle)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_SUBTITLE, $subtitle, $comparison);
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
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterBySlug($slug = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($slug)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_SLUG, $slug, $comparison);
    }

    /**
     * Filter the query on the author column
     *
     * Example usage:
     * <code>
     * $query->filterByAuthor('fooValue');   // WHERE author = 'fooValue'
     * $query->filterByAuthor('%fooValue%', Criteria::LIKE); // WHERE author LIKE '%fooValue%'
     * </code>
     *
     * @param     string $author The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByAuthor($author = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($author)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_AUTHOR, $author, $comparison);
    }

    /**
     * Filter the query on the dedication column
     *
     * Example usage:
     * <code>
     * $query->filterByDedication('fooValue');   // WHERE dedication = 'fooValue'
     * $query->filterByDedication('%fooValue%', Criteria::LIKE); // WHERE dedication LIKE '%fooValue%'
     * </code>
     *
     * @param     string $dedication The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByDedication($dedication = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($dedication)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_DEDICATION, $dedication, $comparison);
    }

    /**
     * Filter the query on the language_id column
     *
     * Example usage:
     * <code>
     * $query->filterByLanguageId(1234); // WHERE language_id = 1234
     * $query->filterByLanguageId(array(12, 34)); // WHERE language_id IN (12, 34)
     * $query->filterByLanguageId(array('min' => 12)); // WHERE language_id > 12
     * </code>
     *
     * @see       filterByLanguage()
     *
     * @param     mixed $languageId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByLanguageId($languageId = null, $comparison = null)
    {
        if (is_array($languageId)) {
            $useMinMax = false;
            if (isset($languageId['min'])) {
                $this->addUsingAlias(BookTableMap::COL_LANGUAGE_ID, $languageId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($languageId['max'])) {
                $this->addUsingAlias(BookTableMap::COL_LANGUAGE_ID, $languageId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_LANGUAGE_ID, $languageId, $comparison);
    }

    /**
     * Filter the query on the publisher column
     *
     * Example usage:
     * <code>
     * $query->filterByPublisher('fooValue');   // WHERE publisher = 'fooValue'
     * $query->filterByPublisher('%fooValue%', Criteria::LIKE); // WHERE publisher LIKE '%fooValue%'
     * </code>
     *
     * @param     string $publisher The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByPublisher($publisher = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($publisher)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_PUBLISHER, $publisher, $comparison);
    }

    /**
     * Filter the query on the year column
     *
     * Example usage:
     * <code>
     * $query->filterByYear(1234); // WHERE year = 1234
     * $query->filterByYear(array(12, 34)); // WHERE year IN (12, 34)
     * $query->filterByYear(array('min' => 12)); // WHERE year > 12
     * </code>
     *
     * @param     mixed $year The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByYear($year = null, $comparison = null)
    {
        if (is_array($year)) {
            $useMinMax = false;
            if (isset($year['min'])) {
                $this->addUsingAlias(BookTableMap::COL_YEAR, $year['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($year['max'])) {
                $this->addUsingAlias(BookTableMap::COL_YEAR, $year['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_YEAR, $year, $comparison);
    }

    /**
     * Filter the query on the isbn column
     *
     * Example usage:
     * <code>
     * $query->filterByIsbn('fooValue');   // WHERE isbn = 'fooValue'
     * $query->filterByIsbn('%fooValue%', Criteria::LIKE); // WHERE isbn LIKE '%fooValue%'
     * </code>
     *
     * @param     string $isbn The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByIsbn($isbn = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($isbn)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_ISBN, $isbn, $comparison);
    }

    /**
     * Filter the query on the extra_info column
     *
     * Example usage:
     * <code>
     * $query->filterByExtraInfo('fooValue');   // WHERE extra_info = 'fooValue'
     * $query->filterByExtraInfo('%fooValue%', Criteria::LIKE); // WHERE extra_info LIKE '%fooValue%'
     * </code>
     *
     * @param     string $extraInfo The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByExtraInfo($extraInfo = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($extraInfo)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_EXTRA_INFO, $extraInfo, $comparison);
    }

    /**
     * Filter the query on the include_font column
     *
     * Example usage:
     * <code>
     * $query->filterByIncludeFont(true); // WHERE include_font = true
     * $query->filterByIncludeFont('yes'); // WHERE include_font = true
     * </code>
     *
     * @param     boolean|string $includeFont The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByIncludeFont($includeFont = null, $comparison = null)
    {
        if (is_string($includeFont)) {
            $includeFont = in_array(strtolower($includeFont), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(BookTableMap::COL_INCLUDE_FONT, $includeFont, $comparison);
    }

    /**
     * Filter the query on the cover_image column
     *
     * Example usage:
     * <code>
     * $query->filterByCoverImage('fooValue');   // WHERE cover_image = 'fooValue'
     * $query->filterByCoverImage('%fooValue%', Criteria::LIKE); // WHERE cover_image LIKE '%fooValue%'
     * </code>
     *
     * @param     string $coverImage The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByCoverImage($coverImage = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($coverImage)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_COVER_IMAGE, $coverImage, $comparison);
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
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(BookTableMap::COL_CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(BookTableMap::COL_CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_CREATED_AT, $createdAt, $comparison);
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
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(BookTableMap::COL_UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(BookTableMap::COL_UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BookTableMap::COL_UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \Language object
     *
     * @param \Language|ObjectCollection $language The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildBookQuery The current query, for fluid interface
     */
    public function filterByLanguage($language, $comparison = null)
    {
        if ($language instanceof \Language) {
            return $this
                ->addUsingAlias(BookTableMap::COL_LANGUAGE_ID, $language->getId(), $comparison);
        } elseif ($language instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BookTableMap::COL_LANGUAGE_ID, $language->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByLanguage() only accepts arguments of type \Language or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Language relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function joinLanguage($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Language');

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
            $this->addJoinObject($join, 'Language');
        }

        return $this;
    }

    /**
     * Use the Language relation Language object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \LanguageQuery A secondary query class using the current class as primary query
     */
    public function useLanguageQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinLanguage($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Language', '\LanguageQuery');
    }

    /**
     * Filter the query by a related \Chapter object
     *
     * @param \Chapter|ObjectCollection $chapter the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildBookQuery The current query, for fluid interface
     */
    public function filterByChapter($chapter, $comparison = null)
    {
        if ($chapter instanceof \Chapter) {
            return $this
                ->addUsingAlias(BookTableMap::COL_ID, $chapter->getBookId(), $comparison);
        } elseif ($chapter instanceof ObjectCollection) {
            return $this
                ->useChapterQuery()
                ->filterByPrimaryKeys($chapter->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByChapter() only accepts arguments of type \Chapter or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Chapter relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function joinChapter($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Chapter');

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
            $this->addJoinObject($join, 'Chapter');
        }

        return $this;
    }

    /**
     * Use the Chapter relation Chapter object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \ChapterQuery A secondary query class using the current class as primary query
     */
    public function useChapterQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinChapter($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Chapter', '\ChapterQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildBook $book Object to remove from the list of results
     *
     * @return $this|ChildBookQuery The current query, for fluid interface
     */
    public function prune($book = null)
    {
        if ($book) {
            $this->addUsingAlias(BookTableMap::COL_ID, $book->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the books table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BookTableMap::clearInstancePool();
            BookTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(BookTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            BookTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            BookTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     $this|ChildBookQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(BookTableMap::COL_UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     $this|ChildBookQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(BookTableMap::COL_UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     $this|ChildBookQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(BookTableMap::COL_UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     $this|ChildBookQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(BookTableMap::COL_CREATED_AT);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     $this|ChildBookQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(BookTableMap::COL_CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date asc
     *
     * @return     $this|ChildBookQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(BookTableMap::COL_CREATED_AT);
    }

} // BookQuery
