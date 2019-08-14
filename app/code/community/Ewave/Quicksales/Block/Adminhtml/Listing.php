<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 15:06
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Block_Adminhtml_Listing extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct()
    {
        $this->_controller = 'adminhtml_listing';
        $this->_blockGroup = 'quicksales';
        $this->_headerText = Mage::helper('quicksales')->__('Listings');
        $this->_addButtonLabel = Mage::helper('quicksales')->__('Add new Listing');
        parent::__construct();
    }

}
