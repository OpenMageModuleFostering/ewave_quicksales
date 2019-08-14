<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 15:06
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Block_Adminhtml_Listing_Log extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct()
    {
        $this->_controller = 'adminhtml_listing_log';
        $this->_blockGroup = 'quicksales';
        $this->_headerText = Mage::helper('quicksales')->__('Listing Log');
        parent::__construct();
        $this->removeButton('add');
    }

}
