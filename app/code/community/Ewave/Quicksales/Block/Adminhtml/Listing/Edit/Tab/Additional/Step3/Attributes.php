<?php

class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step3_Attributes extends Mage_Adminhtml_Block_Widget {

    protected $_assignedProducts = null;
    protected $mAttributes = array();

    protected $qAttributes = array();
    protected $qValues = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quicksales/listing/edit/tab/additional/step3/attributes.phtml');
        $this->setId('listing_edit_step3_attributes');
    }

    public function getListing()
    {
        return Mage::registry('current_listing');
    }

    public function getQAttributes() {
        list($this->qAttributes, $this->qValues) = Mage::getModel('quicksales/api_gettags')->getAttributes($this->getListing()->getCategory());
        return $this->qAttributes;
    }

    public function getMAttributes() {
        if ($this->mAttributes) {
            return $this->mAttributes;
        }

        $mAttributesCollection = Mage::getResourceModel('catalog/eav_mysql4_product_attribute_collection');
        $mAttributesCollection->getSelect()->order('frontend_label');

        $productIds = Mage::helper('quicksales')->getProductIds();

        $products = Mage::getModel('catalog/product')->getCollection();
        $products->addAttributeToSelect('*');
        $products->addIdFilter($productIds);

        if (!$productIds) {
            return '';
        }

        /*
        foreach ($products as $product) {
            if ($product->getTypeId() != 'configurable') {
                continue;
            }
            $configurableAttributes = $product->getTypeInstance(true)->getConfigurableAttributes($product)->getItems();

            $product->setData('configurable_attributes', $configurableAttributes);
        }
        */

        foreach ($mAttributesCollection as $attribute) {
            if ($attribute->getFrontendInput() != 'select' && $attribute->getFrontendInput() != 'multiselect') {
                //continue;
            }
            if (!$attribute->getFrontendLabel()) {
                continue;
            }

            $attributeCode = $attribute->getAttributeCode();

            $skip = false;
            foreach ($products as $product) {
                $configurableAttributes = $product->getConfigurableAttributes();
                if (!$product->hasData($attributeCode) /*&& (!empty($configurableAttributes) && !$configurableAttributes[$attribute->getEntityTypeId()])*/) {
                    $skip = true;
                }
            }

            if ($skip) {
                continue;
            }

            $this->mAttributes[$attribute->getId()] = $attribute->getFrontendLabel();
        }
        return $this->mAttributes;
    }

    public function getQAttributeValues($attributeId) {
        return $this->qValues[$attributeId];
    }

    public function getMAssignedAttributeValues($qAttributeId) {
        
    }

    public function getAssociatedGridHtml($qc_id)
    {
        return Mage::helper('quicksales')->getAssociatedGridHtml($qc_id);
    }

}
