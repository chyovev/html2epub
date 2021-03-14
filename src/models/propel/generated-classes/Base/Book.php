<?php

namespace Base;

use \Book as ChildBook;
use \BookQuery as ChildBookQuery;
use \Chapter as ChildChapter;
use \ChapterQuery as ChildChapterQuery;
use \Language as ChildLanguage;
use \LanguageQuery as ChildLanguageQuery;
use \DateTime;
use \Exception;
use \PDO;
use Map\BookTableMap;
use Map\ChapterTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
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
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Isbn;
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
 * Base class that represents a row from the 'books' table.
 *
 *
 *
 * @package    propel.generator..Base
 */
abstract class Book implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Map\\BookTableMap';


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
     * The value for the title field.
     *
     * @var        string
     */
    protected $title;

    /**
     * The value for the subtitle field.
     *
     * @var        string
     */
    protected $subtitle;

    /**
     * The value for the slug field.
     *
     * @var        string
     */
    protected $slug;

    /**
     * The value for the author field.
     *
     * @var        string
     */
    protected $author;

    /**
     * The value for the dedication field.
     *
     * @var        string
     */
    protected $dedication;

    /**
     * The value for the language_id field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $language_id;

    /**
     * The value for the publisher field.
     *
     * @var        string
     */
    protected $publisher;

    /**
     * The value for the year field.
     *
     * @var        int
     */
    protected $year;

    /**
     * The value for the isbn field.
     *
     * @var        string
     */
    protected $isbn;

    /**
     * The value for the extra_info field.
     *
     * @var        string
     */
    protected $extra_info;

    /**
     * The value for the cover_image field.
     *
     * @var        string
     */
    protected $cover_image;

    /**
     * The value for the created_at field.
     *
     * @var        DateTime
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     *
     * @var        DateTime
     */
    protected $updated_at;

    /**
     * @var        ChildLanguage
     */
    protected $aLanguage;

    /**
     * @var        ObjectCollection|ChildChapter[] Collection to store aggregation of ChildChapter objects.
     */
    protected $collChapters;
    protected $collChaptersPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

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
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildChapter[]
     */
    protected $chaptersScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->language_id = 0;
    }

    /**
     * Initializes internal state of Base\Book object.
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
     * Compares this with another <code>Book</code> instance.  If
     * <code>obj</code> is an instance of <code>Book</code>, delegates to
     * <code>equals(Book)</code>.  Otherwise, returns <code>false</code>.
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
     * @return $this|Book The current object, for fluid interface
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
     * Get the [title] column value.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the [subtitle] column value.
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
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
     * Get the [author] column value.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get the [dedication] column value.
     *
     * @return string
     */
    public function getDedication()
    {
        return $this->dedication;
    }

    /**
     * Get the [language_id] column value.
     *
     * @return int
     */
    public function getLanguageId()
    {
        return $this->language_id;
    }

    /**
     * Get the [publisher] column value.
     *
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Get the [year] column value.
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Get the [isbn] column value.
     *
     * @return string
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Get the [extra_info] column value.
     *
     * @return string
     */
    public function getExtraInfo()
    {
        return $this->extra_info;
    }

    /**
     * Get the [cover_image] column value.
     *
     * @return string
     */
    public function getCoverImage()
    {
        return $this->cover_image;
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
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[BookTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [title] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setTitle($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->title !== $v) {
            $this->title = $v;
            $this->modifiedColumns[BookTableMap::COL_TITLE] = true;
        }

        return $this;
    } // setTitle()

    /**
     * Set the value of [subtitle] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setSubtitle($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->subtitle !== $v) {
            $this->subtitle = $v;
            $this->modifiedColumns[BookTableMap::COL_SUBTITLE] = true;
        }

        return $this;
    } // setSubtitle()

    /**
     * Set the value of [slug] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setSlug($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->slug !== $v) {
            $this->slug = $v;
            $this->modifiedColumns[BookTableMap::COL_SLUG] = true;
        }

        return $this;
    } // setSlug()

    /**
     * Set the value of [author] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setAuthor($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->author !== $v) {
            $this->author = $v;
            $this->modifiedColumns[BookTableMap::COL_AUTHOR] = true;
        }

        return $this;
    } // setAuthor()

    /**
     * Set the value of [dedication] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setDedication($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->dedication !== $v) {
            $this->dedication = $v;
            $this->modifiedColumns[BookTableMap::COL_DEDICATION] = true;
        }

        return $this;
    } // setDedication()

    /**
     * Set the value of [language_id] column.
     *
     * @param int $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setLanguageId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->language_id !== $v) {
            $this->language_id = $v;
            $this->modifiedColumns[BookTableMap::COL_LANGUAGE_ID] = true;
        }

        if ($this->aLanguage !== null && $this->aLanguage->getId() !== $v) {
            $this->aLanguage = null;
        }

        return $this;
    } // setLanguageId()

    /**
     * Set the value of [publisher] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setPublisher($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->publisher !== $v) {
            $this->publisher = $v;
            $this->modifiedColumns[BookTableMap::COL_PUBLISHER] = true;
        }

        return $this;
    } // setPublisher()

    /**
     * Set the value of [year] column.
     *
     * @param int $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setYear($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->year !== $v) {
            $this->year = $v;
            $this->modifiedColumns[BookTableMap::COL_YEAR] = true;
        }

        return $this;
    } // setYear()

    /**
     * Set the value of [isbn] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setIsbn($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->isbn !== $v) {
            $this->isbn = $v;
            $this->modifiedColumns[BookTableMap::COL_ISBN] = true;
        }

        return $this;
    } // setIsbn()

    /**
     * Set the value of [extra_info] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setExtraInfo($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->extra_info !== $v) {
            $this->extra_info = $v;
            $this->modifiedColumns[BookTableMap::COL_EXTRA_INFO] = true;
        }

        return $this;
    } // setExtraInfo()

    /**
     * Set the value of [cover_image] column.
     *
     * @param string $v new value
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setCoverImage($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->cover_image !== $v) {
            $this->cover_image = $v;
            $this->modifiedColumns[BookTableMap::COL_COVER_IMAGE] = true;
        }

        return $this;
    } // setCoverImage()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($this->created_at === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->created_at->format("Y-m-d H:i:s.u")) {
                $this->created_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[BookTableMap::COL_CREATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\Book The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($this->updated_at === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->updated_at->format("Y-m-d H:i:s.u")) {
                $this->updated_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[BookTableMap::COL_UPDATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setUpdatedAt()

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
            if ($this->language_id !== 0) {
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : BookTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : BookTableMap::translateFieldName('Title', TableMap::TYPE_PHPNAME, $indexType)];
            $this->title = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : BookTableMap::translateFieldName('Subtitle', TableMap::TYPE_PHPNAME, $indexType)];
            $this->subtitle = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : BookTableMap::translateFieldName('Slug', TableMap::TYPE_PHPNAME, $indexType)];
            $this->slug = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : BookTableMap::translateFieldName('Author', TableMap::TYPE_PHPNAME, $indexType)];
            $this->author = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : BookTableMap::translateFieldName('Dedication', TableMap::TYPE_PHPNAME, $indexType)];
            $this->dedication = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : BookTableMap::translateFieldName('LanguageId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->language_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : BookTableMap::translateFieldName('Publisher', TableMap::TYPE_PHPNAME, $indexType)];
            $this->publisher = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : BookTableMap::translateFieldName('Year', TableMap::TYPE_PHPNAME, $indexType)];
            $this->year = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : BookTableMap::translateFieldName('Isbn', TableMap::TYPE_PHPNAME, $indexType)];
            $this->isbn = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 10 + $startcol : BookTableMap::translateFieldName('ExtraInfo', TableMap::TYPE_PHPNAME, $indexType)];
            $this->extra_info = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 11 + $startcol : BookTableMap::translateFieldName('CoverImage', TableMap::TYPE_PHPNAME, $indexType)];
            $this->cover_image = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 12 + $startcol : BookTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 13 + $startcol : BookTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 14; // 14 = BookTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Book'), 0, $e);
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
        if ($this->aLanguage !== null && $this->language_id !== $this->aLanguage->getId()) {
            $this->aLanguage = null;
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
            $con = Propel::getServiceContainer()->getReadConnection(BookTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildBookQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aLanguage = null;
            $this->collChapters = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Book::setDeleted()
     * @see Book::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildBookQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // single_image_upload behavior
                $this->deleteImage();
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
            $con = Propel::getServiceContainer()->getWriteConnection(BookTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            // single_image_upload behavior
            $this->uploadImage();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                $time = time();
                $highPrecision = \Propel\Runtime\Util\PropelDateTime::createHighPrecision();
                if (!$this->isColumnModified(BookTableMap::COL_CREATED_AT)) {
                    $this->setCreatedAt($highPrecision);
                }
                if (!$this->isColumnModified(BookTableMap::COL_UPDATED_AT)) {
                    $this->setUpdatedAt($highPrecision);
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(BookTableMap::COL_UPDATED_AT)) {
                    $this->setUpdatedAt(\Propel\Runtime\Util\PropelDateTime::createHighPrecision());
                }
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                BookTableMap::addInstanceToPool($this);
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

            if ($this->aLanguage !== null) {
                if ($this->aLanguage->isModified() || $this->aLanguage->isNew()) {
                    $affectedRows += $this->aLanguage->save($con);
                }
                $this->setLanguage($this->aLanguage);
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

            if ($this->chaptersScheduledForDeletion !== null) {
                if (!$this->chaptersScheduledForDeletion->isEmpty()) {
                    \ChapterQuery::create()
                        ->filterByPrimaryKeys($this->chaptersScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->chaptersScheduledForDeletion = null;
                }
            }

            if ($this->collChapters !== null) {
                foreach ($this->collChapters as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
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

        $this->modifiedColumns[BookTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . BookTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(BookTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(BookTableMap::COL_TITLE)) {
            $modifiedColumns[':p' . $index++]  = 'title';
        }
        if ($this->isColumnModified(BookTableMap::COL_SUBTITLE)) {
            $modifiedColumns[':p' . $index++]  = 'subtitle';
        }
        if ($this->isColumnModified(BookTableMap::COL_SLUG)) {
            $modifiedColumns[':p' . $index++]  = 'slug';
        }
        if ($this->isColumnModified(BookTableMap::COL_AUTHOR)) {
            $modifiedColumns[':p' . $index++]  = 'author';
        }
        if ($this->isColumnModified(BookTableMap::COL_DEDICATION)) {
            $modifiedColumns[':p' . $index++]  = 'dedication';
        }
        if ($this->isColumnModified(BookTableMap::COL_LANGUAGE_ID)) {
            $modifiedColumns[':p' . $index++]  = 'language_id';
        }
        if ($this->isColumnModified(BookTableMap::COL_PUBLISHER)) {
            $modifiedColumns[':p' . $index++]  = 'publisher';
        }
        if ($this->isColumnModified(BookTableMap::COL_YEAR)) {
            $modifiedColumns[':p' . $index++]  = 'year';
        }
        if ($this->isColumnModified(BookTableMap::COL_ISBN)) {
            $modifiedColumns[':p' . $index++]  = 'isbn';
        }
        if ($this->isColumnModified(BookTableMap::COL_EXTRA_INFO)) {
            $modifiedColumns[':p' . $index++]  = 'extra_info';
        }
        if ($this->isColumnModified(BookTableMap::COL_COVER_IMAGE)) {
            $modifiedColumns[':p' . $index++]  = 'cover_image';
        }
        if ($this->isColumnModified(BookTableMap::COL_CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'created_at';
        }
        if ($this->isColumnModified(BookTableMap::COL_UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'updated_at';
        }

        $sql = sprintf(
            'INSERT INTO books (%s) VALUES (%s)',
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
                    case 'title':
                        $stmt->bindValue($identifier, $this->title, PDO::PARAM_STR);
                        break;
                    case 'subtitle':
                        $stmt->bindValue($identifier, $this->subtitle, PDO::PARAM_STR);
                        break;
                    case 'slug':
                        $stmt->bindValue($identifier, $this->slug, PDO::PARAM_STR);
                        break;
                    case 'author':
                        $stmt->bindValue($identifier, $this->author, PDO::PARAM_STR);
                        break;
                    case 'dedication':
                        $stmt->bindValue($identifier, $this->dedication, PDO::PARAM_STR);
                        break;
                    case 'language_id':
                        $stmt->bindValue($identifier, $this->language_id, PDO::PARAM_INT);
                        break;
                    case 'publisher':
                        $stmt->bindValue($identifier, $this->publisher, PDO::PARAM_STR);
                        break;
                    case 'year':
                        $stmt->bindValue($identifier, $this->year, PDO::PARAM_INT);
                        break;
                    case 'isbn':
                        $stmt->bindValue($identifier, $this->isbn, PDO::PARAM_STR);
                        break;
                    case 'extra_info':
                        $stmt->bindValue($identifier, $this->extra_info, PDO::PARAM_STR);
                        break;
                    case 'cover_image':
                        $stmt->bindValue($identifier, $this->cover_image, PDO::PARAM_STR);
                        break;
                    case 'created_at':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                    case 'updated_at':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
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
        $pos = BookTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getTitle();
                break;
            case 2:
                return $this->getSubtitle();
                break;
            case 3:
                return $this->getSlug();
                break;
            case 4:
                return $this->getAuthor();
                break;
            case 5:
                return $this->getDedication();
                break;
            case 6:
                return $this->getLanguageId();
                break;
            case 7:
                return $this->getPublisher();
                break;
            case 8:
                return $this->getYear();
                break;
            case 9:
                return $this->getIsbn();
                break;
            case 10:
                return $this->getExtraInfo();
                break;
            case 11:
                return $this->getCoverImage();
                break;
            case 12:
                return $this->getCreatedAt();
                break;
            case 13:
                return $this->getUpdatedAt();
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

        if (isset($alreadyDumpedObjects['Book'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Book'][$this->hashCode()] = true;
        $keys = BookTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getTitle(),
            $keys[2] => $this->getSubtitle(),
            $keys[3] => $this->getSlug(),
            $keys[4] => $this->getAuthor(),
            $keys[5] => $this->getDedication(),
            $keys[6] => $this->getLanguageId(),
            $keys[7] => $this->getPublisher(),
            $keys[8] => $this->getYear(),
            $keys[9] => $this->getIsbn(),
            $keys[10] => $this->getExtraInfo(),
            $keys[11] => $this->getCoverImage(),
            $keys[12] => $this->getCreatedAt(),
            $keys[13] => $this->getUpdatedAt(),
        );
        if ($result[$keys[12]] instanceof \DateTimeInterface) {
            $result[$keys[12]] = $result[$keys[12]]->format('c');
        }

        if ($result[$keys[13]] instanceof \DateTimeInterface) {
            $result[$keys[13]] = $result[$keys[13]]->format('c');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aLanguage) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'language';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'languages';
                        break;
                    default:
                        $key = 'Language';
                }

                $result[$key] = $this->aLanguage->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collChapters) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'chapters';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'chapterss';
                        break;
                    default:
                        $key = 'Chapters';
                }

                $result[$key] = $this->collChapters->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
     * @return $this|\Book
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = BookTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\Book
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setTitle($value);
                break;
            case 2:
                $this->setSubtitle($value);
                break;
            case 3:
                $this->setSlug($value);
                break;
            case 4:
                $this->setAuthor($value);
                break;
            case 5:
                $this->setDedication($value);
                break;
            case 6:
                $this->setLanguageId($value);
                break;
            case 7:
                $this->setPublisher($value);
                break;
            case 8:
                $this->setYear($value);
                break;
            case 9:
                $this->setIsbn($value);
                break;
            case 10:
                $this->setExtraInfo($value);
                break;
            case 11:
                $this->setCoverImage($value);
                break;
            case 12:
                $this->setCreatedAt($value);
                break;
            case 13:
                $this->setUpdatedAt($value);
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
        $keys = BookTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setTitle($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setSubtitle($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setSlug($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setAuthor($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setDedication($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setLanguageId($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setPublisher($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setYear($arr[$keys[8]]);
        }
        if (array_key_exists($keys[9], $arr)) {
            $this->setIsbn($arr[$keys[9]]);
        }
        if (array_key_exists($keys[10], $arr)) {
            $this->setExtraInfo($arr[$keys[10]]);
        }
        if (array_key_exists($keys[11], $arr)) {
            $this->setCoverImage($arr[$keys[11]]);
        }
        if (array_key_exists($keys[12], $arr)) {
            $this->setCreatedAt($arr[$keys[12]]);
        }
        if (array_key_exists($keys[13], $arr)) {
            $this->setUpdatedAt($arr[$keys[13]]);
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
     * @return $this|\Book The current object, for fluid interface
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
        $criteria = new Criteria(BookTableMap::DATABASE_NAME);

        if ($this->isColumnModified(BookTableMap::COL_ID)) {
            $criteria->add(BookTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(BookTableMap::COL_TITLE)) {
            $criteria->add(BookTableMap::COL_TITLE, $this->title);
        }
        if ($this->isColumnModified(BookTableMap::COL_SUBTITLE)) {
            $criteria->add(BookTableMap::COL_SUBTITLE, $this->subtitle);
        }
        if ($this->isColumnModified(BookTableMap::COL_SLUG)) {
            $criteria->add(BookTableMap::COL_SLUG, $this->slug);
        }
        if ($this->isColumnModified(BookTableMap::COL_AUTHOR)) {
            $criteria->add(BookTableMap::COL_AUTHOR, $this->author);
        }
        if ($this->isColumnModified(BookTableMap::COL_DEDICATION)) {
            $criteria->add(BookTableMap::COL_DEDICATION, $this->dedication);
        }
        if ($this->isColumnModified(BookTableMap::COL_LANGUAGE_ID)) {
            $criteria->add(BookTableMap::COL_LANGUAGE_ID, $this->language_id);
        }
        if ($this->isColumnModified(BookTableMap::COL_PUBLISHER)) {
            $criteria->add(BookTableMap::COL_PUBLISHER, $this->publisher);
        }
        if ($this->isColumnModified(BookTableMap::COL_YEAR)) {
            $criteria->add(BookTableMap::COL_YEAR, $this->year);
        }
        if ($this->isColumnModified(BookTableMap::COL_ISBN)) {
            $criteria->add(BookTableMap::COL_ISBN, $this->isbn);
        }
        if ($this->isColumnModified(BookTableMap::COL_EXTRA_INFO)) {
            $criteria->add(BookTableMap::COL_EXTRA_INFO, $this->extra_info);
        }
        if ($this->isColumnModified(BookTableMap::COL_COVER_IMAGE)) {
            $criteria->add(BookTableMap::COL_COVER_IMAGE, $this->cover_image);
        }
        if ($this->isColumnModified(BookTableMap::COL_CREATED_AT)) {
            $criteria->add(BookTableMap::COL_CREATED_AT, $this->created_at);
        }
        if ($this->isColumnModified(BookTableMap::COL_UPDATED_AT)) {
            $criteria->add(BookTableMap::COL_UPDATED_AT, $this->updated_at);
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
        $criteria = ChildBookQuery::create();
        $criteria->add(BookTableMap::COL_ID, $this->id);

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
     * @param      object $copyObj An object of \Book (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setTitle($this->getTitle());
        $copyObj->setSubtitle($this->getSubtitle());
        $copyObj->setSlug($this->getSlug());
        $copyObj->setAuthor($this->getAuthor());
        $copyObj->setDedication($this->getDedication());
        $copyObj->setLanguageId($this->getLanguageId());
        $copyObj->setPublisher($this->getPublisher());
        $copyObj->setYear($this->getYear());
        $copyObj->setIsbn($this->getIsbn());
        $copyObj->setExtraInfo($this->getExtraInfo());
        $copyObj->setCoverImage($this->getCoverImage());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getChapters() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addChapter($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

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
     * @return \Book Clone of current object.
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
     * Declares an association between this object and a ChildLanguage object.
     *
     * @param  ChildLanguage $v
     * @return $this|\Book The current object (for fluent API support)
     * @throws PropelException
     */
    public function setLanguage(ChildLanguage $v = null)
    {
        if ($v === null) {
            $this->setLanguageId(0);
        } else {
            $this->setLanguageId($v->getId());
        }

        $this->aLanguage = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildLanguage object, it will not be re-added.
        if ($v !== null) {
            $v->addBook($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildLanguage object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildLanguage The associated ChildLanguage object.
     * @throws PropelException
     */
    public function getLanguage(ConnectionInterface $con = null)
    {
        if ($this->aLanguage === null && ($this->language_id != 0)) {
            $this->aLanguage = ChildLanguageQuery::create()->findPk($this->language_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aLanguage->addBooks($this);
             */
        }

        return $this->aLanguage;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('Chapter' == $relationName) {
            $this->initChapters();
            return;
        }
    }

    /**
     * Clears out the collChapters collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addChapters()
     */
    public function clearChapters()
    {
        $this->collChapters = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collChapters collection loaded partially.
     */
    public function resetPartialChapters($v = true)
    {
        $this->collChaptersPartial = $v;
    }

    /**
     * Initializes the collChapters collection.
     *
     * By default this just sets the collChapters collection to an empty array (like clearcollChapters());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initChapters($overrideExisting = true)
    {
        if (null !== $this->collChapters && !$overrideExisting) {
            return;
        }

        $collectionClassName = ChapterTableMap::getTableMap()->getCollectionClassName();

        $this->collChapters = new $collectionClassName;
        $this->collChapters->setModel('\Chapter');
    }

    /**
     * Gets an array of ChildChapter objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildBook is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildChapter[] List of ChildChapter objects
     * @throws PropelException
     */
    public function getChapters(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collChaptersPartial && !$this->isNew();
        if (null === $this->collChapters || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collChapters) {
                // return empty collection
                $this->initChapters();
            } else {
                $collChapters = ChildChapterQuery::create(null, $criteria)
                    ->filterByBook($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collChaptersPartial && count($collChapters)) {
                        $this->initChapters(false);

                        foreach ($collChapters as $obj) {
                            if (false == $this->collChapters->contains($obj)) {
                                $this->collChapters->append($obj);
                            }
                        }

                        $this->collChaptersPartial = true;
                    }

                    return $collChapters;
                }

                if ($partial && $this->collChapters) {
                    foreach ($this->collChapters as $obj) {
                        if ($obj->isNew()) {
                            $collChapters[] = $obj;
                        }
                    }
                }

                $this->collChapters = $collChapters;
                $this->collChaptersPartial = false;
            }
        }

        return $this->collChapters;
    }

    /**
     * Sets a collection of ChildChapter objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $chapters A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildBook The current object (for fluent API support)
     */
    public function setChapters(Collection $chapters, ConnectionInterface $con = null)
    {
        /** @var ChildChapter[] $chaptersToDelete */
        $chaptersToDelete = $this->getChapters(new Criteria(), $con)->diff($chapters);


        $this->chaptersScheduledForDeletion = $chaptersToDelete;

        foreach ($chaptersToDelete as $chapterRemoved) {
            $chapterRemoved->setBook(null);
        }

        $this->collChapters = null;
        foreach ($chapters as $chapter) {
            $this->addChapter($chapter);
        }

        $this->collChapters = $chapters;
        $this->collChaptersPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Chapter objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related Chapter objects.
     * @throws PropelException
     */
    public function countChapters(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collChaptersPartial && !$this->isNew();
        if (null === $this->collChapters || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collChapters) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getChapters());
            }

            $query = ChildChapterQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByBook($this)
                ->count($con);
        }

        return count($this->collChapters);
    }

    /**
     * Method called to associate a ChildChapter object to this object
     * through the ChildChapter foreign key attribute.
     *
     * @param  ChildChapter $l ChildChapter
     * @return $this|\Book The current object (for fluent API support)
     */
    public function addChapter(ChildChapter $l)
    {
        if ($this->collChapters === null) {
            $this->initChapters();
            $this->collChaptersPartial = true;
        }

        if (!$this->collChapters->contains($l)) {
            $this->doAddChapter($l);

            if ($this->chaptersScheduledForDeletion and $this->chaptersScheduledForDeletion->contains($l)) {
                $this->chaptersScheduledForDeletion->remove($this->chaptersScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildChapter $chapter The ChildChapter object to add.
     */
    protected function doAddChapter(ChildChapter $chapter)
    {
        $this->collChapters[]= $chapter;
        $chapter->setBook($this);
    }

    /**
     * @param  ChildChapter $chapter The ChildChapter object to remove.
     * @return $this|ChildBook The current object (for fluent API support)
     */
    public function removeChapter(ChildChapter $chapter)
    {
        if ($this->getChapters()->contains($chapter)) {
            $pos = $this->collChapters->search($chapter);
            $this->collChapters->remove($pos);
            if (null === $this->chaptersScheduledForDeletion) {
                $this->chaptersScheduledForDeletion = clone $this->collChapters;
                $this->chaptersScheduledForDeletion->clear();
            }
            $this->chaptersScheduledForDeletion[]= clone $chapter;
            $chapter->setBook(null);
        }

        return $this;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        if (null !== $this->aLanguage) {
            $this->aLanguage->removeBook($this);
        }
        $this->id = null;
        $this->title = null;
        $this->subtitle = null;
        $this->slug = null;
        $this->author = null;
        $this->dedication = null;
        $this->language_id = null;
        $this->publisher = null;
        $this->year = null;
        $this->isbn = null;
        $this->extra_info = null;
        $this->cover_image = null;
        $this->created_at = null;
        $this->updated_at = null;
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
            if ($this->collChapters) {
                foreach ($this->collChapters as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collChapters = null;
        $this->aLanguage = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(BookTableMap::DEFAULT_STRING_FORMAT);
    }

    // single_image_upload behavior

            ///////////////////////////////////////////////////////////////////////////
            private function uploadImage() {
                $uploadFile = $_FILES["image"] ?? NULL;

                // if there’s no image to be uploaded, continue with the saving
                if ( ! $uploadFile) {
                    return;
                }

                $doesOldFileExist = (bool) $this->getCoverImageName();
                $noImageToUpload  = (bool) ($uploadFile['error'] === UPLOAD_ERR_NO_FILE);
                    // if image is not required, empty image set should just be ignored
                    if ($noImageToUpload) {
                        return;
                    }
                $minSize = 0;
                $maxSize = 2;

                // get the old image src to delete it in case the new upload is successful
                $oldImageName = $this->getCoverImageName();

                // try to save image; if successfully – delete old image
                try {
                    $uploadPath = rtrim($this->getUploadPath(true), "/");
                    $image      = new \ImageUpload($uploadFile, $uploadPath, [$minSize, $maxSize]);

                    if ($image->upload()) {
                        $this->deleteImage($oldImageName);
                        $this->setCoverImage($image->getJson());
                    }
                }

                // if image upload fails, add error message as a validation failure
                // and abort the save
                catch (Exception $e) {
                    $this->addValidationFailure("image", $e->getMessage());
                    throw $e;
                }
            }

            ///////////////////////////////////////////////////////////////////////////
            // get the image JSON decoded
            public function getCoverImageData(): ?array {
                $json = $this->getCoverImage();

                if ( ! $json) {
                    return NULL;
                }

                return \json_decode($json, true);

            }

            ///////////////////////////////////////////////////////////////////////////
            // get the name of the image
            public function getCoverImageName(): ?string {
                $json = $this->getCoverImage();

                if ( ! $json) {
                    return NULL;
                }

                $data = json_decode($json);

                return $data->name;
            }

            ///////////////////////////////////////////////////////////////////////////
            // get the image path
            public function getCoverImageSrc(bool $internal = false): ?string {
                $name = $this->getCoverImageName();

                if ( ! $name) {
                    return NULL;
                }

                return $this->getUploadPath($internal) . $name;
            }

            ///////////////////////////////////////////////////////////////////////////
            public function deleteImage(string $name = NULL): bool {
                // if no name is passed, get current name
                $filename = $name ?? $this->getCoverImageName();

                if ( ! $filename) {
                    return false;
                }

                $fullpath = $this->getUploadPath(true) . $filename;

                @unlink($fullpath);

                // if file still exists, return false
                return ( ! is_file($fullpath));
            }

            ///////////////////////////////////////////////////////////////////////////
            // internal URL is used for deleting old images,
            // otherwise public URL is generated for displaying images
            private function getUploadPath(bool $internal = false): string {
                $prefix = $internal ? (ROOT_INTER . "public/") : ROOT;

                return $prefix . "img/uploads/books/";
            }

            ///////////////////////////////////////////////////////////////////////////
            private function addValidationFailure(string $field, string $message) {
                $failure = new \Symfony\Component\Validator\ConstraintViolation($message, NULL, [], NULL, "$field", NULL);
                $this->validationFailures[] = $failure;
            }
    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     $this|ChildBook The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[BookTableMap::COL_UPDATED_AT] = true;

        return $this;
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
        $metadata->addPropertyConstraint('slug', new Unique(array ('message' => 'A book with this slug already exists.',)));
        $metadata->addPropertyConstraint('slug', new Regex(array ('pattern' => '/^[a-z0-9\\-]+$/','message' => 'Please use only lowercase latin letters and dashes.',)));
        $metadata->addPropertyConstraint('slug', new Regex(array ('pattern' => '/^(?!add$)[a-z0-9\\-]+$/','message' => 'Reserved words are not allowed.',)));
        $metadata->addPropertyConstraint('language_id', new NotBlank(array ('allowNull' => false,'message' => 'Please select a language from the dropdown menu.',)));
        $metadata->addPropertyConstraint('language_id', new GreaterThan(array ('value' => 0,'message' => 'Please select a language from the dropdown menu.',)));
        $metadata->addPropertyConstraint('author', new Length(array ('max' => 255,)));
        $metadata->addPropertyConstraint('dedication', new Length(array ('max' => 255,)));
        $metadata->addPropertyConstraint('publisher', new Length(array ('max' => 255,)));
        $metadata->addPropertyConstraint('isbn', new Length(array ('max' => 255,)));
        $metadata->addPropertyConstraint('extra_info', new Length(array ('max' => 65535,)));
        $metadata->addPropertyConstraint('isbn', new Isbn());
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
            if (method_exists($this->aLanguage, 'validate')) {
                if (!$this->aLanguage->validate($validator)) {
                    $failureMap->addAll($this->aLanguage->getValidationFailures());
                }
            }

            $retval = $validator->validate($this);
            if (count($retval) > 0) {
                $failureMap->addAll($retval);
            }

            if (null !== $this->collChapters) {
                foreach ($this->collChapters as $referrerFK) {
                    if (method_exists($referrerFK, 'validate')) {
                        if (!$referrerFK->validate($validator)) {
                            $failureMap->addAll($referrerFK->getValidationFailures());
                        }
                    }
                }
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
