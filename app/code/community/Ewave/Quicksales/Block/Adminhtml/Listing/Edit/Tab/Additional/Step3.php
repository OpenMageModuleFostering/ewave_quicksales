<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 25.09.11
 * Time: 19:00
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step3 extends Mage_Adminhtml_Block_Widget {

    protected $_assignedProducts = null;
    protected $mAttributes = array();

    protected $qAttributes = array();
    protected $qValues = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quicksales/listing/edit/tab/additional/step3.phtml');
        $this->setId('listing_edit');
    }

    protected function _prepareLayout()
    {
        $this->setChild('attributes_association',
            $this->getLayout()->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step3_attributes',
                'attributes_association')
        );
    }

    public function getListing()
    {
        return Mage::registry('current_listing');
    }

}
