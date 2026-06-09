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
use FeatureType\Model\FeatureTypeQuery;
use FeatureType\Model\Map\FeatureFeatureTypeTableMap;
use FeatureType\Model\Map\FeatureTypeAvMetaTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\FeatureAv;
use Thelia\Model\FeatureAvI18nQuery;
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
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'feature-edit.bottom' => [
                ['type' => 'back', 'method' => 'onFeatureEditBottom'],
            ],
            'feature.edit-js' => [
                ['type' => 'back', 'method' => 'onFeatureEditJs'],
            ],
        ];
    }

    public function onFeatureEditBottom(HookRenderEvent $event): void
    {
        $featureId = (int) $event->getArgument('feature_id');

        $data = $this->hydrateForm($featureId);

        $form = $this->formFactory->createForm(FeatureTypeAvMetaUpdateForm::getName(), data: $data);

        $featureTypes = FeatureTypeQuery::create()
            ->setLocale($this->getRequest()->getLocale())
            ->orderById()
            ->find();

        $langs = LangQuery::create()->find();

        // associated feature types for this feature
        $associatedFeatureTypeIds = [];
        foreach (FeatureFeatureTypeQuery::create()->findByFeatureId($featureId) as $featureFeatureType) {
            $associatedFeatureTypeIds[] = $featureFeatureType->getFeatureTypeId();
        }

        $rows = [];
        $availableForSelect = [];
        foreach ($featureTypes as $featureType) {
            $isAssociated = in_array($featureType->getId(), $associatedFeatureTypeIds, true);
            $info = [
                'id' => $featureType->getId(),
                'slug' => $featureType->getSlug(),
                'title' => $featureType->getTitle(),
                'description' => $featureType->getDescription(),
                'has_feature_av_value' => (bool) $featureType->getHasFeatureAvValue(),
                'is_multilingual_feature_av_value' => (bool) $featureType->getIsMultilingualFeatureAvValue(),
                'input_type' => $featureType->getInputType(),
                'pattern' => $featureType->getPattern(),
                'css_class' => $featureType->getCssClass(),
                'min' => $featureType->getMin(),
                'max' => $featureType->getMax(),
                'step' => $featureType->getStep(),
            ];

            if ($isAssociated) {
                $rows[] = $info;
            } else {
                $availableForSelect[] = $info;
            }
        }

        // feature av titles per lang
        $featureAvTitles = [];
        foreach ($langs as $lang) {
            $featureAvTitles[$lang->getId()] = [];
            $i18ns = FeatureAvI18nQuery::create()
                ->filterByLocale($lang->getLocale())
                ->useFeatureAvQuery()
                    ->filterByFeatureId($featureId)
                ->endUse()
                ->find();
            foreach ($i18ns as $i18n) {
                $featureAvTitles[$lang->getId()][$i18n->getId()] = $i18n->getTitle();
            }
        }

        $langList = [];
        foreach ($langs as $lang) {
            $langList[] = [
                'id' => $lang->getId(),
                'locale' => $lang->getLocale(),
                'code' => $lang->getCode(),
                'title' => $lang->getTitle(),
            ];
        }

        $event->add($this->render(
            'FeatureType/hook/feature-edit-bottom.html.twig',
            [
                'form' => $form->createView()->getView(),
                'feature_id' => $featureId,
                'form_meta_data' => $data,
                'associated_feature_types' => $rows,
                'available_feature_types' => $availableForSelect,
                'langs' => $langList,
                'feature_av_titles' => $featureAvTitles,
                'edit_language_id' => $this->resolveEditLanguageId(),
            ]
        ));
    }

    public function onFeatureEditJs(HookRenderEvent $event): void
    {
        $event->add($this->render(
            'FeatureType/hook/feature-edit-js.html.twig',
            [
                'feature_id' => (int) $event->getArgument('feature_id'),
            ]
        ));
    }

    private function resolveEditLanguageId(): ?int
    {
        $request = $this->getRequest();
        if (!$request->hasSession()) {
            return null;
        }

        $lang = $request->getSession()->get('thelia.admin.edition.lang');

        return $lang instanceof Lang ? $lang->getId() : null;
    }

    protected function getFeatureTypeAvMetas(FeatureAv $featureAv): mixed
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

    protected function hydrateForm(int $featureId): array
    {
        $data = ['feature_av' => []];

        $featureAvs = FeatureAvQuery::create()->findByFeatureId($featureId);

        $featureTypes = FeatureFeatureTypeQuery::create()->findByFeatureId($featureId);

        $langs = LangQuery::create()->find();

        /** @var FeatureAv $featureAv */
        foreach ($featureAvs as $featureAv) {
            $featureAvMetas = self::getFeatureTypeAvMetas($featureAv);

            $data['feature_av'][$featureAv->getId()] = [
                'lang' => [],
            ];

            /** @var Lang $lang */
            foreach ($langs as $lang) {
                $data['feature_av'][$featureAv->getId()]['lang'][$lang->getId()] = [
                    'feature_type' => [],
                ];

                /** @var FeatureTypeAvMeta $featureAvMeta */
                foreach ($featureAvMetas as $featureAvMeta) {
                    /** @var FeatureFeatureType $featureType */
                    foreach ($featureTypes as $featureType) {
                        if ($featureAvMeta->getLocale() === $lang->getLocale()
                            && (int) $featureAvMeta->getVirtualColumn('FEATURE_TYPE_ID') === $featureType->getFeatureTypeId()
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
