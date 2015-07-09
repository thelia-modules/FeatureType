<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Model;

use FeatureType\Model\Base\FeatureType as BaseFeatureType;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use FeatureType\Model\Map\FeatureTypeAvMetaTableMap;
use FeatureType\Model\Map\FeatureTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Model\FeatureAvQuery;
use Thelia\Model\Map\FeatureAvTableMap;

/**
 * Class FeatureType
 * @package FeatureType\Model
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureType extends BaseFeatureType
{
    /**
     * Returns a value based on the slug, feature_av_id and locale
     *
     * <code>
     * $value  = FeatureType::getValue('color', 2);
     * </code>
     *
     * @param string $slug
     * @param int $featureId
     * @param string $locale
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function getValue($slug, $featureId, $locale = 'en_US')
    {
        return self::getValues([$slug], [$featureId], $locale)[$slug][$featureId];
    }

    /**
     * Returns a set of values
     * If the value does not exist, it is replaced by null
     *
     * <code>
     * $values = FeatureType::getValue(['color','texture'], [4,7]);
     * </code>
     *
     * <sample>
     *  array(
     *  'color' => [4 => '#00000', 7 => '#FFF000'],
     *  'texture' => [4 => null, 7 => 'lines.jpg']
     * )
     * </sample>
     *
     * @param string[] $slugs
     * @param int[] $featureIds
     * @param string $locale
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public static function getValues(array $slugs, array $featureIds, $locale = 'en_US')
    {
        $return = array();

        foreach ($slugs as $slug) {
            $return[$slug] = array();
            foreach ($featureIds as $featureId) {
                $return[$slug][$featureId] = null;
            }
        }

        $query = FeatureTypeAvMetaQuery::create()
            ->filterByLocale($locale)
            ->filterByFeatureAvId($featureIds, Criteria::IN);

        self::addJoinFeatureFeatureType($query);
        self::addJoinFeatureType($query);
        self::addJoinFeatureAv($query);

        $query
            ->addJoinCondition('feature_type', "`feature_type`.`SLUG` IN (" . self::formatStringsForIn($slugs) . ")")
            ->addJoinCondition('feature_av', "`feature_av`.`ID` = `feature_type_av_meta`.`FEATURE_AV_ID`")
            ->withColumn('`feature_type`.`SLUG`', 'SLUG')
            ->withColumn('`feature_av`.`ID`', 'FEATURE_AV_ID');

        $results = $query->find();

        foreach ($results as $result) {
            $return[$result->getVirtualColumn('SLUG')][$result->getVirtualColumn('FEATURE_AV_ID')]
                = $result->getValue();
        }

        return $return;
    }

    /**
     * Returns a set of first values
     * If the value does not exist, it is replaced by null
     *
     * <code>
     * $values = FeatureType::getFirstValues(['color','texture', 'other'], [4,7]);
     * </code>
     *
     * <sample>
     *  array(
     *  'color' => '#00000',
     *  'texture' => 'lines.jpg',
     *  'other' => null
     * )
     * </sample>
     *
     * @param string[] $slugs
     * @param int[] $featureIds
     * @param string $locale
     * @return array
     */
    public static function getFirstValues(array $slugs, array $featureIds, $locale = 'en_US')
    {
        $results = self::getValues($slugs, $featureIds, $locale);

        $return = array();

        foreach ($slugs as $slug) {
            if (!isset($return[$slug])) {
                $return[$slug] = null;
            }

            foreach ($results[$slug] as $value) {
                if ($return[$slug] === null) {
                    $return[$slug] = $value;
                    continue;
                }
                break;
            }
        }

        return $return;
    }

    /**
     * Find FeatureAv by slugs, featureIds, values, locales
     *
     * <code>
     * $featureAv = FeatureType::getFeatureAv('color', '1', '#00000');
     * </code>
     *
     * @param null|string|array $slugs
     * @param null|string|array $featureIds
     * @param null|string|array $values meta values
     * @param null|string|array $locale
     *
     * @return \Thelia\Model\FeatureAv
     */
    public static function getFeatureAv($slugs = null, $featureIds = null, $values = null, $locale = 'en_US')
    {
        return self::queryFeatureAvs($slugs, $featureIds, $values, $locale)->findOne();
    }

    /**
     * Find FeatureAv by slugs, featureIds, values, locales
     *
     * <code>
     * $featureAv = FeatureType::getFeatureAvs('color', '1', '#00000');
     * </code>
     *
     * @param null|string|array $slugs
     * @param null|string|array $featureIds
     * @param null|string|array $values meta values
     * @param null|string|array $locale
     *
     * @return \Thelia\Model\FeatureAv
     */
    public static function getFeatureAvs($slugs = null, $featureIds = null, $values = null, $locale = 'en_US')
    {
        return self::queryFeatureAvs($slugs, $featureIds, $values, $locale)->find();
    }

    /**
     * @param null|string|array $slugs
     * @param null|string|array $featureIds
     * @param null|string|array $values meta values
     * @param null|string|array $locales
     *
     * @return FeatureAvQuery
     */
    protected static function queryFeatureAvs($slugs = null, $featureIds = null, $values = null, $locales = null)
    {
        if (!is_array($slugs) && $slugs !== null) {
            $slugs = array($slugs);
        }

        if (!is_array($featureIds) && $featureIds !== null) {
            $featureIds = array($featureIds);
        }

        if (!is_array($values) && $values !== null) {
            $values = array($values);
        }

        if (!is_array($locales) && $locales !== null) {
            $locales = array($locales);
        }

        $query = FeatureAvQuery::create();

        if ($featureIds !== null) {
            $query->filterByFeatureId($featureIds, Criteria::IN);
        }

        self::addJoinFeatureTypeAvMeta($query);
        self::addJoinFeatureFeatureType($query);
        self::addJoinFeatureType($query);

        if ($locales !== null) {
            $query->addJoinCondition(
                'feature_type_av_meta',
                "`feature_type_av_meta`.`LOCALE` IN (" . self::formatStringsForIn($locales) . ")"
            );
        }

        if ($values !== null) {
            $query->addJoinCondition(
                'feature_type_av_meta',
                "`feature_type_av_meta`.`VALUE` IN (" . self::formatStringsForIn($values) . ")"
            );
        }

        if ($slugs !== null) {
            $query->addJoinCondition(
                'feature_type',
                "`feature_type`.`SLUG` IN (" . self::formatStringsForIn($slugs) . ")"
            );
        }

        return $query;
    }

    /**
     * @param array $strings
     * @return string
     */
    protected static function formatStringsForIn(array $strings)
    {
        return implode(
            ',',
            array_map(
                function($v) {
                    return "'" . $v . "'";
                },
                $strings
            )
        );
    }

    /**
     * @param Criteria $query
     */
    protected static function addJoinFeatureTypeAvMeta(Criteria & $query)
    {
        $join = new Join();

        $join->addExplicitCondition(
            FeatureAvTableMap::TABLE_NAME,
            'ID',
            null,
            FeatureTypeAvMetaTableMap::TABLE_NAME,
            'FEATURE_AV_ID',
            null
        );

        $join->setJoinType(Criteria::INNER_JOIN);

        $query->addJoinObject($join, 'feature_type_av_meta');
    }

    /**
     * @param Criteria $query
     */
    protected static function addJoinFeatureFeatureType(Criteria & $query)
    {
        $join = new Join();

        $join->addExplicitCondition(
            FeatureTypeAvMetaTableMap::TABLE_NAME,
            'FEATURE_FEATURE_TYPE_ID',
            null,
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'ID',
            null
        );

        $join->setJoinType(Criteria::INNER_JOIN);

        $query->addJoinObject($join, 'feature_feature_type');
    }

    /**
     * @param Criteria $query
     */
    protected static function addJoinFeatureType(Criteria & $query)
    {
        $join = new Join();

        $join->addExplicitCondition(
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'FEATURE_TYPE_ID',
            null,
            FeatureTypeTableMap::TABLE_NAME,
            'ID',
            null
        );

        $join->setJoinType(Criteria::INNER_JOIN);

        $query->addJoinObject($join, 'feature_type');
    }

    /**
     * @param Criteria $query
     * @return $this
     */
    protected static function addJoinFeatureAv(Criteria & $query)
    {
        $join = new Join();

        $join->addExplicitCondition(
            FeatureFeatureTypeTableMap::TABLE_NAME,
            'FEATURE_ID',
            null,
            FeatureAvTableMap::TABLE_NAME,
            'FEATURE_ID',
            null
        );

        $join->setJoinType(Criteria::INNER_JOIN);

        $query->addJoinObject($join, 'feature_av');
    }
}
