<?php
/*************************************************************************************/
/*      This file is part of the module FeatureType                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace FeatureType\Form;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class FeatureTypeUpdateForm
 * @package FeatureType\Form
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeUpdateForm extends FeatureTypeCreateForm
{
    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'feature_type-update';
    }

    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder feature :
     *
     */
    protected function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add('id', 'integer', array(
                'required' => true,
                'constraints' => array(
                    new NotBlank()
                )
            ));
    }

    /**
     * @param $value
     * @param ExecutionContextInterface $context
     */
    public function checkExistType($value, ExecutionContextInterface $context)
    {
    }
}
