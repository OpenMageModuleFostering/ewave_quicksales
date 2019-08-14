<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 27.09.11
 * Time: 12:18
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Model_Source_Posttolocation extends Mage_Eav_Model_Entity_Attribute_Source_Table
{

    protected $locations = array(
        'Worldwide',
        'National',
        'Statewide',
        'LocalPickup'
    );

    public function getAllOptions()
    {
        if (!$this->_options) {
            
            $options = array();

            foreach ($this->locations as $location) {

                $options[] = array(
                    'value' => $location,
                    'label' => $location
                );
            }
            $this->_options = $options;
        }
        return $this->_options;
    }

}
