<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Form\Type;

use FeatureType\FeatureType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Thelia\Core\Translation\Translator;

/**
 * Class FeatureTypeType
 * @package FeatureType\Form\Type
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'feature_type',
            'collection',
            array(
                'type' => 'text',
                'allow_add'    => true,
                'allow_delete' => true,
                'options' => array(
                    'required' => true
                )
            )
        );
    }

    public function getName()
    {
        return 'feature_type';
    }
}
