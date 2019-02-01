<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Loop;

use FeatureType\Model\FeatureType;
use FeatureType\Model\FeatureTypeQuery;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use FeatureType\Model\Map\FeatureTypeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Template\Element\BaseI18nLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * Class FeatureTypeLoop
 * @package FeatureType\Loop
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeLoop extends BaseI18nLoop implements PropelSearchLoopInterface
{
    /**
     * Definition of loop arguments
     *
     * example :
     *
     * public function getArgDefinitions()
     * {
     *  return new ArgumentCollection(
     *
     *       Argument::createIntListTypeArgument('id'),
     *           new Argument(
     *           'ref',
     *           new TypeCollection(
     *               new Type\AlphaNumStringListType()
     *           )
     *       ),
     *       Argument::createIntListTypeArgument('category'),
     *       Argument::createBooleanTypeArgument('new'),
     *       ...
     *   );
     * }
     *
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument("id"),
            Argument::createIntListTypeArgument("exclude_id"),
            Argument::createAnyTypeArgument('slug'),
            Argument::createIntListTypeArgument("feature_id"),
            Argument::createEnumListTypeArgument(
                "order",
                [
                    "id",
                    "id-reverse",
                    "feature_type",
                    "feature_type-reverse",
                ],
                "id"
            )
        );
    }
    /**
     * this method returns a Propel ModelCriteria
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria()
    {
        $query = new FeatureTypeQuery();

        /* manage translations */
        $this->configureI18nProcessing($query, array('TITLE', 'DESCRIPTION'));

        if (null !== $id = $this->getId()) {
            $query->filterById($id);
        }

        if (null !== $id = $this->getExcludeId()) {
            $query->filterById($id, Criteria::NOT_IN);
        }

        if (null !== $slug = $this->getSlug()) {
            $query->filterBySlug($slug);
        }

        if (null !== $featureId = $this->getFeatureId()) {
            $join = new Join();

            $join->addExplicitCondition(
                FeatureTypeTableMap::TABLE_NAME,
                'ID',
                null,
                FeatureFeatureTypeTableMap::TABLE_NAME,
                'FEATURE_TYPE_ID',
                null
            );

            $join->setJoinType(Criteria::JOIN);

            $query
                ->addJoinObject($join, 'feature_type_join')
                ->addJoinCondition(
                    'feature_type_join',
                    '`feature_feature_type`.`feature_id` IN (?)',
                    implode(',', $featureId),
                    null,
                    \PDO::PARAM_INT
                );

            $query->addJoinObject($join);
        }

        foreach ($this->getOrder() as $order) {
            switch ($order) {
                case "id":
                    $query->orderById();
                    break;
                case "id-reverse":
                    $query->orderById(Criteria::DESC);
                    break;
                case "slug":
                    $query->orderBySlug();
                    break;
                case "slug-reverse":
                    $query->orderBySlug(Criteria::DESC);
                    break;
            }
        }
        return $query;
    }

    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        /** @var FeatureType $entry */
        foreach ($loopResult->getResultDataCollection() as $entry) {
            $row = new LoopResultRow($entry);
            $row
                ->set("ID", $entry->getId())
                ->set("SLUG", $entry->getSlug())
                ->set("TITLE", $entry->getVirtualColumn('i18n_TITLE'))
                ->set("DESCRIPTION", $entry->getVirtualColumn('i18n_DESCRIPTION'))
                ->set("CSS_CLASS", $entry->getCssClass())
                ->set("PATTERN", $entry->getPattern())
                ->set("INPUT_TYPE", $entry->getInputType())
                ->set("MIN", $entry->getMin())
                ->set("MAX", $entry->getMax())
                ->set("STEP", $entry->getStep())
                ->set("IMAGE_MAX_WIDTH", $entry->getImageMaxWidth())
                ->set("IMAGE_MAX_HEIGHT", $entry->getImageMaxHeight())
                ->set("IMAGE_RATIO", $entry->getImageRatio())
                ->set("IS_MULTILINGUAL_FEATURE_AV_VALUE", $entry->getIsMultilingualFeatureAvValue())
                ->set("HAS_FEATURE_AV_VALUE", $entry->getHasFeatureAvValue())
            ;

            $this->addOutputFields($row, $entry);

            $loopResult->addRow($row);
        }

        return $loopResult;
    }
}
