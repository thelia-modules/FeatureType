<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Action;

use FeatureType\Event\FeatureTypeAvMetaEvent;
use FeatureType\Model\FeatureFeatureType;
use FeatureType\Model\FeatureFeatureTypeQuery;
use FeatureType\Event\FeatureTypeEvent;
use FeatureType\Event\FeatureTypeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FeatureTypeAction
 * @package FeatureType\Action
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeAction implements EventSubscriberInterface
{
    /**
     * @param FeatureTypeEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function create(FeatureTypeEvent $event)
    {
        $event->getFeatureType()->save($event->getConnectionInterface());
    }

    /**
     * @param FeatureTypeEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function update(FeatureTypeEvent $event)
    {
        $event->getFeatureType()->save($event->getConnectionInterface());
    }

    /**
     * @param FeatureTypeEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function delete(FeatureTypeEvent $event)
    {
        $event->getFeatureType()->delete($event->getConnectionInterface());
    }

    /**
     * @param FeatureTypeEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function associate(FeatureTypeEvent $event)
    {
        (new FeatureFeatureType())
            ->setFeatureId($event->getFeature()->getId())
            ->setFeatureTypeId($event->getFeatureType()->getId())
            ->save($event->getConnectionInterface());
    }

    /**
     * @param FeatureTypeEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function dissociate(FeatureTypeEvent $event)
    {
        FeatureFeatureTypeQuery::create()
            ->filterByFeature($event->getFeature())
            ->filterByFeatureType($event->getFeatureType())
            ->delete($event->getConnectionInterface());
    }

    /**
     * @param FeatureTypeAvMetaEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function metaCreate(FeatureTypeAvMetaEvent $event)
    {
        $event->getFeatureTypeAvMeta()->save($event->getConnectionInterface());
    }

    /**
     * @param FeatureTypeAvMetaEvent $event
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function metaUpdate(FeatureTypeAvMetaEvent $event)
    {
        $event->getFeatureTypeAvMeta()->save($event->getConnectionInterface());
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            FeatureTypeEvents::FEATURE_TYPE_CREATE => array(
                'create', 128
            ),
            FeatureTypeEvents::FEATURE_TYPE_UPDATE => array(
                'update', 128
            ),
            FeatureTypeEvents::FEATURE_TYPE_DELETE => array(
                'delete', 128
            ),
            FeatureTypeEvents::FEATURE_TYPE_ASSOCIATE => array(
                'associate', 128
            ),
            FeatureTypeEvents::FEATURE_TYPE_DISSOCIATE => array(
                'dissociate', 128
            ),
            FeatureTypeEvents::FEATURE_TYPE_AV_META_CREATE => array(
                'metaCreate', 128
            ),
            FeatureTypeEvents::FEATURE_TYPE_AV_META_UPDATE => array(
                'metaUpdate', 128
            )
        );
    }
}
