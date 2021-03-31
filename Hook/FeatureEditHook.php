<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Hook;

use FeatureType\Form\FeatureTypeAvMetaUpdateForm;
use FeatureType\Model\FeatureFeatureType;
use FeatureType\Model\FeatureFeatureTypeQuery;
use FeatureType\Model\FeatureTypeAvMeta;
use FeatureType\Model\FeatureTypeAvMetaQuery;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use FeatureType\Model\Map\FeatureTypeAvMetaTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Thelia;
use Thelia\Model\FeatureAv;
use Thelia\Model\FeatureAvQuery;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;

/**
 * Class FeatureEditHook
 * @package FeatureType\Hook
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureEditHook extends BaseHook
{
    /** @var ContainerInterface */
    protected $container = null;

    /** @var TheliaFormFactory */
    protected $formFactory = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, TheliaFormFactory $formFactory)
    {
        $this->container = $container;
        $this->formFactory = $formFactory;
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onFeatureEditBottom(HookRenderEvent $event)
    {
        $data = self::hydrateForm($event->getArgument('feature_id'));

        /** @var ParserContext $parserContext */
        $parserContext = $this->container->get('thelia.parser.context');
        $form = $parserContext->getForm('feature_type_av_meta-update', FeatureTypeAvMetaUpdateForm::class, 'form');

        if (!$form) {
            $form = $this->formFactory->createForm('feature_type_av_meta-update', FormType::class, $data);
        }

        $this->container->get('thelia.parser.context')->addForm($form);

        $event->add($this->render(
            'feature-type/hook/feature-edit-bottom.html',
            array(
                'feature_id' => $event->getArgument('feature_id'),
                'form_meta_data' => $data
            )
        ));
    }

    /**
     * @param HookRenderEvent $event
     */
    public function onFeatureEditJs(HookRenderEvent $event)
    {
        $event->add($this->render(
            'feature-type/hook/feature-edit-js.html',
            array(
                'feature_id' => $event->getArgument('feature_id')
            )
        ));
    }

    /**
     * @param FeatureAv $featureAv
     * @return array|mixed|\Propel\Runtime\Collection\ObjectCollection
     */
    protected function getFeatureTypeAvMetas(FeatureAv $featureAv)
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

        return FeatureTypeAvMetaQuery::create()
            ->filterByFeatureAvId($featureAv->getId())
            ->addJoinObject($join)
            ->withColumn('`feature_feature_type`.`feature_type_id`', 'FEATURE_TYPE_ID')
            ->find();
    }

    /**
     * @param int $featureId
     * @return array
     */
    protected function hydrateForm($featureId)
    {
        $data = array('feature_av' => array());

        $featureAvs = FeatureAvQuery::create()->findByFeatureId($featureId);

        $featureTypes = FeatureFeatureTypeQuery::create()->findByFeatureId($featureId);

        $langs = LangQuery::create()->find();

        /** @var FeatureAv $featureAv */
        foreach ($featureAvs as $featureAv) {
            $featureAvMetas = self::getFeatureTypeAvMetas($featureAv);

            $data['feature_av'][$featureAv->getId()] = array(
                'lang' => array()
            );

            /** @var Lang $lang */
            foreach ($langs as $lang) {
                $data['feature_av'][$featureAv->getId()]['lang'][$lang->getId()] = array(
                    'feature_type' => array()
                );

                /** @var FeatureTypeAvMeta $featureAvMeta */
                foreach ($featureAvMetas as $featureAvMeta) {
                    /** @var FeatureFeatureType $featureType */
                    foreach ($featureTypes as $featureType) {
                        if ($featureAvMeta->getLocale() === $lang->getLocale()
                            && intval($featureAvMeta->getVirtualColumn("FEATURE_TYPE_ID")) === $featureType->getFeatureTypeId()
                        ) {
                            $data['feature_av'][$featureAv->getId()]['lang'][$lang->getId()]['feature_type'][$featureType->getFeatureTypeId()] = $featureAvMeta->getValue();
                        }
                    }
                }
            }
        }

        return $data;
    }
}
