<?php

namespace FeatureType\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use FeatureType\Model\FeatureFeatureType as ChildFeatureFeatureType;
use FeatureType\Model\FeatureFeatureTypeQuery as ChildFeatureFeatureTypeQuery;
use FeatureType\Model\FeatureType as ChildFeatureType;
use FeatureType\Model\FeatureTypeI18n as ChildFeatureTypeI18n;
use FeatureType\Model\FeatureTypeI18nQuery as ChildFeatureTypeI18nQuery;
use FeatureType\Model\FeatureTypeQuery as ChildFeatureTypeQuery;
use FeatureType\Model\Map\FeatureTypeTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\DefaultTranslator;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;

abstract class FeatureType implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\FeatureType\\Model\\Map\\FeatureTypeTableMap';


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
     * @var        int
     */
    protected $id;

    /**
     * The value for the slug field.
     * @var        string
     */
    protected $slug;

    /**
     * The value for the has_feature_av_value field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $has_feature_av_value;

    /**
     * The value for the is_multilingual_feature_av_value field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $is_multilingual_feature_av_value;

    /**
     * The value for the pattern field.
     * @var        string
     */
    protected $pattern;

    /**
     * The value for the css_class field.
     * @var        string
     */
    protected $css_class;

    /**
     * The value for the input_type field.
     * @var        string
     */
    protected $input_type;

    /**
     * The value for the max field.
     * @var        double
     */
    protected $max;

    /**
     * The value for the min field.
     * @var        double
     */
    protected $min;

    /**
     * The value for the step field.
     * @var        double
     */
    protected $step;

    /**
     * The value for the image_max_width field.
     * @var        double
     */
    protected $image_max_width;

    /**
     * The value for the image_max_height field.
     * @var        double
     */
    protected $image_max_height;

    /**
     * The value for the image_ratio field.
     * @var        double
     */
    protected $image_ratio;

    /**
     * The value for the created_at field.
     * @var        string
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     * @var        string
     */
    protected $updated_at;

    /**
     * @var        ObjectCollection|ChildFeatureFeatureType[] Collection to store aggregation of ChildFeatureFeatureType objects.
     */
    protected $collFeatureFeatureTypes;
    protected $collFeatureFeatureTypesPartial;

    /**
     * @var        ObjectCollection|ChildFeatureTypeI18n[] Collection to store aggregation of ChildFeatureTypeI18n objects.
     */
    protected $collFeatureTypeI18ns;
    protected $collFeatureTypeI18nsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    // i18n behavior

    /**
     * Current locale
     * @var        string
     */
    protected $currentLocale = 'en_US';

    /**
     * Current translation objects
     * @var        array[ChildFeatureTypeI18n]
     */
    protected $currentTranslations;

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
     * @var ObjectCollection
     */
    protected $featureFeatureTypesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $featureTypeI18nsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->has_feature_av_value = 0;
        $this->is_multilingual_feature_av_value = 0;
    }

    /**
     * Initializes internal state of FeatureType\Model\Base\FeatureType object.
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
        $this->new = (Boolean) $b;
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
        $this->deleted = (Boolean) $b;
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
     * Compares this with another <code>FeatureType</code> instance.  If
     * <code>obj</code> is an instance of <code>FeatureType</code>, delegates to
     * <code>equals(FeatureType)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
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
     * @return FeatureType The current object, for fluid interface
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
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return FeatureType The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);

        return $this;
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

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [slug] column value.
     *
     * @return   string
     */
    public function getSlug()
    {

        return $this->slug;
    }

    /**
     * Get the [has_feature_av_value] column value.
     *
     * @return   int
     */
    public function getHasFeatureAvValue()
    {

        return $this->has_feature_av_value;
    }

    /**
     * Get the [is_multilingual_feature_av_value] column value.
     *
     * @return   int
     */
    public function getIsMultilingualFeatureAvValue()
    {

        return $this->is_multilingual_feature_av_value;
    }

    /**
     * Get the [pattern] column value.
     *
     * @return   string
     */
    public function getPattern()
    {

        return $this->pattern;
    }

    /**
     * Get the [css_class] column value.
     *
     * @return   string
     */
    public function getCssClass()
    {

        return $this->css_class;
    }

    /**
     * Get the [input_type] column value.
     *
     * @return   string
     */
    public function getInputType()
    {

        return $this->input_type;
    }

    /**
     * Get the [max] column value.
     *
     * @return   double
     */
    public function getMax()
    {

        return $this->max;
    }

    /**
     * Get the [min] column value.
     *
     * @return   double
     */
    public function getMin()
    {

        return $this->min;
    }

    /**
     * Get the [step] column value.
     *
     * @return   double
     */
    public function getStep()
    {

        return $this->step;
    }

    /**
     * Get the [image_max_width] column value.
     *
     * @return   double
     */
    public function getImageMaxWidth()
    {

        return $this->image_max_width;
    }

    /**
     * Get the [image_max_height] column value.
     *
     * @return   double
     */
    public function getImageMaxHeight()
    {

        return $this->image_max_height;
    }

    /**
     * Get the [image_ratio] column value.
     *
     * @return   double
     */
    public function getImageRatio()
    {

        return $this->image_ratio;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at instanceof \DateTime ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at instanceof \DateTime ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[FeatureTypeTableMap::ID] = true;
        }


        return $this;
    } // setId()

    /**
     * Set the value of [slug] column.
     *
     * @param      string $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setSlug($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->slug !== $v) {
            $this->slug = $v;
            $this->modifiedColumns[FeatureTypeTableMap::SLUG] = true;
        }


        return $this;
    } // setSlug()

    /**
     * Set the value of [has_feature_av_value] column.
     *
     * @param      int $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setHasFeatureAvValue($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->has_feature_av_value !== $v) {
            $this->has_feature_av_value = $v;
            $this->modifiedColumns[FeatureTypeTableMap::HAS_FEATURE_AV_VALUE] = true;
        }


        return $this;
    } // setHasFeatureAvValue()

    /**
     * Set the value of [is_multilingual_feature_av_value] column.
     *
     * @param      int $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setIsMultilingualFeatureAvValue($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->is_multilingual_feature_av_value !== $v) {
            $this->is_multilingual_feature_av_value = $v;
            $this->modifiedColumns[FeatureTypeTableMap::IS_MULTILINGUAL_FEATURE_AV_VALUE] = true;
        }


        return $this;
    } // setIsMultilingualFeatureAvValue()

    /**
     * Set the value of [pattern] column.
     *
     * @param      string $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setPattern($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->pattern !== $v) {
            $this->pattern = $v;
            $this->modifiedColumns[FeatureTypeTableMap::PATTERN] = true;
        }


        return $this;
    } // setPattern()

    /**
     * Set the value of [css_class] column.
     *
     * @param      string $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setCssClass($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->css_class !== $v) {
            $this->css_class = $v;
            $this->modifiedColumns[FeatureTypeTableMap::CSS_CLASS] = true;
        }


        return $this;
    } // setCssClass()

    /**
     * Set the value of [input_type] column.
     *
     * @param      string $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setInputType($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->input_type !== $v) {
            $this->input_type = $v;
            $this->modifiedColumns[FeatureTypeTableMap::INPUT_TYPE] = true;
        }


        return $this;
    } // setInputType()

    /**
     * Set the value of [max] column.
     *
     * @param      double $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setMax($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->max !== $v) {
            $this->max = $v;
            $this->modifiedColumns[FeatureTypeTableMap::MAX] = true;
        }


        return $this;
    } // setMax()

    /**
     * Set the value of [min] column.
     *
     * @param      double $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setMin($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->min !== $v) {
            $this->min = $v;
            $this->modifiedColumns[FeatureTypeTableMap::MIN] = true;
        }


        return $this;
    } // setMin()

    /**
     * Set the value of [step] column.
     *
     * @param      double $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setStep($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->step !== $v) {
            $this->step = $v;
            $this->modifiedColumns[FeatureTypeTableMap::STEP] = true;
        }


        return $this;
    } // setStep()

    /**
     * Set the value of [image_max_width] column.
     *
     * @param      double $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setImageMaxWidth($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->image_max_width !== $v) {
            $this->image_max_width = $v;
            $this->modifiedColumns[FeatureTypeTableMap::IMAGE_MAX_WIDTH] = true;
        }


        return $this;
    } // setImageMaxWidth()

    /**
     * Set the value of [image_max_height] column.
     *
     * @param      double $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setImageMaxHeight($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->image_max_height !== $v) {
            $this->image_max_height = $v;
            $this->modifiedColumns[FeatureTypeTableMap::IMAGE_MAX_HEIGHT] = true;
        }


        return $this;
    } // setImageMaxHeight()

    /**
     * Set the value of [image_ratio] column.
     *
     * @param      double $v new value
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setImageRatio($v)
    {
        if ($v !== null) {
            $v = (double) $v;
        }

        if ($this->image_ratio !== $v) {
            $this->image_ratio = $v;
            $this->modifiedColumns[FeatureTypeTableMap::IMAGE_RATIO] = true;
        }


        return $this;
    } // setImageRatio()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($dt !== $this->created_at) {
                $this->created_at = $dt;
                $this->modifiedColumns[FeatureTypeTableMap::CREATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($dt !== $this->updated_at) {
                $this->updated_at = $dt;
                $this->modifiedColumns[FeatureTypeTableMap::UPDATED_AT] = true;
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
            if ($this->has_feature_av_value !== 0) {
                return false;
            }

            if ($this->is_multilingual_feature_av_value !== 0) {
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
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : FeatureTypeTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : FeatureTypeTableMap::translateFieldName('Slug', TableMap::TYPE_PHPNAME, $indexType)];
            $this->slug = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : FeatureTypeTableMap::translateFieldName('HasFeatureAvValue', TableMap::TYPE_PHPNAME, $indexType)];
            $this->has_feature_av_value = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : FeatureTypeTableMap::translateFieldName('IsMultilingualFeatureAvValue', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_multilingual_feature_av_value = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : FeatureTypeTableMap::translateFieldName('Pattern', TableMap::TYPE_PHPNAME, $indexType)];
            $this->pattern = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : FeatureTypeTableMap::translateFieldName('CssClass', TableMap::TYPE_PHPNAME, $indexType)];
            $this->css_class = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : FeatureTypeTableMap::translateFieldName('InputType', TableMap::TYPE_PHPNAME, $indexType)];
            $this->input_type = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : FeatureTypeTableMap::translateFieldName('Max', TableMap::TYPE_PHPNAME, $indexType)];
            $this->max = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : FeatureTypeTableMap::translateFieldName('Min', TableMap::TYPE_PHPNAME, $indexType)];
            $this->min = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : FeatureTypeTableMap::translateFieldName('Step', TableMap::TYPE_PHPNAME, $indexType)];
            $this->step = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 10 + $startcol : FeatureTypeTableMap::translateFieldName('ImageMaxWidth', TableMap::TYPE_PHPNAME, $indexType)];
            $this->image_max_width = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 11 + $startcol : FeatureTypeTableMap::translateFieldName('ImageMaxHeight', TableMap::TYPE_PHPNAME, $indexType)];
            $this->image_max_height = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 12 + $startcol : FeatureTypeTableMap::translateFieldName('ImageRatio', TableMap::TYPE_PHPNAME, $indexType)];
            $this->image_ratio = (null !== $col) ? (double) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 13 + $startcol : FeatureTypeTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 14 + $startcol : FeatureTypeTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 15; // 15 = FeatureTypeTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \FeatureType\Model\FeatureType object", 0, $e);
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
            $con = Propel::getServiceContainer()->getReadConnection(FeatureTypeTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildFeatureTypeQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collFeatureFeatureTypes = null;

            $this->collFeatureTypeI18ns = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see FeatureType::setDeleted()
     * @see FeatureType::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(FeatureTypeTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildFeatureTypeQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
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

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(FeatureTypeTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                if (!$this->isColumnModified(FeatureTypeTableMap::CREATED_AT)) {
                    $this->setCreatedAt(time());
                }
                if (!$this->isColumnModified(FeatureTypeTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(FeatureTypeTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
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
                FeatureTypeTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
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

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->featureFeatureTypesScheduledForDeletion !== null) {
                if (!$this->featureFeatureTypesScheduledForDeletion->isEmpty()) {
                    \FeatureType\Model\FeatureFeatureTypeQuery::create()
                        ->filterByPrimaryKeys($this->featureFeatureTypesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->featureFeatureTypesScheduledForDeletion = null;
                }
            }

                if ($this->collFeatureFeatureTypes !== null) {
            foreach ($this->collFeatureFeatureTypes as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->featureTypeI18nsScheduledForDeletion !== null) {
                if (!$this->featureTypeI18nsScheduledForDeletion->isEmpty()) {
                    \FeatureType\Model\FeatureTypeI18nQuery::create()
                        ->filterByPrimaryKeys($this->featureTypeI18nsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->featureTypeI18nsScheduledForDeletion = null;
                }
            }

                if ($this->collFeatureTypeI18ns !== null) {
            foreach ($this->collFeatureTypeI18ns as $referrerFK) {
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

        $this->modifiedColumns[FeatureTypeTableMap::ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . FeatureTypeTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(FeatureTypeTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::SLUG)) {
            $modifiedColumns[':p' . $index++]  = 'SLUG';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::HAS_FEATURE_AV_VALUE)) {
            $modifiedColumns[':p' . $index++]  = 'HAS_FEATURE_AV_VALUE';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::IS_MULTILINGUAL_FEATURE_AV_VALUE)) {
            $modifiedColumns[':p' . $index++]  = 'IS_MULTILINGUAL_FEATURE_AV_VALUE';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::PATTERN)) {
            $modifiedColumns[':p' . $index++]  = 'PATTERN';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::CSS_CLASS)) {
            $modifiedColumns[':p' . $index++]  = 'CSS_CLASS';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::INPUT_TYPE)) {
            $modifiedColumns[':p' . $index++]  = 'INPUT_TYPE';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::MAX)) {
            $modifiedColumns[':p' . $index++]  = 'MAX';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::MIN)) {
            $modifiedColumns[':p' . $index++]  = 'MIN';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::STEP)) {
            $modifiedColumns[':p' . $index++]  = 'STEP';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::IMAGE_MAX_WIDTH)) {
            $modifiedColumns[':p' . $index++]  = 'IMAGE_MAX_WIDTH';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::IMAGE_MAX_HEIGHT)) {
            $modifiedColumns[':p' . $index++]  = 'IMAGE_MAX_HEIGHT';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::IMAGE_RATIO)) {
            $modifiedColumns[':p' . $index++]  = 'IMAGE_RATIO';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'CREATED_AT';
        }
        if ($this->isColumnModified(FeatureTypeTableMap::UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'UPDATED_AT';
        }

        $sql = sprintf(
            'INSERT INTO feature_type (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'SLUG':
                        $stmt->bindValue($identifier, $this->slug, PDO::PARAM_STR);
                        break;
                    case 'HAS_FEATURE_AV_VALUE':
                        $stmt->bindValue($identifier, $this->has_feature_av_value, PDO::PARAM_INT);
                        break;
                    case 'IS_MULTILINGUAL_FEATURE_AV_VALUE':
                        $stmt->bindValue($identifier, $this->is_multilingual_feature_av_value, PDO::PARAM_INT);
                        break;
                    case 'PATTERN':
                        $stmt->bindValue($identifier, $this->pattern, PDO::PARAM_STR);
                        break;
                    case 'CSS_CLASS':
                        $stmt->bindValue($identifier, $this->css_class, PDO::PARAM_STR);
                        break;
                    case 'INPUT_TYPE':
                        $stmt->bindValue($identifier, $this->input_type, PDO::PARAM_STR);
                        break;
                    case 'MAX':
                        $stmt->bindValue($identifier, $this->max, PDO::PARAM_STR);
                        break;
                    case 'MIN':
                        $stmt->bindValue($identifier, $this->min, PDO::PARAM_STR);
                        break;
                    case 'STEP':
                        $stmt->bindValue($identifier, $this->step, PDO::PARAM_STR);
                        break;
                    case 'IMAGE_MAX_WIDTH':
                        $stmt->bindValue($identifier, $this->image_max_width, PDO::PARAM_STR);
                        break;
                    case 'IMAGE_MAX_HEIGHT':
                        $stmt->bindValue($identifier, $this->image_max_height, PDO::PARAM_STR);
                        break;
                    case 'IMAGE_RATIO':
                        $stmt->bindValue($identifier, $this->image_ratio, PDO::PARAM_STR);
                        break;
                    case 'CREATED_AT':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'UPDATED_AT':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
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
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = FeatureTypeTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getSlug();
                break;
            case 2:
                return $this->getHasFeatureAvValue();
                break;
            case 3:
                return $this->getIsMultilingualFeatureAvValue();
                break;
            case 4:
                return $this->getPattern();
                break;
            case 5:
                return $this->getCssClass();
                break;
            case 6:
                return $this->getInputType();
                break;
            case 7:
                return $this->getMax();
                break;
            case 8:
                return $this->getMin();
                break;
            case 9:
                return $this->getStep();
                break;
            case 10:
                return $this->getImageMaxWidth();
                break;
            case 11:
                return $this->getImageMaxHeight();
                break;
            case 12:
                return $this->getImageRatio();
                break;
            case 13:
                return $this->getCreatedAt();
                break;
            case 14:
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
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
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
        if (isset($alreadyDumpedObjects['FeatureType'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['FeatureType'][$this->getPrimaryKey()] = true;
        $keys = FeatureTypeTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getSlug(),
            $keys[2] => $this->getHasFeatureAvValue(),
            $keys[3] => $this->getIsMultilingualFeatureAvValue(),
            $keys[4] => $this->getPattern(),
            $keys[5] => $this->getCssClass(),
            $keys[6] => $this->getInputType(),
            $keys[7] => $this->getMax(),
            $keys[8] => $this->getMin(),
            $keys[9] => $this->getStep(),
            $keys[10] => $this->getImageMaxWidth(),
            $keys[11] => $this->getImageMaxHeight(),
            $keys[12] => $this->getImageRatio(),
            $keys[13] => $this->getCreatedAt(),
            $keys[14] => $this->getUpdatedAt(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collFeatureFeatureTypes) {
                $result['FeatureFeatureTypes'] = $this->collFeatureFeatureTypes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collFeatureTypeI18ns) {
                $result['FeatureTypeI18ns'] = $this->collFeatureTypeI18ns->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = FeatureTypeTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setSlug($value);
                break;
            case 2:
                $this->setHasFeatureAvValue($value);
                break;
            case 3:
                $this->setIsMultilingualFeatureAvValue($value);
                break;
            case 4:
                $this->setPattern($value);
                break;
            case 5:
                $this->setCssClass($value);
                break;
            case 6:
                $this->setInputType($value);
                break;
            case 7:
                $this->setMax($value);
                break;
            case 8:
                $this->setMin($value);
                break;
            case 9:
                $this->setStep($value);
                break;
            case 10:
                $this->setImageMaxWidth($value);
                break;
            case 11:
                $this->setImageMaxHeight($value);
                break;
            case 12:
                $this->setImageRatio($value);
                break;
            case 13:
                $this->setCreatedAt($value);
                break;
            case 14:
                $this->setUpdatedAt($value);
                break;
        } // switch()
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
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = FeatureTypeTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setSlug($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setHasFeatureAvValue($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setIsMultilingualFeatureAvValue($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setPattern($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setCssClass($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setInputType($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setMax($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setMin($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setStep($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setImageMaxWidth($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setImageMaxHeight($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setImageRatio($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setCreatedAt($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setUpdatedAt($arr[$keys[14]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(FeatureTypeTableMap::DATABASE_NAME);

        if ($this->isColumnModified(FeatureTypeTableMap::ID)) $criteria->add(FeatureTypeTableMap::ID, $this->id);
        if ($this->isColumnModified(FeatureTypeTableMap::SLUG)) $criteria->add(FeatureTypeTableMap::SLUG, $this->slug);
        if ($this->isColumnModified(FeatureTypeTableMap::HAS_FEATURE_AV_VALUE)) $criteria->add(FeatureTypeTableMap::HAS_FEATURE_AV_VALUE, $this->has_feature_av_value);
        if ($this->isColumnModified(FeatureTypeTableMap::IS_MULTILINGUAL_FEATURE_AV_VALUE)) $criteria->add(FeatureTypeTableMap::IS_MULTILINGUAL_FEATURE_AV_VALUE, $this->is_multilingual_feature_av_value);
        if ($this->isColumnModified(FeatureTypeTableMap::PATTERN)) $criteria->add(FeatureTypeTableMap::PATTERN, $this->pattern);
        if ($this->isColumnModified(FeatureTypeTableMap::CSS_CLASS)) $criteria->add(FeatureTypeTableMap::CSS_CLASS, $this->css_class);
        if ($this->isColumnModified(FeatureTypeTableMap::INPUT_TYPE)) $criteria->add(FeatureTypeTableMap::INPUT_TYPE, $this->input_type);
        if ($this->isColumnModified(FeatureTypeTableMap::MAX)) $criteria->add(FeatureTypeTableMap::MAX, $this->max);
        if ($this->isColumnModified(FeatureTypeTableMap::MIN)) $criteria->add(FeatureTypeTableMap::MIN, $this->min);
        if ($this->isColumnModified(FeatureTypeTableMap::STEP)) $criteria->add(FeatureTypeTableMap::STEP, $this->step);
        if ($this->isColumnModified(FeatureTypeTableMap::IMAGE_MAX_WIDTH)) $criteria->add(FeatureTypeTableMap::IMAGE_MAX_WIDTH, $this->image_max_width);
        if ($this->isColumnModified(FeatureTypeTableMap::IMAGE_MAX_HEIGHT)) $criteria->add(FeatureTypeTableMap::IMAGE_MAX_HEIGHT, $this->image_max_height);
        if ($this->isColumnModified(FeatureTypeTableMap::IMAGE_RATIO)) $criteria->add(FeatureTypeTableMap::IMAGE_RATIO, $this->image_ratio);
        if ($this->isColumnModified(FeatureTypeTableMap::CREATED_AT)) $criteria->add(FeatureTypeTableMap::CREATED_AT, $this->created_at);
        if ($this->isColumnModified(FeatureTypeTableMap::UPDATED_AT)) $criteria->add(FeatureTypeTableMap::UPDATED_AT, $this->updated_at);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(FeatureTypeTableMap::DATABASE_NAME);
        $criteria->add(FeatureTypeTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
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
     * @param      object $copyObj An object of \FeatureType\Model\FeatureType (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setSlug($this->getSlug());
        $copyObj->setHasFeatureAvValue($this->getHasFeatureAvValue());
        $copyObj->setIsMultilingualFeatureAvValue($this->getIsMultilingualFeatureAvValue());
        $copyObj->setPattern($this->getPattern());
        $copyObj->setCssClass($this->getCssClass());
        $copyObj->setInputType($this->getInputType());
        $copyObj->setMax($this->getMax());
        $copyObj->setMin($this->getMin());
        $copyObj->setStep($this->getStep());
        $copyObj->setImageMaxWidth($this->getImageMaxWidth());
        $copyObj->setImageMaxHeight($this->getImageMaxHeight());
        $copyObj->setImageRatio($this->getImageRatio());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getFeatureFeatureTypes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addFeatureFeatureType($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getFeatureTypeI18ns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addFeatureTypeI18n($relObj->copy($deepCopy));
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
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return                 \FeatureType\Model\FeatureType Clone of current object.
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
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('FeatureFeatureType' == $relationName) {
            return $this->initFeatureFeatureTypes();
        }
        if ('FeatureTypeI18n' == $relationName) {
            return $this->initFeatureTypeI18ns();
        }
    }

    /**
     * Clears out the collFeatureFeatureTypes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addFeatureFeatureTypes()
     */
    public function clearFeatureFeatureTypes()
    {
        $this->collFeatureFeatureTypes = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collFeatureFeatureTypes collection loaded partially.
     */
    public function resetPartialFeatureFeatureTypes($v = true)
    {
        $this->collFeatureFeatureTypesPartial = $v;
    }

    /**
     * Initializes the collFeatureFeatureTypes collection.
     *
     * By default this just sets the collFeatureFeatureTypes collection to an empty array (like clearcollFeatureFeatureTypes());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initFeatureFeatureTypes($overrideExisting = true)
    {
        if (null !== $this->collFeatureFeatureTypes && !$overrideExisting) {
            return;
        }
        $this->collFeatureFeatureTypes = new ObjectCollection();
        $this->collFeatureFeatureTypes->setModel('\FeatureType\Model\FeatureFeatureType');
    }

    /**
     * Gets an array of ChildFeatureFeatureType objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildFeatureType is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildFeatureFeatureType[] List of ChildFeatureFeatureType objects
     * @throws PropelException
     */
    public function getFeatureFeatureTypes($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collFeatureFeatureTypesPartial && !$this->isNew();
        if (null === $this->collFeatureFeatureTypes || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collFeatureFeatureTypes) {
                // return empty collection
                $this->initFeatureFeatureTypes();
            } else {
                $collFeatureFeatureTypes = ChildFeatureFeatureTypeQuery::create(null, $criteria)
                    ->filterByFeatureType($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collFeatureFeatureTypesPartial && count($collFeatureFeatureTypes)) {
                        $this->initFeatureFeatureTypes(false);

                        foreach ($collFeatureFeatureTypes as $obj) {
                            if (false == $this->collFeatureFeatureTypes->contains($obj)) {
                                $this->collFeatureFeatureTypes->append($obj);
                            }
                        }

                        $this->collFeatureFeatureTypesPartial = true;
                    }

                    reset($collFeatureFeatureTypes);

                    return $collFeatureFeatureTypes;
                }

                if ($partial && $this->collFeatureFeatureTypes) {
                    foreach ($this->collFeatureFeatureTypes as $obj) {
                        if ($obj->isNew()) {
                            $collFeatureFeatureTypes[] = $obj;
                        }
                    }
                }

                $this->collFeatureFeatureTypes = $collFeatureFeatureTypes;
                $this->collFeatureFeatureTypesPartial = false;
            }
        }

        return $this->collFeatureFeatureTypes;
    }

    /**
     * Sets a collection of FeatureFeatureType objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $featureFeatureTypes A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildFeatureType The current object (for fluent API support)
     */
    public function setFeatureFeatureTypes(Collection $featureFeatureTypes, ConnectionInterface $con = null)
    {
        $featureFeatureTypesToDelete = $this->getFeatureFeatureTypes(new Criteria(), $con)->diff($featureFeatureTypes);


        $this->featureFeatureTypesScheduledForDeletion = $featureFeatureTypesToDelete;

        foreach ($featureFeatureTypesToDelete as $featureFeatureTypeRemoved) {
            $featureFeatureTypeRemoved->setFeatureType(null);
        }

        $this->collFeatureFeatureTypes = null;
        foreach ($featureFeatureTypes as $featureFeatureType) {
            $this->addFeatureFeatureType($featureFeatureType);
        }

        $this->collFeatureFeatureTypes = $featureFeatureTypes;
        $this->collFeatureFeatureTypesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related FeatureFeatureType objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related FeatureFeatureType objects.
     * @throws PropelException
     */
    public function countFeatureFeatureTypes(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collFeatureFeatureTypesPartial && !$this->isNew();
        if (null === $this->collFeatureFeatureTypes || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collFeatureFeatureTypes) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getFeatureFeatureTypes());
            }

            $query = ChildFeatureFeatureTypeQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByFeatureType($this)
                ->count($con);
        }

        return count($this->collFeatureFeatureTypes);
    }

    /**
     * Method called to associate a ChildFeatureFeatureType object to this object
     * through the ChildFeatureFeatureType foreign key attribute.
     *
     * @param    ChildFeatureFeatureType $l ChildFeatureFeatureType
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function addFeatureFeatureType(ChildFeatureFeatureType $l)
    {
        if ($this->collFeatureFeatureTypes === null) {
            $this->initFeatureFeatureTypes();
            $this->collFeatureFeatureTypesPartial = true;
        }

        if (!in_array($l, $this->collFeatureFeatureTypes->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddFeatureFeatureType($l);
        }

        return $this;
    }

    /**
     * @param FeatureFeatureType $featureFeatureType The featureFeatureType object to add.
     */
    protected function doAddFeatureFeatureType($featureFeatureType)
    {
        $this->collFeatureFeatureTypes[]= $featureFeatureType;
        $featureFeatureType->setFeatureType($this);
    }

    /**
     * @param  FeatureFeatureType $featureFeatureType The featureFeatureType object to remove.
     * @return ChildFeatureType The current object (for fluent API support)
     */
    public function removeFeatureFeatureType($featureFeatureType)
    {
        if ($this->getFeatureFeatureTypes()->contains($featureFeatureType)) {
            $this->collFeatureFeatureTypes->remove($this->collFeatureFeatureTypes->search($featureFeatureType));
            if (null === $this->featureFeatureTypesScheduledForDeletion) {
                $this->featureFeatureTypesScheduledForDeletion = clone $this->collFeatureFeatureTypes;
                $this->featureFeatureTypesScheduledForDeletion->clear();
            }
            $this->featureFeatureTypesScheduledForDeletion[]= clone $featureFeatureType;
            $featureFeatureType->setFeatureType(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this FeatureType is new, it will return
     * an empty collection; or if this FeatureType has previously
     * been saved, it will retrieve related FeatureFeatureTypes from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in FeatureType.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return Collection|ChildFeatureFeatureType[] List of ChildFeatureFeatureType objects
     */
    public function getFeatureFeatureTypesJoinFeature($criteria = null, $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildFeatureFeatureTypeQuery::create(null, $criteria);
        $query->joinWith('Feature', $joinBehavior);

        return $this->getFeatureFeatureTypes($query, $con);
    }

    /**
     * Clears out the collFeatureTypeI18ns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addFeatureTypeI18ns()
     */
    public function clearFeatureTypeI18ns()
    {
        $this->collFeatureTypeI18ns = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collFeatureTypeI18ns collection loaded partially.
     */
    public function resetPartialFeatureTypeI18ns($v = true)
    {
        $this->collFeatureTypeI18nsPartial = $v;
    }

    /**
     * Initializes the collFeatureTypeI18ns collection.
     *
     * By default this just sets the collFeatureTypeI18ns collection to an empty array (like clearcollFeatureTypeI18ns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initFeatureTypeI18ns($overrideExisting = true)
    {
        if (null !== $this->collFeatureTypeI18ns && !$overrideExisting) {
            return;
        }
        $this->collFeatureTypeI18ns = new ObjectCollection();
        $this->collFeatureTypeI18ns->setModel('\FeatureType\Model\FeatureTypeI18n');
    }

    /**
     * Gets an array of ChildFeatureTypeI18n objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildFeatureType is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildFeatureTypeI18n[] List of ChildFeatureTypeI18n objects
     * @throws PropelException
     */
    public function getFeatureTypeI18ns($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collFeatureTypeI18nsPartial && !$this->isNew();
        if (null === $this->collFeatureTypeI18ns || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collFeatureTypeI18ns) {
                // return empty collection
                $this->initFeatureTypeI18ns();
            } else {
                $collFeatureTypeI18ns = ChildFeatureTypeI18nQuery::create(null, $criteria)
                    ->filterByFeatureType($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collFeatureTypeI18nsPartial && count($collFeatureTypeI18ns)) {
                        $this->initFeatureTypeI18ns(false);

                        foreach ($collFeatureTypeI18ns as $obj) {
                            if (false == $this->collFeatureTypeI18ns->contains($obj)) {
                                $this->collFeatureTypeI18ns->append($obj);
                            }
                        }

                        $this->collFeatureTypeI18nsPartial = true;
                    }

                    reset($collFeatureTypeI18ns);

                    return $collFeatureTypeI18ns;
                }

                if ($partial && $this->collFeatureTypeI18ns) {
                    foreach ($this->collFeatureTypeI18ns as $obj) {
                        if ($obj->isNew()) {
                            $collFeatureTypeI18ns[] = $obj;
                        }
                    }
                }

                $this->collFeatureTypeI18ns = $collFeatureTypeI18ns;
                $this->collFeatureTypeI18nsPartial = false;
            }
        }

        return $this->collFeatureTypeI18ns;
    }

    /**
     * Sets a collection of FeatureTypeI18n objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $featureTypeI18ns A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildFeatureType The current object (for fluent API support)
     */
    public function setFeatureTypeI18ns(Collection $featureTypeI18ns, ConnectionInterface $con = null)
    {
        $featureTypeI18nsToDelete = $this->getFeatureTypeI18ns(new Criteria(), $con)->diff($featureTypeI18ns);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->featureTypeI18nsScheduledForDeletion = clone $featureTypeI18nsToDelete;

        foreach ($featureTypeI18nsToDelete as $featureTypeI18nRemoved) {
            $featureTypeI18nRemoved->setFeatureType(null);
        }

        $this->collFeatureTypeI18ns = null;
        foreach ($featureTypeI18ns as $featureTypeI18n) {
            $this->addFeatureTypeI18n($featureTypeI18n);
        }

        $this->collFeatureTypeI18ns = $featureTypeI18ns;
        $this->collFeatureTypeI18nsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related FeatureTypeI18n objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related FeatureTypeI18n objects.
     * @throws PropelException
     */
    public function countFeatureTypeI18ns(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collFeatureTypeI18nsPartial && !$this->isNew();
        if (null === $this->collFeatureTypeI18ns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collFeatureTypeI18ns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getFeatureTypeI18ns());
            }

            $query = ChildFeatureTypeI18nQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByFeatureType($this)
                ->count($con);
        }

        return count($this->collFeatureTypeI18ns);
    }

    /**
     * Method called to associate a ChildFeatureTypeI18n object to this object
     * through the ChildFeatureTypeI18n foreign key attribute.
     *
     * @param    ChildFeatureTypeI18n $l ChildFeatureTypeI18n
     * @return   \FeatureType\Model\FeatureType The current object (for fluent API support)
     */
    public function addFeatureTypeI18n(ChildFeatureTypeI18n $l)
    {
        if ($l && $locale = $l->getLocale()) {
            $this->setLocale($locale);
            $this->currentTranslations[$locale] = $l;
        }
        if ($this->collFeatureTypeI18ns === null) {
            $this->initFeatureTypeI18ns();
            $this->collFeatureTypeI18nsPartial = true;
        }

        if (!in_array($l, $this->collFeatureTypeI18ns->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddFeatureTypeI18n($l);
        }

        return $this;
    }

    /**
     * @param FeatureTypeI18n $featureTypeI18n The featureTypeI18n object to add.
     */
    protected function doAddFeatureTypeI18n($featureTypeI18n)
    {
        $this->collFeatureTypeI18ns[]= $featureTypeI18n;
        $featureTypeI18n->setFeatureType($this);
    }

    /**
     * @param  FeatureTypeI18n $featureTypeI18n The featureTypeI18n object to remove.
     * @return ChildFeatureType The current object (for fluent API support)
     */
    public function removeFeatureTypeI18n($featureTypeI18n)
    {
        if ($this->getFeatureTypeI18ns()->contains($featureTypeI18n)) {
            $this->collFeatureTypeI18ns->remove($this->collFeatureTypeI18ns->search($featureTypeI18n));
            if (null === $this->featureTypeI18nsScheduledForDeletion) {
                $this->featureTypeI18nsScheduledForDeletion = clone $this->collFeatureTypeI18ns;
                $this->featureTypeI18nsScheduledForDeletion->clear();
            }
            $this->featureTypeI18nsScheduledForDeletion[]= clone $featureTypeI18n;
            $featureTypeI18n->setFeatureType(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id = null;
        $this->slug = null;
        $this->has_feature_av_value = null;
        $this->is_multilingual_feature_av_value = null;
        $this->pattern = null;
        $this->css_class = null;
        $this->input_type = null;
        $this->max = null;
        $this->min = null;
        $this->step = null;
        $this->image_max_width = null;
        $this->image_max_height = null;
        $this->image_ratio = null;
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
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collFeatureFeatureTypes) {
                foreach ($this->collFeatureFeatureTypes as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collFeatureTypeI18ns) {
                foreach ($this->collFeatureTypeI18ns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        // i18n behavior
        $this->currentLocale = 'en_US';
        $this->currentTranslations = null;

        $this->collFeatureFeatureTypes = null;
        $this->collFeatureTypeI18ns = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(FeatureTypeTableMap::DEFAULT_STRING_FORMAT);
    }

    // i18n behavior

    /**
     * Sets the locale for translations
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     *
     * @return    ChildFeatureType The current object (for fluent API support)
     */
    public function setLocale($locale = 'en_US')
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Gets the locale for translations
     *
     * @return    string $locale Locale to use for the translation, e.g. 'fr_FR'
     */
    public function getLocale()
    {
        return $this->currentLocale;
    }

    /**
     * Returns the current translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ChildFeatureTypeI18n */
    public function getTranslation($locale = 'en_US', ConnectionInterface $con = null)
    {
        if (!isset($this->currentTranslations[$locale])) {
            if (null !== $this->collFeatureTypeI18ns) {
                foreach ($this->collFeatureTypeI18ns as $translation) {
                    if ($translation->getLocale() == $locale) {
                        $this->currentTranslations[$locale] = $translation;

                        return $translation;
                    }
                }
            }
            if ($this->isNew()) {
                $translation = new ChildFeatureTypeI18n();
                $translation->setLocale($locale);
            } else {
                $translation = ChildFeatureTypeI18nQuery::create()
                    ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                    ->findOneOrCreate($con);
                $this->currentTranslations[$locale] = $translation;
            }
            $this->addFeatureTypeI18n($translation);
        }

        return $this->currentTranslations[$locale];
    }

    /**
     * Remove the translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return    ChildFeatureType The current object (for fluent API support)
     */
    public function removeTranslation($locale = 'en_US', ConnectionInterface $con = null)
    {
        if (!$this->isNew()) {
            ChildFeatureTypeI18nQuery::create()
                ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                ->delete($con);
        }
        if (isset($this->currentTranslations[$locale])) {
            unset($this->currentTranslations[$locale]);
        }
        foreach ($this->collFeatureTypeI18ns as $key => $translation) {
            if ($translation->getLocale() == $locale) {
                unset($this->collFeatureTypeI18ns[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Returns the current translation
     *
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ChildFeatureTypeI18n */
    public function getCurrentTranslation(ConnectionInterface $con = null)
    {
        return $this->getTranslation($this->getLocale(), $con);
    }


        /**
         * Get the [title] column value.
         *
         * @return   string
         */
        public function getTitle()
        {
        return $this->getCurrentTranslation()->getTitle();
    }


        /**
         * Set the value of [title] column.
         *
         * @param      string $v new value
         * @return   \FeatureType\Model\FeatureTypeI18n The current object (for fluent API support)
         */
        public function setTitle($v)
        {    $this->getCurrentTranslation()->setTitle($v);

        return $this;
    }


        /**
         * Get the [description] column value.
         *
         * @return   string
         */
        public function getDescription()
        {
        return $this->getCurrentTranslation()->getDescription();
    }


        /**
         * Set the value of [description] column.
         *
         * @param      string $v new value
         * @return   \FeatureType\Model\FeatureTypeI18n The current object (for fluent API support)
         */
        public function setDescription($v)
        {    $this->getCurrentTranslation()->setDescription($v);

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
        $metadata->addPropertyConstraint('slug', new Regex(array ('pattern' => '/[a-z][a-z_0-9]{3,50}/',)));
    }

    /**
     * Validates the object and all objects related to this table.
     *
     * @see        getValidationFailures()
     * @param      object $validator A Validator class instance
     * @return     boolean Whether all objects pass validation.
     */
    public function validate(Validator $validator = null)
    {
        if (null === $validator) {
            $validator = new Validator(new ClassMetadataFactory(new StaticMethodLoader()), new ConstraintValidatorFactory(), new DefaultTranslator());
        }

        $failureMap = new ConstraintViolationList();

        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;


            $retval = $validator->validate($this);
            if (count($retval) > 0) {
                $failureMap->addAll($retval);
            }

            if (null !== $this->collFeatureFeatureTypes) {
                foreach ($this->collFeatureFeatureTypes as $referrerFK) {
                    if (method_exists($referrerFK, 'validate')) {
                        if (!$referrerFK->validate($validator)) {
                            $failureMap->addAll($referrerFK->getValidationFailures());
                        }
                    }
                }
            }
            if (null !== $this->collFeatureTypeI18ns) {
                foreach ($this->collFeatureTypeI18ns as $referrerFK) {
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
     * @return     object ConstraintViolationList
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     ChildFeatureType The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[FeatureTypeTableMap::UPDATED_AT] = true;

        return $this;
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
