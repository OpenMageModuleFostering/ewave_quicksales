<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 22.09.11
 * Time: 16:02
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Model_Listing extends Mage_Catalog_Model_Abstract //Mage_Core_Model_Abstract
{
    const ENTITY = 'quicksales_listing';
    protected $_assignedProducts = null;

    protected $_attributeValuesAssociation = null;
    protected $_attributesAssociation = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('quicksales/listing');
    }

    protected function getAssociations() {
        $attributes = Mage::getResourceModel('quicksales/listing_associated_attribute_collection');
        $attributes->addFieldToFilter('listing_id', $this->getId());

        $assignedAttributes = null;
        $assignedAttributeValues = null;
        
        foreach ($attributes as $attribute) {

            $assignedAttributes[$attribute->getQattributeId()] = $attribute->getMattributeId();

            $attributeValues = Mage::getResourceModel('quicksales/listing_associated_attribute_value_collection');
            $attributeValues->addFieldToFilter('attribute_map_id', $attribute->getId());
            foreach ($attributeValues as $value) {
                $assignedAttributeValues[$attribute->getQattributeId()][$value->getQattributeValueId()] = $value->getMattributeValueId();
            }
        }

        $this->_attributesAssociation = $assignedAttributes;
        $this->_attributeValuesAssociation = $assignedAttributeValues;

        return $this;
    }


    public function getAttributesAssociation() {
        if (!$this->_attributesAssociation) {
            $this->getAssociations();
        }

        return $this->_attributesAssociation;
    }

    public function getAttributeValuesAssociation() {

        if (!$this->_attributeValuesAssociation) {
            $this->getAssociations();
        }

        return $this->_attributeValuesAssociation;

    }

    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    public function getAssignedProducts()
    {

        if ($this->_assignedProducts == null) {
            $products = Mage::getResourceModel('quicksales/listing_product_collection');
            $products->addFieldToFilter('listing_id', $this->getId());
            $productIds = array();
            if (!empty($products)) {
                foreach ($products as $product) {
                    $productIds[] = $product->getProductId();
                }
            }

            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToSelect('name');

            $collection->addFIeldToFilter('entity_id', array('in' => $productIds));

            $this->_assignedProducts = $collection;
        }
        return $this->_assignedProducts;
    }


    public function getAttributes($groupId = null, $skipSuper = false)
    {
        $listingAttributes = $this->getResource()
                ->loadAllAttributes($this)
                ->getSortedAttributes();
        if ($groupId) {
            $attributes = array();
            foreach ($listingAttributes as $attribute) {
                if ($attribute->isInGroup($this->getAttributeSetId(), $groupId)) {
                    $attributes[] = $attribute;
                }
            }
        } else {
            $attributes = $listingAttributes;
        }

        return $attributes;
    }

    public function getProductsCount() {

        return $this->getAssignedProducts()->count();
    }
}
