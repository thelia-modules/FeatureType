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

namespace FeatureType\Event;

use FeatureType\Model\FeatureTypeAvMeta;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Core\Event\ActionEvent;

/**
 * Class FeatureTypeAvMetaEvent
 * @package FeatureType\Event
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeAvMetaEvent extends ActionEvent
{
    /** @var ConnectionInterface|null */
    protected $connectionInterface = null;

    /** @var FeatureTypeAvMeta */
    protected $featureAvMeta = null;

    /**
     * @param FeatureTypeAvMeta $featureAvMeta
     */
    public function __construct(FeatureTypeAvMeta $featureAvMeta)
    {
        $this->featureAvMeta = $featureAvMeta;
    }

    /**
     * @return FeatureTypeAvMeta
     */
    public function getFeatureTypeAvMeta()
    {
        return $this->featureAvMeta;
    }

    /**
     * @param $featureAvMeta
     * @return $this
     */
    public function setFeatureTypeAvMeta(FeatureTypeAvMeta $featureAvMeta)
    {
        $this->featureAvMeta = $featureAvMeta;

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
