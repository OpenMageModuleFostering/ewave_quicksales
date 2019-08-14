<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 27.09.11
 * Time: 12:43
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Model_Source_Shipping extends Mage_Eav_Model_Entity_Attribute_Source_Table
{

    protected $shippings = array(
        '1' => 'Free postage',
        '2' => 'Flat postage',
        '3' => 'Flat postage by location',
        '4' => 'Calculated postage',
        '5' => 'See item description',
        '6' => 'No postage - local pickup only',
        '7' => 'Temando',
    );

    public function getAllOptions()
    {
        if (!$this->_options) {

            $options = array();

            foreach ($this->shippings as $id => $shipping) {

                $options[] = array(
                    'value' => $id,
                    'label' => $shipping
                );
            }
            $this->_options = $options;
        }
        return $this->_options;
    }

    public function getLabelById($id) {
        return $this->shippings[$id];
    }

}