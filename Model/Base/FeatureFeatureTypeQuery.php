<?php

namespace FeatureType\Model\Base;

use \Exception;
use \PDO;
use FeatureType\Model\FeatureFeatureType as ChildFeatureFeatureType;
use FeatureType\Model\FeatureFeatureTypeQuery as ChildFeatureFeatureTypeQuery;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Feature;

/**
 * Base class that represents a query for the 'feature_feature_type' table.
 *
 *
 *
 * @method     ChildFeatureFeatureTypeQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildFeatureFeatureTypeQuery orderByFeatureId($order = Criteria::ASC) Order by the feature_id column
 * @method     ChildFeatureFeatureTypeQuery orderByFeatureTypeId($order = Criteria::ASC) Order by the feature_type_id column
 *
 * @method     ChildFeatureFeatureTypeQuery groupById() Group by the id column
 * @method     ChildFeatureFeatureTypeQuery groupByFeatureId() Group by the feature_id column
 * @method     ChildFeatureFeatureTypeQuery groupByFeatureTypeId() Group by the feature_type_id column
 *
 * @method     ChildFeatureFeatureTypeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildFeatureFeatureTypeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildFeatureFeatureTypeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildFeatureFeatureTypeQuery leftJoinFeature($relationAlias = null) Adds a LEFT JOIN clause to the query using the Feature relation
 * @method     ChildFeatureFeatureTypeQuery rightJoinFeature($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Feature relation
 * @method     ChildFeatureFeatureTypeQuery innerJoinFeature($relationAlias = null) Adds a INNER JOIN clause to the query using the Feature relation
 *
 * @method     ChildFeatureFeatureTypeQuery leftJoinFeatureType($relationAlias = null) Adds a LEFT JOIN clause to the query using the FeatureType relation
 * @method     ChildFeatureFeatureTypeQuery rightJoinFeatureType($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FeatureType relation
 * @method     ChildFeatureFeatureTypeQuery innerJoinFeatureType($relationAlias = null) Adds a INNER JOIN clause to the query using the FeatureType relation
 *
 * @method     ChildFeatureFeatureTypeQuery leftJoinFeatureTypeAvMeta($relationAlias = null) Adds a LEFT JOIN clause to the query using the FeatureTypeAvMeta relation
 * @method     ChildFeatureFeatureTypeQuery rightJoinFeatureTypeAvMeta($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FeatureTypeAvMeta relation
 * @method     ChildFeatureFeatureTypeQuery innerJoinFeatureTypeAvMeta($relationAlias = null) Adds a INNER JOIN clause to the query using the FeatureTypeAvMeta relation
 *
 * @method     ChildFeatureFeatureType findOne(ConnectionInterface $con = null) Return the first ChildFeatureFeatureType matching the query
 * @method     ChildFeatureFeatureType findOneOrCreate(ConnectionInterface $con = null) Return the first ChildFeatureFeatureType matching the query, or a new ChildFeatureFeatureType object populated from the query conditions when no match is found
 *
 * @method     ChildFeatureFeatureType findOneById(int $id) Return the first ChildFeatureFeatureType filtered by the id column
 * @method     ChildFeatureFeatureType findOneByFeatureId(int $feature_id) Return the first ChildFeatureFeatureType filtered by the feature_id column
 * @method     ChildFeatureFeatureType findOneByFeatureTypeId(int $feature_type_id) Return the first ChildFeatureFeatureType filtered by the feature_type_id column
 *
 * @method     array findById(int $id) Return ChildFeatureFeatureType objects filtered by the id column
 * @method     array findByFeatureId(int $feature_id) Return ChildFeatureFeatureType objects filtered by the feature_id column
 * @method     array findByFeatureTypeId(int $feature_type_id) Return ChildFeatureFeatureType objects filtered by the feature_type_id column
 *
 */
abstract class FeatureFeatureTypeQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \FeatureType\Model\Base\FeatureFeatureTypeQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\FeatureType\\Model\\FeatureFeatureType', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildFeatureFeatureTypeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildFeatureFeatureTypeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \FeatureType\Model\FeatureFeatureTypeQuery) {
            return $criteria;
        }
        $query = new \FeatureType\Model\FeatureFeatureTypeQuery();
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
     * @return ChildFeatureFeatureType|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = FeatureFeatureTypeTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(FeatureFeatureTypeTableMap::DATABASE_NAME);
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
     * @return   ChildFeatureFeatureType A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, FEATURE_ID, FEATURE_TYPE_ID FROM feature_feature_type WHERE ID = :p0';
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
            $obj = new ChildFeatureFeatureType();
            $obj->hydrate($row);
            FeatureFeatureTypeTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildFeatureFeatureType|array|mixed the result, formatted by the current formatter
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
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(FeatureFeatureTypeTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(FeatureFeatureTypeTableMap::ID, $keys, Criteria::IN);
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
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(FeatureFeatureTypeTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(FeatureFeatureTypeTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureFeatureTypeTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the feature_id column
     *
     * Example usage:
     * <code>
     * $query->filterByFeatureId(1234); // WHERE feature_id = 1234
     * $query->filterByFeatureId(array(12, 34)); // WHERE feature_id IN (12, 34)
     * $query->filterByFeatureId(array('min' => 12)); // WHERE feature_id > 12
     * </code>
     *
     * @see       filterByFeature()
     *
     * @param     mixed $featureId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByFeatureId($featureId = null, $comparison = null)
    {
        if (is_array($featureId)) {
            $useMinMax = false;
            if (isset($featureId['min'])) {
                $this->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_ID, $featureId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($featureId['max'])) {
                $this->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_ID, $featureId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_ID, $featureId, $comparison);
    }

    /**
     * Filter the query on the feature_type_id column
     *
     * Example usage:
     * <code>
     * $query->filterByFeatureTypeId(1234); // WHERE feature_type_id = 1234
     * $query->filterByFeatureTypeId(array(12, 34)); // WHERE feature_type_id IN (12, 34)
     * $query->filterByFeatureTypeId(array('min' => 12)); // WHERE feature_type_id > 12
     * </code>
     *
     * @see       filterByFeatureType()
     *
     * @param     mixed $featureTypeId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByFeatureTypeId($featureTypeId = null, $comparison = null)
    {
        if (is_array($featureTypeId)) {
            $useMinMax = false;
            if (isset($featureTypeId['min'])) {
                $this->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_TYPE_ID, $featureTypeId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($featureTypeId['max'])) {
                $this->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_TYPE_ID, $featureTypeId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_TYPE_ID, $featureTypeId, $comparison);
    }

    /**
     * Filter the query by a related \Thelia\Model\Feature object
     *
     * @param \Thelia\Model\Feature|ObjectCollection $feature The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByFeature($feature, $comparison = null)
    {
        if ($feature instanceof \Thelia\Model\Feature) {
            return $this
                ->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_ID, $feature->getId(), $comparison);
        } elseif ($feature instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_ID, $feature->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByFeature() only accepts arguments of type \Thelia\Model\Feature or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Feature relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function joinFeature($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Feature');

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
            $this->addJoinObject($join, 'Feature');
        }

        return $this;
    }

    /**
     * Use the Feature relation Feature object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\FeatureQuery A secondary query class using the current class as primary query
     */
    public function useFeatureQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinFeature($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Feature', '\Thelia\Model\FeatureQuery');
    }

    /**
     * Filter the query by a related \FeatureType\Model\FeatureType object
     *
     * @param \FeatureType\Model\FeatureType|ObjectCollection $featureType The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByFeatureType($featureType, $comparison = null)
    {
        if ($featureType instanceof \FeatureType\Model\FeatureType) {
            return $this
                ->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_TYPE_ID, $featureType->getId(), $comparison);
        } elseif ($featureType instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(FeatureFeatureTypeTableMap::FEATURE_TYPE_ID, $featureType->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByFeatureType() only accepts arguments of type \FeatureType\Model\FeatureType or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FeatureType relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function joinFeatureType($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FeatureType');

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
            $this->addJoinObject($join, 'FeatureType');
        }

        return $this;
    }

    /**
     * Use the FeatureType relation FeatureType object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \FeatureType\Model\FeatureTypeQuery A secondary query class using the current class as primary query
     */
    public function useFeatureTypeQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinFeatureType($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FeatureType', '\FeatureType\Model\FeatureTypeQuery');
    }

    /**
     * Filter the query by a related \FeatureType\Model\FeatureTypeAvMeta object
     *
     * @param \FeatureType\Model\FeatureTypeAvMeta|ObjectCollection $featureTypeAvMeta  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function filterByFeatureTypeAvMeta($featureTypeAvMeta, $comparison = null)
    {
        if ($featureTypeAvMeta instanceof \FeatureType\Model\FeatureTypeAvMeta) {
            return $this
                ->addUsingAlias(FeatureFeatureTypeTableMap::ID, $featureTypeAvMeta->getFeatureFeatureTypeId(), $comparison);
        } elseif ($featureTypeAvMeta instanceof ObjectCollection) {
            return $this
                ->useFeatureTypeAvMetaQuery()
                ->filterByPrimaryKeys($featureTypeAvMeta->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByFeatureTypeAvMeta() only accepts arguments of type \FeatureType\Model\FeatureTypeAvMeta or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FeatureTypeAvMeta relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function joinFeatureTypeAvMeta($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FeatureTypeAvMeta');

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
            $this->addJoinObject($join, 'FeatureTypeAvMeta');
        }

        return $this;
    }

    /**
     * Use the FeatureTypeAvMeta relation FeatureTypeAvMeta object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \FeatureType\Model\FeatureTypeAvMetaQuery A secondary query class using the current class as primary query
     */
    public function useFeatureTypeAvMetaQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinFeatureTypeAvMeta($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FeatureTypeAvMeta', '\FeatureType\Model\FeatureTypeAvMetaQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildFeatureFeatureType $featureFeatureType Object to remove from the list of results
     *
     * @return ChildFeatureFeatureTypeQuery The current query, for fluid interface
     */
    public function prune($featureFeatureType = null)
    {
        if ($featureFeatureType) {
            $this->addUsingAlias(FeatureFeatureTypeTableMap::ID, $featureFeatureType->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the feature_feature_type table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(FeatureFeatureTypeTableMap::DATABASE_NAME);
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
            FeatureFeatureTypeTableMap::clearInstancePool();
            FeatureFeatureTypeTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildFeatureFeatureType or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildFeatureFeatureType object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(FeatureFeatureTypeTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(FeatureFeatureTypeTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        FeatureFeatureTypeTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            FeatureFeatureTypeTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // FeatureFeatureTypeQuery
