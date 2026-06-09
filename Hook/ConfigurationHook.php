<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Hook;

use FeatureType\Form\FeatureTypeCreateForm;
use FeatureType\Model\FeatureFeatureTypeQuery;
use FeatureType\Model\FeatureTypeQuery;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\FeatureQuery;
use Thelia\Model\LangQuery;

/**
 * Class ConfigurationHook
 * @package FeatureType\Hook
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class ConfigurationHook extends BaseHook
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
            'configuration.catalog-top' => [
                ['type' => 'back', 'method' => 'onConfigurationCatalogTop'],
            ],
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
        ];
    }

    public function onConfigurationCatalogTop(HookRenderEvent $event): void
    {
        $event->add($this->render('FeatureType/hook/configuration-catalog.html.twig'));
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $createForm = $this->formFactory->createForm(
            FeatureTypeCreateForm::getName(),
            data: ['has_feature_av_value' => 0]
        );

        $event->add($this->render('FeatureType/hook/module-configuration.html.twig', [
            'feature_types' => $this->buildFeatureTypeList(),
            'edit_language_id' => $this->resolveEditLanguageId(),
            'feature_type_id' => null,
            'create_form' => $createForm->createView()->getView(),
            'langs' => $this->buildLangList(),
        ]));
    }

    /**
     * Build the feature-type list (with the features each one is associated to)
     * displayed on the module configuration page.
     */
    private function buildFeatureTypeList(): array
    {
        $locale = $this->getRequest()?->getLocale() ?? 'en_US';

        $featureTypes = FeatureTypeQuery::create()
            ->setLocale($locale)
            ->orderById()
            ->find();

        $list = [];
        foreach ($featureTypes as $featureType) {
            $features = [];
            $featureFeatureTypes = FeatureFeatureTypeQuery::create()
                ->filterByFeatureTypeId($featureType->getId())
                ->find();

            foreach ($featureFeatureTypes as $featureFeatureType) {
                $feature = FeatureQuery::create()
                    ->setLocale($locale)
                    ->findPk($featureFeatureType->getFeatureId());

                if (null !== $feature) {
                    $features[] = [
                        'id' => $feature->getId(),
                        'title' => $feature->getTitle(),
                    ];
                }
            }

            $list[] = [
                'id' => $featureType->getId(),
                'slug' => $featureType->getSlug(),
                'title' => $featureType->getTitle(),
                'description' => $featureType->getDescription(),
                'features' => $features,
            ];
        }

        return $list;
    }

    private function buildLangList(): array
    {
        $langs = [];
        foreach (LangQuery::create()->filterByActive(1)->find() as $lang) {
            $langs[] = [
                'id' => $lang->getId(),
                'locale' => $lang->getLocale(),
                'code' => $lang->getCode(),
                'title' => $lang->getTitle(),
            ];
        }

        return $langs;
    }

    private function resolveEditLanguageId(): ?int
    {
        $request = $this->getRequest();
        if (null === $request || !$request->hasSession()) {
            return null;
        }

        $lang = $request->getSession()->get('thelia.admin.edition.lang');

        return $lang instanceof \Thelia\Model\Lang ? $lang->getId() : null;
    }
}
