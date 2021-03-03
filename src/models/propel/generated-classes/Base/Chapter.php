<?php

namespace Base;

use \Book as ChildBook;
use \BookQuery as ChildBookQuery;
use \Chapter as ChildChapter;
use \ChapterQuery as ChildChapterQuery;
use \DateTime;
use \Exception;
use \PDO;
use Map\ChapterTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\ActiveRecord\NestedSetRecursiveIterator;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use Propel\Runtime\Validator\Constraints\Unique;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Base class that represents a row from the 'chapters' table.
 *
 *
 *
 * @package    propel.generator..Base
 */
abstract class Chapter implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Map\\ChapterTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected $id;

    /**
     * The value for the book_id field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $book_id;

    /**
     * The value for the title field.
     *
     * @var        string
     */
    protected $title;

    /**
     * The value for the slug field.
     *
     * @var        string
     */
    protected $slug;

    /**
     * The value for the body field.
     *
     * @var        string
     */
    protected $body;

    /**
     * The value for the updated_at field.
     *
     * @var        DateTime
     */
    protected $updated_at;

    /**
     * The value for the tree_left field.
     *
     * @var        int
     */
    protected $tree_left;

    /**
     * The value for the tree_right field.
     *
     * @var        int
     */
    protected $tree_right;

    /**
     * The value for the tree_level field.
     *
     * @var        int
     */
    protected $tree_level;

    /**
     * The value for the created_at field.
     *
     * @var        DateTime
     */
    protected $created_at;

    /**
     * @var        ChildBook
     */
    protected $aBook;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    // nested_set behavior

    /**
     * Queries to be executed in the save transaction
     * @var        array
     */
    protected $nestedSetQueries = array();

    /**
     * Internal cache for children nodes
     * @var        null|ObjectCollection
     */
    protected $collNestedSetChildren = null;

    /**
     * Internal cache for parent node
     * @var        null|ChildChapter
     */
    protected $aNestedSetParent = null;

    /**
     * Left column for the set
     */
    const LEFT_COL = 'chapters.tree_left';

    /**
     * Right column for the set
     */
    const RIGHT_COL = 'chapters.tree_right';

    /**
     * Level column for the set
     */
    const LEVEL_COL = 'chapters.tree_level';

    /**
     * Scope column for the set
     */
    const SCOPE_COL = 'chapters.book_id';

    // validate behavior

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * ConstraintViolationList object
     *
     * @see     http://api.symfony.com/2.0/Symfony/Component/Validator/ConstraintViolationList.html
     * @var     ConstraintViolationList
     */
    protected $validationFailures;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->book_id = 0;
    }

    /**
     * Initializes internal state of Base\Chapter object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>Chapter</code> instance.  If
     * <code>obj</code> is an instance of <code>Chapter</code>, delegates to
     * <code>equals(Chapter)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|Chapter The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [book_id] column value.
     *
     * @return int
     */
    public function getBookId()
    {
        return $this->book_id;
    }

    /**
     * Get the [title] column value.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the [slug] column value.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get the [body] column value.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string|null $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Get the [tree_left] column value.
     *
     * @return int
     */
    public function getTreeLeft()
    {
        return $this->tree_left;
    }

    /**
     * Get the [tree_right] column value.
     *
     * @return int
     */
    public function getTreeRight()
    {
        return $this->tree_right;
    }

    /**
     * Get the [tree_level] column value.
     *
     * @return int
     */
    public function getTreeLevel()
    {
        return $this->tree_level;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string|null $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at instanceof \DateTimeInterface ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[ChapterTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [book_id] column.
     *
     * @param int $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setBookId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->book_id !== $v) {
            $this->book_id = $v;
            $this->modifiedColumns[ChapterTableMap::COL_BOOK_ID] = true;
        }

        if ($this->aBook !== null && $this->aBook->getId() !== $v) {
            $this->aBook = null;
        }

        return $this;
    } // setBookId()

    /**
     * Set the value of [title] column.
     *
     * @param string $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setTitle($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->title !== $v) {
            $this->title = $v;
            $this->modifiedColumns[ChapterTableMap::COL_TITLE] = true;
        }

        return $this;
    } // setTitle()

    /**
     * Set the value of [slug] column.
     *
     * @param string $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setSlug($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->slug !== $v) {
            $this->slug = $v;
            $this->modifiedColumns[ChapterTableMap::COL_SLUG] = true;
        }

        return $this;
    } // setSlug()

    /**
     * Set the value of [body] column.
     *
     * @param string $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setBody($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->body !== $v) {
            $this->body = $v;
            $this->modifiedColumns[ChapterTableMap::COL_BODY] = true;
        }

        return $this;
    } // setBody()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($this->updated_at === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->updated_at->format("Y-m-d H:i:s.u")) {
                $this->updated_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[ChapterTableMap::COL_UPDATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setUpdatedAt()

    /**
     * Set the value of [tree_left] column.
     *
     * @param int $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setTreeLeft($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->tree_left !== $v) {
            $this->tree_left = $v;
            $this->modifiedColumns[ChapterTableMap::COL_TREE_LEFT] = true;
        }

        return $this;
    } // setTreeLeft()

    /**
     * Set the value of [tree_right] column.
     *
     * @param int $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setTreeRight($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->tree_right !== $v) {
            $this->tree_right = $v;
            $this->modifiedColumns[ChapterTableMap::COL_TREE_RIGHT] = true;
        }

        return $this;
    } // setTreeRight()

    /**
     * Set the value of [tree_level] column.
     *
     * @param int $v new value
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setTreeLevel($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->tree_level !== $v) {
            $this->tree_level = $v;
            $this->modifiedColumns[ChapterTableMap::COL_TREE_LEVEL] = true;
        }

        return $this;
    } // setTreeLevel()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\Chapter The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($this->created_at === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->created_at->format("Y-m-d H:i:s.u")) {
                $this->created_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[ChapterTableMap::COL_CREATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setCreatedAt()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->book_id !== 0) {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ChapterTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ChapterTableMap::translateFieldName('BookId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->book_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ChapterTableMap::translateFieldName('Title', TableMap::TYPE_PHPNAME, $indexType)];
            $this->title = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ChapterTableMap::translateFieldName('Slug', TableMap::TYPE_PHPNAME, $indexType)];
            $this->slug = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ChapterTableMap::translateFieldName('Body', TableMap::TYPE_PHPNAME, $indexType)];
            $this->body = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : ChapterTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : ChapterTableMap::translateFieldName('TreeLeft', TableMap::TYPE_PHPNAME, $indexType)];
            $this->tree_left = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : ChapterTableMap::translateFieldName('TreeRight', TableMap::TYPE_PHPNAME, $indexType)];
            $this->tree_right = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : ChapterTableMap::translateFieldName('TreeLevel', TableMap::TYPE_PHPNAME, $indexType)];
            $this->tree_level = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : ChapterTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 10; // 10 = ChapterTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Chapter'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aBook !== null && $this->book_id !== $this->aBook->getId()) {
            $this->aBook = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ChapterTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildChapterQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aBook = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Chapter::setDeleted()
     * @see Chapter::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildChapterQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            // nested_set behavior
            if ($this->isRoot()) {
                throw new PropelException('Deletion of a root node is disabled for nested sets. Use ChildChapterQuery::deleteTree($scope) instead to delete an entire tree');
            }

            if ($this->isInTree()) {
                $this->deleteDescendants($con);
            }

            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // nested_set behavior
                if ($this->isInTree()) {
                    // fill up the room that was used by the node
                    ChildChapterQuery::shiftRLValues(-2, $this->getRightValue() + 1, null, $this->getScopeValue(), $con);
                }

                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            // nested_set behavior
            if ($this->isNew() && $this->isRoot()) {
                // check if no other root exist in, the tree
                $rootExists = ChildChapterQuery::create()
                    ->addUsingAlias(ChildChapter::LEFT_COL, 1, Criteria::EQUAL)
                    ->addUsingAlias(ChildChapter::SCOPE_COL, $this->getScopeValue(), Criteria::EQUAL)
                    ->exists($con);
                if ($rootExists) {
                        throw new PropelException(sprintf('A root node already exists in this tree with scope "%s".', $this->getScopeValue()));
                }
            }
            $this->processNestedSetQueries($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                $time = time();
                $highPrecision = \Propel\Runtime\Util\PropelDateTime::createHighPrecision();
                if (!$this->isColumnModified(ChapterTableMap::COL_CREATED_AT)) {
                    $this->setCreatedAt($highPrecision);
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                ChapterTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aBook !== null) {
                if ($this->aBook->isModified() || $this->aBook->isNew()) {
                    $affectedRows += $this->aBook->save($con);
                }
                $this->setBook($this->aBook);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[ChapterTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ChapterTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ChapterTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_BOOK_ID)) {
            $modifiedColumns[':p' . $index++]  = 'book_id';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TITLE)) {
            $modifiedColumns[':p' . $index++]  = 'title';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_SLUG)) {
            $modifiedColumns[':p' . $index++]  = 'slug';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_BODY)) {
            $modifiedColumns[':p' . $index++]  = 'body';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'updated_at';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TREE_LEFT)) {
            $modifiedColumns[':p' . $index++]  = 'tree_left';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TREE_RIGHT)) {
            $modifiedColumns[':p' . $index++]  = 'tree_right';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TREE_LEVEL)) {
            $modifiedColumns[':p' . $index++]  = 'tree_level';
        }
        if ($this->isColumnModified(ChapterTableMap::COL_CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'created_at';
        }

        $sql = sprintf(
            'INSERT INTO chapters (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'id':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'book_id':
                        $stmt->bindValue($identifier, $this->book_id, PDO::PARAM_INT);
                        break;
                    case 'title':
                        $stmt->bindValue($identifier, $this->title, PDO::PARAM_STR);
                        break;
                    case 'slug':
                        $stmt->bindValue($identifier, $this->slug, PDO::PARAM_STR);
                        break;
                    case 'body':
                        $stmt->bindValue($identifier, $this->body, PDO::PARAM_STR);
                        break;
                    case 'updated_at':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                    case 'tree_left':
                        $stmt->bindValue($identifier, $this->tree_left, PDO::PARAM_INT);
                        break;
                    case 'tree_right':
                        $stmt->bindValue($identifier, $this->tree_right, PDO::PARAM_INT);
                        break;
                    case 'tree_level':
                        $stmt->bindValue($identifier, $this->tree_level, PDO::PARAM_INT);
                        break;
                    case 'created_at':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ChapterTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getBookId();
                break;
            case 2:
                return $this->getTitle();
                break;
            case 3:
                return $this->getSlug();
                break;
            case 4:
                return $this->getBody();
                break;
            case 5:
                return $this->getUpdatedAt();
                break;
            case 6:
                return $this->getTreeLeft();
                break;
            case 7:
                return $this->getTreeRight();
                break;
            case 8:
                return $this->getTreeLevel();
                break;
            case 9:
                return $this->getCreatedAt();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['Chapter'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Chapter'][$this->hashCode()] = true;
        $keys = ChapterTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getBookId(),
            $keys[2] => $this->getTitle(),
            $keys[3] => $this->getSlug(),
            $keys[4] => $this->getBody(),
            $keys[5] => $this->getUpdatedAt(),
            $keys[6] => $this->getTreeLeft(),
            $keys[7] => $this->getTreeRight(),
            $keys[8] => $this->getTreeLevel(),
            $keys[9] => $this->getCreatedAt(),
        );
        if ($result[$keys[5]] instanceof \DateTimeInterface) {
            $result[$keys[5]] = $result[$keys[5]]->format('c');
        }

        if ($result[$keys[9]] instanceof \DateTimeInterface) {
            $result[$keys[9]] = $result[$keys[9]]->format('c');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aBook) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'book';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'books';
                        break;
                    default:
                        $key = 'Book';
                }

                $result[$key] = $this->aBook->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\Chapter
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ChapterTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\Chapter
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setBookId($value);
                break;
            case 2:
                $this->setTitle($value);
                break;
            case 3:
                $this->setSlug($value);
                break;
            case 4:
                $this->setBody($value);
                break;
            case 5:
                $this->setUpdatedAt($value);
                break;
            case 6:
                $this->setTreeLeft($value);
                break;
            case 7:
                $this->setTreeRight($value);
                break;
            case 8:
                $this->setTreeLevel($value);
                break;
            case 9:
                $this->setCreatedAt($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = ChapterTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setBookId($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setTitle($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setSlug($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setBody($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setUpdatedAt($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setTreeLeft($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setTreeRight($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setTreeLevel($arr[$keys[8]]);
        }
        if (array_key_exists($keys[9], $arr)) {
            $this->setCreatedAt($arr[$keys[9]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\Chapter The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ChapterTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ChapterTableMap::COL_ID)) {
            $criteria->add(ChapterTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_BOOK_ID)) {
            $criteria->add(ChapterTableMap::COL_BOOK_ID, $this->book_id);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TITLE)) {
            $criteria->add(ChapterTableMap::COL_TITLE, $this->title);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_SLUG)) {
            $criteria->add(ChapterTableMap::COL_SLUG, $this->slug);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_BODY)) {
            $criteria->add(ChapterTableMap::COL_BODY, $this->body);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_UPDATED_AT)) {
            $criteria->add(ChapterTableMap::COL_UPDATED_AT, $this->updated_at);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TREE_LEFT)) {
            $criteria->add(ChapterTableMap::COL_TREE_LEFT, $this->tree_left);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TREE_RIGHT)) {
            $criteria->add(ChapterTableMap::COL_TREE_RIGHT, $this->tree_right);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_TREE_LEVEL)) {
            $criteria->add(ChapterTableMap::COL_TREE_LEVEL, $this->tree_level);
        }
        if ($this->isColumnModified(ChapterTableMap::COL_CREATED_AT)) {
            $criteria->add(ChapterTableMap::COL_CREATED_AT, $this->created_at);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildChapterQuery::create();
        $criteria->add(ChapterTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \Chapter (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setBookId($this->getBookId());
        $copyObj->setTitle($this->getTitle());
        $copyObj->setSlug($this->getSlug());
        $copyObj->setBody($this->getBody());
        $copyObj->setUpdatedAt($this->getUpdatedAt());
        $copyObj->setTreeLeft($this->getTreeLeft());
        $copyObj->setTreeRight($this->getTreeRight());
        $copyObj->setTreeLevel($this->getTreeLevel());
        $copyObj->setCreatedAt($this->getCreatedAt());
        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \Chapter Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildBook object.
     *
     * @param  ChildBook $v
     * @return $this|\Chapter The current object (for fluent API support)
     * @throws PropelException
     */
    public function setBook(ChildBook $v = null)
    {
        if ($v === null) {
            $this->setBookId(0);
        } else {
            $this->setBookId($v->getId());
        }

        $this->aBook = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildBook object, it will not be re-added.
        if ($v !== null) {
            $v->addChapter($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildBook object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildBook The associated ChildBook object.
     * @throws PropelException
     */
    public function getBook(ConnectionInterface $con = null)
    {
        if ($this->aBook === null && ($this->book_id != 0)) {
            $this->aBook = ChildBookQuery::create()->findPk($this->book_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aBook->addChapters($this);
             */
        }

        return $this->aBook;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        if (null !== $this->aBook) {
            $this->aBook->removeChapter($this);
        }
        $this->id = null;
        $this->book_id = null;
        $this->title = null;
        $this->slug = null;
        $this->body = null;
        $this->updated_at = null;
        $this->tree_left = null;
        $this->tree_right = null;
        $this->tree_level = null;
        $this->created_at = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
        } // if ($deep)

        // nested_set behavior
        $this->collNestedSetChildren = null;
        $this->aNestedSetParent = null;
        $this->aBook = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ChapterTableMap::DEFAULT_STRING_FORMAT);
    }

    // nested_set behavior

    /**
     * Execute queries that were saved to be run inside the save transaction
     *
     * @param  ConnectionInterface $con Connection to use.
     */
    protected function processNestedSetQueries(ConnectionInterface $con)
    {
        foreach ($this->nestedSetQueries as $query) {
            $query['arguments'][] = $con;
            call_user_func_array($query['callable'], $query['arguments']);
        }
        $this->nestedSetQueries = array();
    }

    /**
     * Proxy getter method for the left value of the nested set model.
     * It provides a generic way to get the value, whatever the actual column name is.
     *
     * @return     int The nested set left value
     */
    public function getLeftValue()
    {
        return $this->tree_left;
    }

    /**
     * Proxy getter method for the right value of the nested set model.
     * It provides a generic way to get the value, whatever the actual column name is.
     *
     * @return     int The nested set right value
     */
    public function getRightValue()
    {
        return $this->tree_right;
    }

    /**
     * Proxy getter method for the level value of the nested set model.
     * It provides a generic way to get the value, whatever the actual column name is.
     *
     * @return     int The nested set level value
     */
    public function getLevel()
    {
        return $this->tree_level;
    }

    /**
     * Proxy getter method for the scope value of the nested set model.
     * It provides a generic way to get the value, whatever the actual column name is.
     *
     * @return     int The nested set scope value
     */
    public function getScopeValue()
    {
        return $this->book_id;
    }

    /**
     * Proxy setter method for the left value of the nested set model.
     * It provides a generic way to set the value, whatever the actual column name is.
     *
     * @param  int $v The nested set left value
     * @return $this|ChildChapter The current object (for fluent API support)
     */
    public function setLeftValue($v)
    {
        return $this->setTreeLeft($v);
    }

    /**
     * Proxy setter method for the right value of the nested set model.
     * It provides a generic way to set the value, whatever the actual column name is.
     *
     * @param      int $v The nested set right value
     * @return     $this|ChildChapter The current object (for fluent API support)
     */
    public function setRightValue($v)
    {
        return $this->setTreeRight($v);
    }

    /**
     * Proxy setter method for the level value of the nested set model.
     * It provides a generic way to set the value, whatever the actual column name is.
     *
     * @param      int $v The nested set level value
     * @return     $this|ChildChapter The current object (for fluent API support)
     */
    public function setLevel($v)
    {
        return $this->setTreeLevel($v);
    }

    /**
     * Proxy setter method for the scope value of the nested set model.
     * It provides a generic way to set the value, whatever the actual column name is.
     *
     * @param      int $v The nested set scope value
     * @return     $this|ChildChapter The current object (for fluent API support)
     */
    public function setScopeValue($v)
    {
        return $this->setBookId($v);
    }

    /**
     * Creates the supplied node as the root node.
     *
     * @return     $this|ChildChapter The current object (for fluent API support)
     * @throws     PropelException
     */
    public function makeRoot()
    {
        if ($this->getLeftValue() || $this->getRightValue()) {
            throw new PropelException('Cannot turn an existing node into a root node.');
        }

        $this->setLeftValue(1);
        $this->setRightValue(2);
        $this->setLevel(0);

        return $this;
    }

    /**
     * Tests if object is a node, i.e. if it is inserted in the tree
     *
     * @return     bool
     */
    public function isInTree()
    {
        return $this->getLeftValue() > 0 && $this->getRightValue() > $this->getLeftValue();
    }

    /**
     * Tests if node is a root
     *
     * @return     bool
     */
    public function isRoot()
    {
        return $this->isInTree() && $this->getLeftValue() == 1;
    }

    /**
     * Tests if node is a leaf
     *
     * @return     bool
     */
    public function isLeaf()
    {
        return $this->isInTree() &&  ($this->getRightValue() - $this->getLeftValue()) == 1;
    }

    /**
     * Tests if node is a descendant of another node
     *
     * @param      ChildChapter $parent Propel node object
     * @return     bool
     */
    public function isDescendantOf(ChildChapter $parent)
    {
        if ($this->getScopeValue() !== $parent->getScopeValue()) {
            return false; //since the `this` and $parent are in different scopes, there's no way that `this` is be a descendant of $parent.
        }

        return $this->isInTree() && $this->getLeftValue() > $parent->getLeftValue() && $this->getRightValue() < $parent->getRightValue();
    }

    /**
     * Tests if node is a ancestor of another node
     *
     * @param      ChildChapter $child Propel node object
     * @return     bool
     */
    public function isAncestorOf(ChildChapter $child)
    {
        return $child->isDescendantOf($this);
    }

    /**
     * Tests if object has an ancestor
     *
     * @return boolean
     */
    public function hasParent()
    {
        return $this->getLevel() > 0;
    }

    /**
     * Sets the cache for parent node of the current object.
     * Warning: this does not move the current object in the tree.
     * Use moveTofirstChildOf() or moveToLastChildOf() for that purpose
     *
     * @param      ChildChapter $parent
     * @return     $this|ChildChapter The current object, for fluid interface
     */
    public function setParent(ChildChapter $parent = null)
    {
        $this->aNestedSetParent = $parent;

        return $this;
    }

    /**
     * Gets parent node for the current object if it exists
     * The result is cached so further calls to the same method don't issue any queries
     *
     * @param  ConnectionInterface $con Connection to use.
     * @return ChildChapter|null Propel object if exists else null
     */
    public function getParent(ConnectionInterface $con = null)
    {
        if (null === $this->aNestedSetParent && $this->hasParent()) {
            $this->aNestedSetParent = ChildChapterQuery::create()
                ->ancestorsOf($this)
                ->orderByLevel(true)
                ->findOne($con);
        }

        return $this->aNestedSetParent;
    }

    /**
     * Determines if the node has previous sibling
     *
     * @param      ConnectionInterface $con Connection to use.
     * @return     bool
     */
    public function hasPrevSibling(ConnectionInterface $con = null)
    {
        if (!ChildChapterQuery::isValid($this)) {
            return false;
        }

        return ChildChapterQuery::create()
            ->filterByTreeRight($this->getLeftValue() - 1)
            ->inTree($this->getScopeValue())
            ->exists($con);
    }

    /**
     * Gets previous sibling for the given node if it exists
     *
     * @param      ConnectionInterface $con Connection to use.
     * @return     ChildChapter|null         Propel object if exists else null
     */
    public function getPrevSibling(ConnectionInterface $con = null)
    {
        return ChildChapterQuery::create()
            ->filterByTreeRight($this->getLeftValue() - 1)
            ->inTree($this->getScopeValue())
            ->findOne($con);
    }

    /**
     * Determines if the node has next sibling
     *
     * @param      ConnectionInterface $con Connection to use.
     * @return     bool
     */
    public function hasNextSibling(ConnectionInterface $con = null)
    {
        if (!ChildChapterQuery::isValid($this)) {
            return false;
        }

        return ChildChapterQuery::create()
            ->filterByTreeLeft($this->getRightValue() + 1)
            ->inTree($this->getScopeValue())
            ->exists($con);
    }

    /**
     * Gets next sibling for the given node if it exists
     *
     * @param      ConnectionInterface $con Connection to use.
     * @return     ChildChapter|null         Propel object if exists else null
     */
    public function getNextSibling(ConnectionInterface $con = null)
    {
        return ChildChapterQuery::create()
            ->filterByTreeLeft($this->getRightValue() + 1)
            ->inTree($this->getScopeValue())
            ->findOne($con);
    }

    /**
     * Clears out the $collNestedSetChildren collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return     void
     */
    public function clearNestedSetChildren()
    {
        $this->collNestedSetChildren = null;
    }

    /**
     * Initializes the $collNestedSetChildren collection.
     *
     * @return     void
     */
    public function initNestedSetChildren()
    {
        $collectionClassName = \Map\ChapterTableMap::getTableMap()->getCollectionClassName();

        $this->collNestedSetChildren = new $collectionClassName;
        $this->collNestedSetChildren->setModel('\Chapter');
    }

    /**
     * Adds an element to the internal $collNestedSetChildren collection.
     * Beware that this doesn't insert a node in the tree.
     * This method is only used to facilitate children hydration.
     *
     * @param      ChildChapter $chapter
     *
     * @return     void
     */
    public function addNestedSetChild(ChildChapter $chapter)
    {
        if (null === $this->collNestedSetChildren) {
            $this->initNestedSetChildren();
        }
        if (!in_array($chapter, $this->collNestedSetChildren->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->collNestedSetChildren[]= $chapter;
            $chapter->setParent($this);
        }
    }

    /**
     * Tests if node has children
     *
     * @return     bool
     */
    public function hasChildren()
    {
        return ($this->getRightValue() - $this->getLeftValue()) > 1;
    }

    /**
     * Gets the children of the given node
     *
     * @param      Criteria  $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     ObjectCollection|ChildChapter[] List of ChildChapter objects
     */
    public function getChildren(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if (null === $this->collNestedSetChildren || null !== $criteria) {
            if ($this->isLeaf() || ($this->isNew() && null === $this->collNestedSetChildren)) {
                // return empty collection
                $this->initNestedSetChildren();
            } else {
                $collNestedSetChildren = ChildChapterQuery::create(null, $criteria)
                    ->childrenOf($this)
                    ->orderByBranch()
                    ->find($con);
                if (null !== $criteria) {
                    return $collNestedSetChildren;
                }
                $this->collNestedSetChildren = $collNestedSetChildren;
            }
        }

        return $this->collNestedSetChildren;
    }

    /**
     * Gets number of children for the given node
     *
     * @param      Criteria  $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     int       Number of children
     */
    public function countChildren(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if (null === $this->collNestedSetChildren || null !== $criteria) {
            if ($this->isLeaf() || ($this->isNew() && null === $this->collNestedSetChildren)) {
                return 0;
            } else {
                return ChildChapterQuery::create(null, $criteria)
                    ->childrenOf($this)
                    ->count($con);
            }
        } else {
            return count($this->collNestedSetChildren);
        }
    }

    /**
     * Gets the first child of the given node
     *
     * @param      Criteria $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     ChildChapter|null First child or null if this is a leaf
     */
    public function getFirstChild(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if ($this->isLeaf()) {
            return null;
        } else {
            return ChildChapterQuery::create(null, $criteria)
                ->childrenOf($this)
                ->orderByBranch()
                ->findOne($con);
        }
    }

    /**
     * Gets the last child of the given node
     *
     * @param      Criteria $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     ChildChapter|null Last child or null if this is a leaf
     */
    public function getLastChild(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if ($this->isLeaf()) {
            return null;
        } else {
            return ChildChapterQuery::create(null, $criteria)
                ->childrenOf($this)
                ->orderByBranch(true)
                ->findOne($con);
        }
    }

    /**
     * Gets the siblings of the given node
     *
     * @param boolean             $includeNode Whether to include the current node or not
     * @param Criteria            $criteria Criteria to filter results.
     * @param ConnectionInterface $con Connection to use.
     *
     * @return ObjectCollection|ChildChapter[] List of ChildChapter objects
     */
    public function getSiblings($includeNode = false, Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if ($this->isRoot()) {
            return array();
        } else {
            $query = ChildChapterQuery::create(null, $criteria)
                ->childrenOf($this->getParent($con))
                ->orderByBranch();
            if (!$includeNode) {
                $query->prune($this);
            }

            return $query->find($con);
        }
    }

    /**
     * Gets descendants for the given node
     *
     * @param      Criteria $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     ObjectCollection|ChildChapter[] List of ChildChapter objects
     */
    public function getDescendants(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if ($this->isLeaf()) {
            return array();
        } else {
            return ChildChapterQuery::create(null, $criteria)
                ->descendantsOf($this)
                ->orderByBranch()
                ->find($con);
        }
    }

    /**
     * Gets number of descendants for the given node
     *
     * @param      Criteria $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     int         Number of descendants
     */
    public function countDescendants(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if ($this->isLeaf()) {
            // save one query
            return 0;
        } else {
            return ChildChapterQuery::create(null, $criteria)
                ->descendantsOf($this)
                ->count($con);
        }
    }

    /**
     * Gets descendants for the given node, plus the current node
     *
     * @param      Criteria $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     ObjectCollection|ChildChapter[] List of ChildChapter objects
     */
    public function getBranch(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        return ChildChapterQuery::create(null, $criteria)
            ->branchOf($this)
            ->orderByBranch()
            ->find($con);
    }

    /**
     * Gets ancestors for the given node, starting with the root node
     * Use it for breadcrumb paths for instance
     *
     * @param      Criteria $criteria Criteria to filter results.
     * @param      ConnectionInterface $con Connection to use.
     * @return     ObjectCollection|ChildChapter[] List of ChildChapter objects
     */
    public function getAncestors(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        if ($this->isRoot()) {
            // save one query
            return array();
        } else {
            return ChildChapterQuery::create(null, $criteria)
                ->ancestorsOf($this)
                ->orderByBranch()
                ->find($con);
        }
    }

    /**
     * Inserts the given $child node as first child of current
     * The modifications in the current object and the tree
     * are not persisted until the child object is saved.
     *
     * @param      ChildChapter $child    Propel object for child node
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function addChild(ChildChapter $child)
    {
        if ($this->isNew()) {
            throw new PropelException('A ChildChapter object must not be new to accept children.');
        }
        $child->insertAsFirstChildOf($this);

        return $this;
    }

    /**
     * Inserts the current node as first child of given $parent node
     * The modifications in the current object and the tree
     * are not persisted until the current object is saved.
     *
     * @param      ChildChapter $parent    Propel object for parent node
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function insertAsFirstChildOf(ChildChapter $parent)
    {
        if ($this->isInTree()) {
            throw new PropelException('A ChildChapter object must not already be in the tree to be inserted. Use the moveToFirstChildOf() instead.');
        }
        $left = $parent->getLeftValue() + 1;
        // Update node properties
        $this->setLeftValue($left);
        $this->setRightValue($left + 1);
        $this->setLevel($parent->getLevel() + 1);
        $scope = $parent->getScopeValue();
        $this->setScopeValue($scope);
        // update the children collection of the parent
        $parent->addNestedSetChild($this);

        // Keep the tree modification query for the save() transaction
        $this->nestedSetQueries[] = array(
            'callable'  => array('\ChapterQuery', 'makeRoomForLeaf'),
            'arguments' => array($left, $scope, $this->isNew() ? null : $this)
        );

        return $this;
    }

    /**
     * Inserts the current node as last child of given $parent node
     * The modifications in the current object and the tree
     * are not persisted until the current object is saved.
     *
     * @param  ChildChapter $parent Propel object for parent node
     * @return $this|ChildChapter The current Propel object
     */
    public function insertAsLastChildOf(ChildChapter $parent)
    {
        if ($this->isInTree()) {
            throw new PropelException(
                'A ChildChapter object must not already be in the tree to be inserted. Use the moveToLastChildOf() instead.'
            );
        }

        $left = $parent->getRightValue();
        // Update node properties
        $this->setLeftValue($left);
        $this->setRightValue($left + 1);
        $this->setLevel($parent->getLevel() + 1);

        $scope = $parent->getScopeValue();
        $this->setScopeValue($scope);

        // update the children collection of the parent
        $parent->addNestedSetChild($this);

        // Keep the tree modification query for the save() transaction
        $this->nestedSetQueries []= array(
            'callable'  => array('\ChapterQuery', 'makeRoomForLeaf'),
            'arguments' => array($left, $scope, $this->isNew() ? null : $this)
        );

        return $this;
    }

    /**
     * Inserts the current node as prev sibling given $sibling node
     * The modifications in the current object and the tree
     * are not persisted until the current object is saved.
     *
     * @param      ChildChapter $sibling    Propel object for parent node
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function insertAsPrevSiblingOf(ChildChapter $sibling)
    {
        if ($this->isInTree()) {
            throw new PropelException('A ChildChapter object must not already be in the tree to be inserted. Use the moveToPrevSiblingOf() instead.');
        }
        $left = $sibling->getLeftValue();
        // Update node properties
        $this->setLeftValue($left);
        $this->setRightValue($left + 1);
        $this->setLevel($sibling->getLevel());
        $scope = $sibling->getScopeValue();
        $this->setScopeValue($scope);
        // Keep the tree modification query for the save() transaction
        $this->nestedSetQueries []= array(
            'callable'  => array('\ChapterQuery', 'makeRoomForLeaf'),
            'arguments' => array($left, $scope, $this->isNew() ? null : $this)
        );

        return $this;
    }

    /**
     * Inserts the current node as next sibling given $sibling node
     * The modifications in the current object and the tree
     * are not persisted until the current object is saved.
     *
     * @param      ChildChapter $sibling    Propel object for parent node
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function insertAsNextSiblingOf(ChildChapter $sibling)
    {
        if ($this->isInTree()) {
            throw new PropelException('A ChildChapter object must not already be in the tree to be inserted. Use the moveToNextSiblingOf() instead.');
        }
        $left = $sibling->getRightValue() + 1;
        // Update node properties
        $this->setLeftValue($left);
        $this->setRightValue($left + 1);
        $this->setLevel($sibling->getLevel());
        $scope = $sibling->getScopeValue();
        $this->setScopeValue($scope);
        // Keep the tree modification query for the save() transaction
        $this->nestedSetQueries []= array(
            'callable'  => array('\ChapterQuery', 'makeRoomForLeaf'),
            'arguments' => array($left, $scope, $this->isNew() ? null : $this)
        );

        return $this;
    }

    /**
     * Moves current node and its subtree to be the first child of $parent
     * The modifications in the current object and the tree are immediate
     *
     * @param      ChildChapter $parent    Propel object for parent node
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function moveToFirstChildOf(ChildChapter $parent, ConnectionInterface $con = null)
    {
        if (!$this->isInTree()) {
            throw new PropelException('A ChildChapter object must be already in the tree to be moved. Use the insertAsFirstChildOf() instead.');
        }
        if ($parent->isDescendantOf($this)) {
            throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
        }

        $this->moveSubtreeTo($parent->getLeftValue() + 1, $parent->getLevel() - $this->getLevel() + 1, $parent->getScopeValue(), $con);

        return $this;
    }

    /**
     * Moves current node and its subtree to be the last child of $parent
     * The modifications in the current object and the tree are immediate
     *
     * @param      ChildChapter $parent    Propel object for parent node
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function moveToLastChildOf(ChildChapter $parent, ConnectionInterface $con = null)
    {
        if (!$this->isInTree()) {
            throw new PropelException('A ChildChapter object must be already in the tree to be moved. Use the insertAsLastChildOf() instead.');
        }
        if ($parent->isDescendantOf($this)) {
            throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
        }

        $this->moveSubtreeTo($parent->getRightValue(), $parent->getLevel() - $this->getLevel() + 1, $parent->getScopeValue(), $con);

        return $this;
    }

    /**
     * Moves current node and its subtree to be the previous sibling of $sibling
     * The modifications in the current object and the tree are immediate
     *
     * @param      ChildChapter $sibling    Propel object for sibling node
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function moveToPrevSiblingOf(ChildChapter $sibling, ConnectionInterface $con = null)
    {
        if (!$this->isInTree()) {
            throw new PropelException('A ChildChapter object must be already in the tree to be moved. Use the insertAsPrevSiblingOf() instead.');
        }
        if ($sibling->isRoot()) {
            throw new PropelException('Cannot move to previous sibling of a root node.');
        }
        if ($sibling->isDescendantOf($this)) {
            throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
        }

        $this->moveSubtreeTo($sibling->getLeftValue(), $sibling->getLevel() - $this->getLevel(), $sibling->getScopeValue(), $con);

        return $this;
    }

    /**
     * Moves current node and its subtree to be the next sibling of $sibling
     * The modifications in the current object and the tree are immediate
     *
     * @param      ChildChapter $sibling    Propel object for sibling node
     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     $this|ChildChapter The current Propel object
     */
    public function moveToNextSiblingOf(ChildChapter $sibling, ConnectionInterface $con = null)
    {
        if (!$this->isInTree()) {
            throw new PropelException('A ChildChapter object must be already in the tree to be moved. Use the insertAsNextSiblingOf() instead.');
        }
        if ($sibling->isRoot()) {
            throw new PropelException('Cannot move to next sibling of a root node.');
        }
        if ($sibling->isDescendantOf($this)) {
            throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
        }

        $this->moveSubtreeTo($sibling->getRightValue() + 1, $sibling->getLevel() - $this->getLevel(), $sibling->getScopeValue(), $con);

        return $this;
    }

    /**
     * Move current node and its children to location $destLeft and updates rest of tree
     *
     * @param      int    $destLeft Destination left value
     * @param      int    $levelDelta Delta to add to the levels
     * @param      ConnectionInterface $con        Connection to use.
     */
    protected function moveSubtreeTo($destLeft, $levelDelta, $targetScope = null, ConnectionInterface $con = null)
    {
        $left  = $this->getLeftValue();
        $right = $this->getRightValue();
        $scope = $this->getScopeValue();

        if ($targetScope === null) {
            $targetScope = $scope;
        }

        $treeSize = $right - $left +1;

        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ChapterTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con, $treeSize, $destLeft, $left, $right, $levelDelta, $scope, $targetScope) {
            $preventDefault = false;

            // make room next to the target for the subtree
            ChildChapterQuery::shiftRLValues($treeSize, $destLeft, null, $targetScope, $con);

            if ($targetScope != $scope) {

                //move subtree to < 0, so the items are out of scope.
                ChildChapterQuery::shiftRLValues(-$right, $left, $right, $scope, $con);

                //update scopes
                ChildChapterQuery::setNegativeScope($targetScope, $con);

                //update levels
                ChildChapterQuery::shiftLevel($levelDelta, $left - $right, 0, $targetScope, $con);

                //move the subtree to the target
                ChildChapterQuery::shiftRLValues(($right - $left) + $destLeft, $left - $right, 0, $targetScope, $con);


                $preventDefault = true;
            }

            if (!$preventDefault) {

                if ($left >= $destLeft) { // src was shifted too?
                    $left += $treeSize;
                    $right += $treeSize;
                }

                if ($levelDelta) {
                    // update the levels of the subtree
                    ChildChapterQuery::shiftLevel($levelDelta, $left, $right, $scope, $con);
                }

                // move the subtree to the target
                ChildChapterQuery::shiftRLValues($destLeft - $left, $left, $right, $scope, $con);
            }

            // remove the empty room at the previous location of the subtree
            ChildChapterQuery::shiftRLValues(-$treeSize, $right + 1, null, $scope, $con);

            // update all loaded nodes
            ChildChapterQuery::updateLoadedNodes(null, $con);
        });
    }

    /**
     * Deletes all descendants for the given node
     * Instance pooling is wiped out by this command,
     * so existing ChildChapter instances are probably invalid (except for the current one)
     *
     * @param      ConnectionInterface $con Connection to use.
     *
     * @return     int         number of deleted nodes
     */
    public function deleteDescendants(ConnectionInterface $con = null)
    {
        if ($this->isLeaf()) {
            // save one query
            return;
        }
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(ChapterTableMap::DATABASE_NAME);
        }
        $left = $this->getLeftValue();
        $right = $this->getRightValue();
        $scope = $this->getScopeValue();

        return $con->transaction(function () use ($con, $left, $right, $scope) {
            // delete descendant nodes (will empty the instance pool)
            $ret = ChildChapterQuery::create()
                ->descendantsOf($this)
                ->delete($con);

            // fill up the room that was used by descendants
            ChildChapterQuery::shiftRLValues($left - $right + 1, $right, null, $scope, $con);

            // fix the right value for the current node, which is now a leaf
            $this->setRightValue($left + 1);

            return $ret;
        });
    }

    /**
     * Returns a pre-order iterator for this node and its children.
     *
     * @return NestedSetRecursiveIterator
     */
    public function getIterator()
    {
        return new NestedSetRecursiveIterator($this);
    }

    // validate behavior

    /**
     * Configure validators constraints. The Validator object uses this method
     * to perform object validation.
     *
     * @param ClassMetadata $metadata
     */
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new NotBlank(array ('allowNull' => false,)));
        $metadata->addPropertyConstraint('title', new Length(array ('max' => 255,'allowEmptyString' => false,)));
        $metadata->addPropertyConstraint('slug', new NotBlank(array ('allowNull' => false,)));
        $metadata->addPropertyConstraint('slug', new Length(array ('max' => 255,'allowEmptyString' => false,)));
        $metadata->addPropertyConstraint('slug', new Unique(array ('message' => 'A chapter with this slug already exists.',)));
        $metadata->addPropertyConstraint('slug', new Regex(array ('pattern' => '/^[a-z0-9\\-]+$/','message' => 'Please use only lowercase latin letters and dashes.',)));
        $metadata->addPropertyConstraint('slug', new Regex(array ('pattern' => '/^(?!delete$)[a-z0-9\\-]+$/','message' => 'Reserved words are not allowed.',)));
        $metadata->addPropertyConstraint('body', new Length(array ('max' => 65535,)));
    }

    /**
     * Validates the object and all objects related to this table.
     *
     * @see        getValidationFailures()
     * @param      ValidatorInterface|null $validator A Validator class instance
     * @return     boolean Whether all objects pass validation.
     */
    public function validate(ValidatorInterface $validator = null)
    {
        if (null === $validator) {
            $validator = new RecursiveValidator(
                new ExecutionContextFactory(new IdentityTranslator()),
                new LazyLoadingMetadataFactory(new StaticMethodLoader()),
                new ConstraintValidatorFactory()
            );
        }

        $failureMap = new ConstraintViolationList();

        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            // We call the validate method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            // If validate() method exists, the validate-behavior is configured for related object
            if (method_exists($this->aBook, 'validate')) {
                if (!$this->aBook->validate($validator)) {
                    $failureMap->addAll($this->aBook->getValidationFailures());
                }
            }

            $retval = $validator->validate($this);
            if (count($retval) > 0) {
                $failureMap->addAll($retval);
            }


            $this->alreadyInValidation = false;
        }

        $this->validationFailures = $failureMap;

        return (Boolean) (!(count($this->validationFailures) > 0));

    }

    /**
     * Gets any ConstraintViolation objects that resulted from last call to validate().
     *
     *
     * @return     ConstraintViolationList
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
                return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
            }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
                return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
            }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
                return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
            }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
                return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
            }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
