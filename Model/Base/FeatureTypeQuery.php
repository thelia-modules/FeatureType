<?php

namespace FeatureType\Model\Base;

use \Exception;
use \PDO;
use FeatureType\Model\FeatureType as ChildFeatureType;
use FeatureType\Model\FeatureTypeI18nQuery as ChildFeatureTypeI18nQuery;
use FeatureType\Model\FeatureTypeQuery as ChildFeatureTypeQuery;
use FeatureType\Model\Map\FeatureTypeTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'feature_type' table.
 *
 *
 *
 * @method     ChildFeatureTypeQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildFeatureTypeQuery orderBySlug($order = Criteria::ASC) Order by the slug column
 * @method     ChildFeatureTypeQuery orderByHasFeatureAvValue($order = Criteria::ASC) Order by the has_feature_av_value column
 * @method     ChildFeatureTypeQuery orderByIsMultilingualFeatureAvValue($order = Criteria::ASC) Order by the is_multilingual_feature_av_value column
 * @method     ChildFeatureTypeQuery orderByPattern($order = Criteria::ASC) Order by the pattern column
 * @method     ChildFeatureTypeQuery orderByCssClass($order = Criteria::ASC) Order by the css_class column
 * @method     ChildFeatureTypeQuery orderByInputType($order = Criteria::ASC) Order by the input_type column
 * @method     ChildFeatureTypeQuery orderByMax($order = Criteria::ASC) Order by the max column
 * @method     ChildFeatureTypeQuery orderByMin($order = Criteria::ASC) Order by the min column
 * @method     ChildFeatureTypeQuery orderByStep($order = Criteria::ASC) Order by the step column
 * @method     ChildFeatureTypeQuery orderByImageMaxWidth($order = Criteria::ASC) Order by the image_max_width column
 * @method     ChildFeatureTypeQuery orderByImageMaxHeight($order = Criteria::ASC) Order by the image_max_height column
 * @method     ChildFeatureTypeQuery orderByImageRatio($order = Criteria::ASC) Order by the image_ratio column
 * @method     ChildFeatureTypeQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildFeatureTypeQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildFeatureTypeQuery groupById() Group by the id column
 * @method     ChildFeatureTypeQuery groupBySlug() Group by the slug column
 * @method     ChildFeatureTypeQuery groupByHasFeatureAvValue() Group by the has_feature_av_value column
 * @method     ChildFeatureTypeQuery groupByIsMultilingualFeatureAvValue() Group by the is_multilingual_feature_av_value column
 * @method     ChildFeatureTypeQuery groupByPattern() Group by the pattern column
 * @method     ChildFeatureTypeQuery groupByCssClass() Group by the css_class column
 * @method     ChildFeatureTypeQuery groupByInputType() Group by the input_type column
 * @method     ChildFeatureTypeQuery groupByMax() Group by the max column
 * @method     ChildFeatureTypeQuery groupByMin() Group by the min column
 * @method     ChildFeatureTypeQuery groupByStep() Group by the step column
 * @method     ChildFeatureTypeQuery groupByImageMaxWidth() Group by the image_max_width column
 * @method     ChildFeatureTypeQuery groupByImageMaxHeight() Group by the image_max_height column
 * @method     ChildFeatureTypeQuery groupByImageRatio() Group by the image_ratio column
 * @method     ChildFeatureTypeQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildFeatureTypeQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildFeatureTypeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildFeatureTypeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildFeatureTypeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildFeatureTypeQuery leftJoinFeatureFeatureType($relationAlias = null) Adds a LEFT JOIN clause to the query using the FeatureFeatureType relation
 * @method     ChildFeatureTypeQuery rightJoinFeatureFeatureType($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FeatureFeatureType relation
 * @method     ChildFeatureTypeQuery innerJoinFeatureFeatureType($relationAlias = null) Adds a INNER JOIN clause to the query using the FeatureFeatureType relation
 *
 * @method     ChildFeatureTypeQuery leftJoinFeatureTypeI18n($relationAlias = null) Adds a LEFT JOIN clause to the query using the FeatureTypeI18n relation
 * @method     ChildFeatureTypeQuery rightJoinFeatureTypeI18n($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FeatureTypeI18n relation
 * @method     ChildFeatureTypeQuery innerJoinFeatureTypeI18n($relationAlias = null) Adds a INNER JOIN clause to the query using the FeatureTypeI18n relation
 *
 * @method     ChildFeatureType findOne(ConnectionInterface $con = null) Return the first ChildFeatureType matching the query
 * @method     ChildFeatureType findOneOrCreate(ConnectionInterface $con = null) Return the first ChildFeatureType matching the query, or a new ChildFeatureType object populated from the query conditions when no match is found
 *
 * @method     ChildFeatureType findOneById(int $id) Return the first ChildFeatureType filtered by the id column
 * @method     ChildFeatureType findOneBySlug(string $slug) Return the first ChildFeatureType filtered by the slug column
 * @method     ChildFeatureType findOneByHasFeatureAvValue(int $has_feature_av_value) Return the first ChildFeatureType filtered by the has_feature_av_value column
 * @method     ChildFeatureType findOneByIsMultilingualFeatureAvValue(int $is_multilingual_feature_av_value) Return the first ChildFeatureType filtered by the is_multilingual_feature_av_value column
 * @method     ChildFeatureType findOneByPattern(string $pattern) Return the first ChildFeatureType filtered by the pattern column
 * @method     ChildFeatureType findOneByCssClass(string $css_class) Return the first ChildFeatureType filtered by the css_class column
 * @method     ChildFeatureType findOneByInputType(string $input_type) Return the first ChildFeatureType filtered by the input_type column
 * @method     ChildFeatureType findOneByMax(double $max) Return the first ChildFeatureType filtered by the max column
 * @method     ChildFeatureType findOneByMin(double $min) Return the first ChildFeatureType filtered by the min column
 * @method     ChildFeatureType findOneByStep(double $step) Return the first ChildFeatureType filtered by the step column
 * @method     ChildFeatureType findOneByImageMaxWidth(double $image_max_width) Return the first ChildFeatureType filtered by the image_max_width column
 * @method     ChildFeatureType findOneByImageMaxHeight(double $image_max_height) Return the first ChildFeatureType filtered by the image_max_height column
 * @method     ChildFeatureType findOneByImageRatio(double $image_ratio) Return the first ChildFeatureType filtered by the image_ratio column
 * @method     ChildFeatureType findOneByCreatedAt(string $created_at) Return the first ChildFeatureType filtered by the created_at column
 * @method     ChildFeatureType findOneByUpdatedAt(string $updated_at) Return the first ChildFeatureType filtered by the updated_at column
 *
 * @method     array findById(int $id) Return ChildFeatureType objects filtered by the id column
 * @method     array findBySlug(string $slug) Return ChildFeatureType objects filtered by the slug column
 * @method     array findByHasFeatureAvValue(int $has_feature_av_value) Return ChildFeatureType objects filtered by the has_feature_av_value column
 * @method     array findByIsMultilingualFeatureAvValue(int $is_multilingual_feature_av_value) Return ChildFeatureType objects filtered by the is_multilingual_feature_av_value column
 * @method     array findByPattern(string $pattern) Return ChildFeatureType objects filtered by the pattern column
 * @method     array findByCssClass(string $css_class) Return ChildFeatureType objects filtered by the css_class column
 * @method     array findByInputType(string $input_type) Return ChildFeatureType objects filtered by the input_type column
 * @method     array findByMax(double $max) Return ChildFeatureType objects filtered by the max column
 * @method     array findByMin(double $min) Return ChildFeatureType objects filtered by the min column
 * @method     array findByStep(double $step) Return ChildFeatureType objects filtered by the step column
 * @method     array findByImageMaxWidth(double $image_max_width) Return ChildFeatureType objects filtered by the image_max_width column
 * @method     array findByImageMaxHeight(double $image_max_height) Return ChildFeatureType objects filtered by the image_max_height column
 * @method     array findByImageRatio(double $image_ratio) Return ChildFeatureType objects filtered by the image_ratio column
 * @method     array findByCreatedAt(string $created_at) Return ChildFeatureType objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildFeatureType objects filtered by the updated_at column
 *
 */
abstract class FeatureTypeQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \FeatureType\Model\Base\FeatureTypeQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\FeatureType\\Model\\FeatureType', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildFeatureTypeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildFeatureTypeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \FeatureType\Model\FeatureTypeQuery) {
            return $criteria;
        }
        $query = new \FeatureType\Model\FeatureTypeQuery();
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
     * @return ChildFeatureType|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = FeatureTypeTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(FeatureTypeTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildFeatureType A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, SLUG, HAS_FEATURE_AV_VALUE, IS_MULTILINGUAL_FEATURE_AV_VALUE, PATTERN, CSS_CLASS, INPUT_TYPE, MAX, MIN, STEP, IMAGE_MAX_WIDTH, IMAGE_MAX_HEIGHT, IMAGE_RATIO, CREATED_AT, UPDATED_AT FROM feature_type WHERE ID = :p0';
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
            $obj = new ChildFeatureType();
            $obj->hydrate($row);
            FeatureTypeTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildFeatureType|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
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
    public function findPks($keys, $con = null)
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
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(FeatureTypeTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(FeatureTypeTableMap::ID, $keys, Criteria::IN);
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
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the slug column
     *
     * Example usage:
     * <code>
     * $query->filterBySlug('fooValue');   // WHERE slug = 'fooValue'
     * $query->filterBySlug('%fooValue%'); // WHERE slug LIKE '%fooValue%'
     * </code>
     *
     * @param     string $slug The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterBySlug($slug = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($slug)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $slug)) {
                $slug = str_replace('*', '%', $slug);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::SLUG, $slug, $comparison);
    }

    /**
     * Filter the query on the has_feature_av_value column
     *
     * Example usage:
     * <code>
     * $query->filterByHasFeatureAvValue(1234); // WHERE has_feature_av_value = 1234
     * $query->filterByHasFeatureAvValue(array(12, 34)); // WHERE has_feature_av_value IN (12, 34)
     * $query->filterByHasFeatureAvValue(array('min' => 12)); // WHERE has_feature_av_value > 12
     * </code>
     *
     * @param     mixed $hasFeatureAvValue The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByHasFeatureAvValue($hasFeatureAvValue = null, $comparison = null)
    {
        if (is_array($hasFeatureAvValue)) {
            $useMinMax = false;
            if (isset($hasFeatureAvValue['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::HAS_FEATURE_AV_VALUE, $hasFeatureAvValue['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($hasFeatureAvValue['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::HAS_FEATURE_AV_VALUE, $hasFeatureAvValue['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::HAS_FEATURE_AV_VALUE, $hasFeatureAvValue, $comparison);
    }

    /**
     * Filter the query on the is_multilingual_feature_av_value column
     *
     * Example usage:
     * <code>
     * $query->filterByIsMultilingualFeatureAvValue(1234); // WHERE is_multilingual_feature_av_value = 1234
     * $query->filterByIsMultilingualFeatureAvValue(array(12, 34)); // WHERE is_multilingual_feature_av_value IN (12, 34)
     * $query->filterByIsMultilingualFeatureAvValue(array('min' => 12)); // WHERE is_multilingual_feature_av_value > 12
     * </code>
     *
     * @param     mixed $isMultilingualFeatureAvValue The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByIsMultilingualFeatureAvValue($isMultilingualFeatureAvValue = null, $comparison = null)
    {
        if (is_array($isMultilingualFeatureAvValue)) {
            $useMinMax = false;
            if (isset($isMultilingualFeatureAvValue['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IS_MULTILINGUAL_FEATURE_AV_VALUE, $isMultilingualFeatureAvValue['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($isMultilingualFeatureAvValue['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IS_MULTILINGUAL_FEATURE_AV_VALUE, $isMultilingualFeatureAvValue['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::IS_MULTILINGUAL_FEATURE_AV_VALUE, $isMultilingualFeatureAvValue, $comparison);
    }

    /**
     * Filter the query on the pattern column
     *
     * Example usage:
     * <code>
     * $query->filterByPattern('fooValue');   // WHERE pattern = 'fooValue'
     * $query->filterByPattern('%fooValue%'); // WHERE pattern LIKE '%fooValue%'
     * </code>
     *
     * @param     string $pattern The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByPattern($pattern = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($pattern)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $pattern)) {
                $pattern = str_replace('*', '%', $pattern);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::PATTERN, $pattern, $comparison);
    }

    /**
     * Filter the query on the css_class column
     *
     * Example usage:
     * <code>
     * $query->filterByCssClass('fooValue');   // WHERE css_class = 'fooValue'
     * $query->filterByCssClass('%fooValue%'); // WHERE css_class LIKE '%fooValue%'
     * </code>
     *
     * @param     string $cssClass The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByCssClass($cssClass = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($cssClass)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $cssClass)) {
                $cssClass = str_replace('*', '%', $cssClass);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::CSS_CLASS, $cssClass, $comparison);
    }

    /**
     * Filter the query on the input_type column
     *
     * Example usage:
     * <code>
     * $query->filterByInputType('fooValue');   // WHERE input_type = 'fooValue'
     * $query->filterByInputType('%fooValue%'); // WHERE input_type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $inputType The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByInputType($inputType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($inputType)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $inputType)) {
                $inputType = str_replace('*', '%', $inputType);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::INPUT_TYPE, $inputType, $comparison);
    }

    /**
     * Filter the query on the max column
     *
     * Example usage:
     * <code>
     * $query->filterByMax(1234); // WHERE max = 1234
     * $query->filterByMax(array(12, 34)); // WHERE max IN (12, 34)
     * $query->filterByMax(array('min' => 12)); // WHERE max > 12
     * </code>
     *
     * @param     mixed $max The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByMax($max = null, $comparison = null)
    {
        if (is_array($max)) {
            $useMinMax = false;
            if (isset($max['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::MAX, $max['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($max['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::MAX, $max['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::MAX, $max, $comparison);
    }

    /**
     * Filter the query on the min column
     *
     * Example usage:
     * <code>
     * $query->filterByMin(1234); // WHERE min = 1234
     * $query->filterByMin(array(12, 34)); // WHERE min IN (12, 34)
     * $query->filterByMin(array('min' => 12)); // WHERE min > 12
     * </code>
     *
     * @param     mixed $min The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByMin($min = null, $comparison = null)
    {
        if (is_array($min)) {
            $useMinMax = false;
            if (isset($min['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::MIN, $min['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($min['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::MIN, $min['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::MIN, $min, $comparison);
    }

    /**
     * Filter the query on the step column
     *
     * Example usage:
     * <code>
     * $query->filterByStep(1234); // WHERE step = 1234
     * $query->filterByStep(array(12, 34)); // WHERE step IN (12, 34)
     * $query->filterByStep(array('min' => 12)); // WHERE step > 12
     * </code>
     *
     * @param     mixed $step The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByStep($step = null, $comparison = null)
    {
        if (is_array($step)) {
            $useMinMax = false;
            if (isset($step['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::STEP, $step['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($step['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::STEP, $step['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::STEP, $step, $comparison);
    }

    /**
     * Filter the query on the image_max_width column
     *
     * Example usage:
     * <code>
     * $query->filterByImageMaxWidth(1234); // WHERE image_max_width = 1234
     * $query->filterByImageMaxWidth(array(12, 34)); // WHERE image_max_width IN (12, 34)
     * $query->filterByImageMaxWidth(array('min' => 12)); // WHERE image_max_width > 12
     * </code>
     *
     * @param     mixed $imageMaxWidth The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByImageMaxWidth($imageMaxWidth = null, $comparison = null)
    {
        if (is_array($imageMaxWidth)) {
            $useMinMax = false;
            if (isset($imageMaxWidth['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IMAGE_MAX_WIDTH, $imageMaxWidth['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($imageMaxWidth['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IMAGE_MAX_WIDTH, $imageMaxWidth['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::IMAGE_MAX_WIDTH, $imageMaxWidth, $comparison);
    }

    /**
     * Filter the query on the image_max_height column
     *
     * Example usage:
     * <code>
     * $query->filterByImageMaxHeight(1234); // WHERE image_max_height = 1234
     * $query->filterByImageMaxHeight(array(12, 34)); // WHERE image_max_height IN (12, 34)
     * $query->filterByImageMaxHeight(array('min' => 12)); // WHERE image_max_height > 12
     * </code>
     *
     * @param     mixed $imageMaxHeight The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByImageMaxHeight($imageMaxHeight = null, $comparison = null)
    {
        if (is_array($imageMaxHeight)) {
            $useMinMax = false;
            if (isset($imageMaxHeight['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IMAGE_MAX_HEIGHT, $imageMaxHeight['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($imageMaxHeight['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IMAGE_MAX_HEIGHT, $imageMaxHeight['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::IMAGE_MAX_HEIGHT, $imageMaxHeight, $comparison);
    }

    /**
     * Filter the query on the image_ratio column
     *
     * Example usage:
     * <code>
     * $query->filterByImageRatio(1234); // WHERE image_ratio = 1234
     * $query->filterByImageRatio(array(12, 34)); // WHERE image_ratio IN (12, 34)
     * $query->filterByImageRatio(array('min' => 12)); // WHERE image_ratio > 12
     * </code>
     *
     * @param     mixed $imageRatio The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByImageRatio($imageRatio = null, $comparison = null)
    {
        if (is_array($imageRatio)) {
            $useMinMax = false;
            if (isset($imageRatio['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IMAGE_RATIO, $imageRatio['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($imageRatio['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::IMAGE_RATIO, $imageRatio['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::IMAGE_RATIO, $imageRatio, $comparison);
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
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::CREATED_AT, $createdAt, $comparison);
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
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(FeatureTypeTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(FeatureTypeTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureTypeTableMap::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \FeatureType\Model\FeatureFeatureType object
     *
     * @param \FeatureType\Model\FeatureFeatureType|ObjectCollection $featureFeatureType  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByFeatureFeatureType($featureFeatureType, $comparison = null)
    {
        if ($featureFeatureType instanceof \FeatureType\Model\FeatureFeatureType) {
            return $this
                ->addUsingAlias(FeatureTypeTableMap::ID, $featureFeatureType->getFeatureTypeId(), $comparison);
        } elseif ($featureFeatureType instanceof ObjectCollection) {
            return $this
                ->useFeatureFeatureTypeQuery()
                ->filterByPrimaryKeys($featureFeatureType->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByFeatureFeatureType() only accepts arguments of type \FeatureType\Model\FeatureFeatureType or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FeatureFeatureType relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function joinFeatureFeatureType($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FeatureFeatureType');

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
            $this->addJoinObject($join, 'FeatureFeatureType');
        }

        return $this;
    }

    /**
     * Use the FeatureFeatureType relation FeatureFeatureType object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \FeatureType\Model\FeatureFeatureTypeQuery A secondary query class using the current class as primary query
     */
    public function useFeatureFeatureTypeQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinFeatureFeatureType($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FeatureFeatureType', '\FeatureType\Model\FeatureFeatureTypeQuery');
    }

    /**
     * Filter the query by a related \FeatureType\Model\FeatureTypeI18n object
     *
     * @param \FeatureType\Model\FeatureTypeI18n|ObjectCollection $featureTypeI18n  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByFeatureTypeI18n($featureTypeI18n, $comparison = null)
    {
        if ($featureTypeI18n instanceof \FeatureType\Model\FeatureTypeI18n) {
            return $this
                ->addUsingAlias(FeatureTypeTableMap::ID, $featureTypeI18n->getId(), $comparison);
        } elseif ($featureTypeI18n instanceof ObjectCollection) {
            return $this
                ->useFeatureTypeI18nQuery()
                ->filterByPrimaryKeys($featureTypeI18n->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByFeatureTypeI18n() only accepts arguments of type \FeatureType\Model\FeatureTypeI18n or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FeatureTypeI18n relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function joinFeatureTypeI18n($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FeatureTypeI18n');

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
            $this->addJoinObject($join, 'FeatureTypeI18n');
        }

        return $this;
    }

    /**
     * Use the FeatureTypeI18n relation FeatureTypeI18n object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \FeatureType\Model\FeatureTypeI18nQuery A secondary query class using the current class as primary query
     */
    public function useFeatureTypeI18nQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinFeatureTypeI18n($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FeatureTypeI18n', '\FeatureType\Model\FeatureTypeI18nQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildFeatureType $featureType Object to remove from the list of results
     *
     * @return ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function prune($featureType = null)
    {
        if ($featureType) {
            $this->addUsingAlias(FeatureTypeTableMap::ID, $featureType->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the feature_type table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(FeatureTypeTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            FeatureTypeTableMap::clearInstancePool();
            FeatureTypeTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildFeatureType or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildFeatureType object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(FeatureTypeTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(FeatureTypeTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        FeatureTypeTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            FeatureTypeTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

    // i18n behavior

    /**
     * Adds a JOIN clause to the query using the i18n relation
     *
     * @param     string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return    ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function joinI18n($locale = 'en_US', $relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $relationName = $relationAlias ? $relationAlias : 'FeatureTypeI18n';

        return $this
            ->joinFeatureTypeI18n($relationAlias, $joinType)
            ->addJoinCondition($relationName, $relationName . '.Locale = ?', $locale);
    }

    /**
     * Adds a JOIN clause to the query and hydrates the related I18n object.
     * Shortcut for $c->joinI18n($locale)->with()
     *
     * @param     string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return    ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function joinWithI18n($locale = 'en_US', $joinType = Criteria::LEFT_JOIN)
    {
        $this
            ->joinI18n($locale, null, $joinType)
            ->with('FeatureTypeI18n');
        $this->with['FeatureTypeI18n']->setIsWithOneToMany(false);

        return $this;
    }

    /**
     * Use the I18n relation query object
     *
     * @see       useQuery()
     *
     * @param     string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return    ChildFeatureTypeI18nQuery A secondary query class using the current class as primary query
     */
    public function useI18nQuery($locale = 'en_US', $relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinI18n($locale, $relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FeatureTypeI18n', '\FeatureType\Model\FeatureTypeI18nQuery');
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(FeatureTypeTableMap::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(FeatureTypeTableMap::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(FeatureTypeTableMap::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(FeatureTypeTableMap::UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(FeatureTypeTableMap::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     ChildFeatureTypeQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(FeatureTypeTableMap::CREATED_AT);
    }

} // FeatureTypeQuery
