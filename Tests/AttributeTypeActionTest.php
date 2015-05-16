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

namespace FeatureType\Tests;

use FeatureType\Action\FeatureTypeAction;
use FeatureType\Event\FeatureTypeAvMetaEvent;
use FeatureType\Event\FeatureTypeEvent;
use FeatureType\Model\FeatureFeatureTypeQuery;
use FeatureType\Model\FeatureTypeAvMeta;
use FeatureType\Model\FeatureTypeAvMetaQuery;
use Thelia\Model\Feature;
use Thelia\Model\FeatureAv;
use Thelia\Model\FeatureQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\Module;
use Thelia\Tests\TestCaseWithURLToolSetup;
use FeatureType\Model\FeatureType;
use FeatureType\Model\FeatureTypeQuery;

/**
 * Class FeatureTypeActionTest
 * @package FeatureType\Tests
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class FeatureTypeActionTest extends TestCaseWithURLToolSetup
{
    /** @var string */
    protected $slugTest = 'test-php-unit';

    /** @var FeatureTypeAction */
    protected $action = null;

    /** @var FeatureType */
    protected $featureType = null;

    /** @var Feature */
    protected $feature = null;

    /** @var bool */
    protected $isActive = false;

    public function __construct()
    {
        parent::__construct();

        /** @var Module $module */
        if (null !== $module = ModuleQuery::create()->findOneByCode('FeatureType')) {
            if ($module->getActivate()) {
                $this->isActive = true;
            }
        }

        $this->action = new FeatureTypeAction($this->getContainer());

        $this->featureType = (new FeatureType())
            ->setSlug($this->slugTest)
            ->setInputType('text');

        $this->feature = FeatureQuery::create()->findOne();
    }

    public function testActions()
    {
        if (!$this->action) {
            return;
        }

        // if last test is wrong
        FeatureTypeQuery::create()->filterBySlug($this->slugTest)->delete();

        self::createAction();
        self::updateAction();
        self::associateAction();
        self::createMetaAction();
        self::updateMetaAction();
        self::dissociateAction();
        self::deleteAction();
    }

    private function createAction()
    {
        $event = new FeatureTypeEvent($this->featureType);

        $this->action->create($event);

        $featureType = $event->getFeatureType();

        $this->assertNotEquals(null, $featureType->getId());
    }

    private function updateAction()
    {
        $this->featureType->setInputType('number');

        $this->action->update(
            new FeatureTypeEvent($this->featureType)
        );

        $featureTypeGetSuccessTest = FeatureTypeQuery::create()
            ->findOneById($this->featureType->getId());

        // test if input type is number after update
        $this->assertEquals('number', $featureTypeGetSuccessTest->getInputType());
    }

    private function associateAction()
    {
        $this->action->associate(
            (new FeatureTypeEvent($this->featureType))->setFeature($this->feature)
        );

        $featureFeatureType = FeatureFeatureTypeQuery::create()
            ->filterByFeatureId($this->feature->getId())
            ->filterByFeatureTypeId($this->featureType->getId())
            ->findOne();

        $this->assertNotEquals(null, $featureFeatureType);
    }

    private function createMetaAction()
    {
        /** @var FeatureAv $featureAv */
        $featureAv = $this->feature->getFeatureAvs()->getFirst();

        $featureFeatureType = FeatureFeatureTypeQuery::create()
            ->filterByFeatureId($this->feature->getId())
            ->filterByFeatureTypeId($this->featureType->getId())
            ->findOne();

        $featureTypeAvMeta = (new FeatureTypeAvMeta())
            ->setFeatureFeatureTypeId($featureFeatureType->getId())
            ->setFeatureAvId($featureAv->getId())
            ->setValue("");

        $this->action->metaCreate(
            new FeatureTypeAvMetaEvent($featureTypeAvMeta)
        );

        $this->assertNotEquals(null, $featureTypeAvMeta->getId());
    }

    private function updateMetaAction()
    {
        /** @var FeatureAv $featureAv */
        $featureAv = $this->feature->getFeatureAvs()->getFirst();

        $featureFeatureType = FeatureFeatureTypeQuery::create()
            ->filterByFeatureId($this->feature->getId())
            ->filterByFeatureTypeId($this->featureType->getId())
            ->findOne();

        $featureTypeAvMeta = FeatureTypeAvMetaQuery::create()
            ->filterByFeatureFeatureTypeId($featureFeatureType->getId())
            ->filterByFeatureAvId($featureAv->getId())
            ->findOne();

        $featureTypeAvMeta->setValue('test');

        $this->action->metaUpdate(
            new FeatureTypeAvMetaEvent($featureTypeAvMeta)
        );

        $featureTypeAvMetaTest = FeatureTypeAvMetaQuery::create()
            ->filterByFeatureFeatureTypeId($featureFeatureType->getId())
            ->filterByFeatureAvId($featureAv->getId())
            ->findOne();

        $this->assertEquals('test', $featureTypeAvMetaTest->getValue());
    }

    private function dissociateAction()
    {
        $this->action->dissociate(
            (new FeatureTypeEvent($this->featureType))->setFeature($this->feature)
        );

        $featureFeatureType = FeatureFeatureTypeQuery::create()
            ->filterByFeatureId($this->feature->getId())
            ->filterByFeatureTypeId($this->featureType->getId())
            ->findOne();

        $this->assertEquals(null, $featureFeatureType);
    }

    private function deleteAction()
    {
        $this->action->delete(
            new FeatureTypeEvent($this->featureType)
        );

        $featureTypeGetSuccessTest = FeatureTypeQuery::create()
            ->findOneById($this->featureType->getId());

        // test if input type is number after update
        $this->assertEquals(null, $featureTypeGetSuccessTest);
    }
}
