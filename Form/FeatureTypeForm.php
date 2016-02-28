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

namespace FeatureType\Form;

use Thelia\Form\BaseForm;

/**
 * Class FeatureTypeForm
 * @package FeatureType\Form
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class FeatureTypeForm extends BaseForm
{
    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'feature_type';
    }

    /**
     *
     * in this function you add all the fields you need for your Form.
     * Form this you have to call add method on $this->formBuilder feature :
     *
     */
    protected function buildForm()
    {
    }
}
