<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Event;

use FeatureType\Model\FeatureType;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Core\Event\ActionEvent;
use Thelia\Model\Feature;

/**
 * Class FeatureTypeEvent
 * @package FeatureType\Event
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeEvent extends ActionEvent
{
    /** @var ConnectionInterface|null */
    protected $connectionInterface = null;

    /** @var FeatureType */
    protected $featureType = null;

    /** @var Feature|null */
    protected $feature = null;

    /**
     * @param FeatureType $featureType
     */
    public function __construct(FeatureType $featureType)
    {
        $this->featureType = $featureType;
    }

    /**
     * @return FeatureType
     */
    public function getFeatureType()
    {
        return $this->featureType;
    }

    /**
     * @param $featureType
     * @return $this
     */
    public function setFeatureType(FeatureType $featureType)
    {
        $this->featureType = $featureType;

        return $this;
    }

    /**
     * @return null|Feature
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * @param Feature $feature
     * @return $this
     */
    public function setFeature(Feature $feature)
    {
        $this->feature = $feature;

        return $this;
    }

    /**
     * @return null|ConnectionInterface
     */
    public function getConnectionInterface()
    {
        return $this->connectionInterface;
    }

    /**
     * @param ConnectionInterface $connectionInterface
     * @return $this
     */
    public function setConnectionInterface(ConnectionInterface $connectionInterface)
    {
        $this->connectionInterface = $connectionInterface;

        return $this;
    }
}
