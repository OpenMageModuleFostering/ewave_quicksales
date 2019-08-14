<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 25.09.11
 * Time: 19:00
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step1 extends Mage_Adminhtml_Block_Widget {

    protected $_assignedProducts = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quicksales/listing/edit/tab/additional/step1.phtml');
        $this->setId('listing_edit');
    }

    protected function _prepareLayout()
    {
        $this->setChild('quicksales_product_grid',
            $this->getLayout()->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step1_grid',
                'quicksales_product_grid')
        );
    }


    public function getGridJsObject()
    {
        return $this->getChild('quicksales_product_grid')->getJsObjectName();
    }

    public function getAssignedProductsJson() {

        $jsonProducts = array();
        foreach ($this->getAssignedProducts() as $product) {
            $jsonProducts[$product->getId()] = array(
                'id' => $product->getId(),
                'name' => $product->getName()
            );
        }

        if (empty($jsonProducts)) {
            return '{}';
        }

        return Mage::helper('core')->jsonEncode($jsonProducts);
    }

    public function getListing()
    {
        return Mage::registry('current_listing');
    }

    public function getAssignedProducts() {

        if ($this->_assignedProducts == null) {
            $listing = $this->getListing();
            $this->_assignedProducts = $listing->getAssignedProducts();

            $this->_assignedProducts->joinField('qlisting_id',
                               'quicksales/listing_product',
                               'quicksale_listing_id',
                               'product_id=entity_id',
                               '{{table}}.listing_id="' . $listing->getId() . '"',
                               'inner');
        }
        return $this->_assignedProducts;
    }
}
