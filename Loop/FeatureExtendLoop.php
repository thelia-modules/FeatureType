<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Loop;

use FeatureType\Model\FeatureFeatureType;
use FeatureType\Model\FeatureFeatureTypeQuery;
use FeatureType\Model\FeatureType;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use FeatureType\Model\Map\FeatureTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Feature;
use Thelia\Model\FeatureAv as FeatureModel;
use Thelia\Model\Map\FeatureTableMap;

/**
 * Class FeatureExtendLoop
 * @package FeatureType\Loop
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureExtendLoop extends Feature implements PropelSearchLoopInterface
{
    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return parent::getArgDefinitions()->addArguments(array(
            Argument::createIntListTypeArgument("feature_type_id"),
            Argument::createAnyTypeArgument("feature_type_slug")
        ));
    }

    /**
     * this method returns a Propel ModelCriteria
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria()
    {
        $query = parent::buildModelCriteria();

        if (null !== $featureTypeSlug = $this->getFeatureTypeSlug()) {
            $featureTypeSlug = array_map(function($value) {
                return "'" . addslashes($value) . "'";
            }, explode(',', $featureTypeSlug));

            $join = new Join();

            $join->addExplicitCondition(
                FeatureTableMap::TABLE_NAME,
                'ID',
                null,
                FeatureFeatureTypeTableMap::TABLE_NAME,
                'FEATURE_ID',
                null
            );

            $join2 = new Join();

            $join2->addExplicitCondition(
                FeatureFeatureTypeTableMap::TABLE_NAME,
                'FEATURE_TYPE_ID',
                null,
                FeatureTypeTableMap::TABLE_NAME,
                'ID',
                null
            );

            $join->setJoinType(Criteria::JOIN);
            $join2->setJoinType(Criteria::JOIN);

            $query
                ->addJoinObject($join, 'feature_feature_type_join')
                ->addJoinObject($join2, 'feature_type_join')
                ->addJoinCondition(
                    'feature_type_join',
                    '`feature_type`.`slug` IN ('.implode(',', $featureTypeSlug).')'
                );
        }

        if (null !== $featureTypeId = $this->getFeatureTypeId()) {
            $join = new Join();

            $join->addExplicitCondition(
                FeatureTableMap::TABLE_NAME,
                'ID',
                null,
                FeatureFeatureTypeTableMap::TABLE_NAME,
                'FEATURE_ID',
                null
            );

            $join->setJoinType(Criteria::JOIN);

            $query
                ->addJoinObject($join, 'feature_type_join')
                ->addJoinCondition(
                    'feature_type_join',
                    '`feature_feature_type`.`feature_type_id` IN (?)',
                    implode(',', $featureTypeId),
                    null,
                    \PDO::PARAM_INT
                );
        }

        return $query;
    }

    /**
     * @param LoopResult $loopResult
     * @return array|mixed|\Propel\Runtime\Collection\ObjectCollection
     */
    private function getFeaturesType(LoopResult $loopResult)
    {
        $featureIds = array();

        /** @var FeatureModel $feature */
        foreach ($loopResult->getResultDataCollection() as $feature) {
            $featureIds[] = $feature->getId();
        }

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

        $query = FeatureFeatureTypeQuery::create()
            ->filterByFeatureId($featureIds, Criteria::IN)
            ->addJoinObject($join);

        return $query
            ->withColumn('`feature_type`.`SLUG`', 'SLUG')
            ->find();
    }

    /**
     * @param string $slug
     * @return string
     */
    private function formatSlug($slug)
    {
        return strtoupper(str_replace('-', '_', $slug));
    }

    /**
     * @param LoopResult $loopResult
     * @return LoopResult
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function parseResults(LoopResult $loopResult)
    {
        $featureTypes = self::getFeaturesType($loopResult);

        $slugs = array();

        /** @var FeatureType $featureType */
        foreach ($featureTypes as $featureType) {
            $slugs[$featureType->getVirtualColumn('SLUG')] = true;
        }

        /** @var FeatureModel $feature */
        foreach ($loopResult->getResultDataCollection() as $feature) {
            $loopResultRow = new LoopResultRow($feature);
            $loopResultRow->set("ID", $feature->getId())
                ->set("IS_TRANSLATED", $feature->getVirtualColumn('IS_TRANSLATED'))
                ->set("LOCALE", $this->locale)
                ->set("TITLE", $feature->getVirtualColumn('i18n_TITLE'))
                ->set("CHAPO", $feature->getVirtualColumn('i18n_CHAPO'))
                ->set("DESCRIPTION", $feature->getVirtualColumn('i18n_DESCRIPTION'))
                ->set("POSTSCRIPTUM", $feature->getVirtualColumn('i18n_POSTSCRIPTUM'))
                ->set("POSITION", $this->useFeaturePosition ? $feature->getPosition() : $feature->getVirtualColumn('position'))
            ;

            // init slug variable
            foreach ($slugs as $slug => $bool) {
                $loopResultRow->set(
                    self::formatSlug(
                        $slug
                    ),
                    null
                );
            }

            /** @var FeatureFeatureType $featureType */
            foreach ($featureTypes as $featureType) {
                if ($featureType->getFeatureId() === $feature->getId()) {
                    $loopResultRow->set(
                        self::formatSlug(
                            $featureType->getVirtualColumn('SLUG')
                        ),
                        true
                    );
                }
            }

            $this->addOutputFields($loopResultRow, $feature);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
