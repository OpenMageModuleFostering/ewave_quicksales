<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 03.10.11
 * Time: 11:07
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Model_Catalog_Resource_Product_Type_Configurable_Attribute_Collection
    extends Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
{

    public function getStoreId()
    {
        if (!$this->getProduct()) {
            return 0;
        }
        return (int)$this->_product->getStoreId();
    }
    /**
     * Add product attributes to collection items
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _addProductAttributes()
    {

        if (!$this->getProduct()) {
            return $this;
        }
        
        foreach ($this->_items as $item) {
            $productAttribute = $this->getProduct()->getTypeInstance(true)
                ->getAttributeById($item->getAttributeId(), $this->getProduct());
            $item->setProductAttribute($productAttribute);
        }
        return $this;
    }

     /**
     * Add Associated Product Filters (From Product Type Instance)
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    public function _addAssociatedProductFilters()
    {
        if (!$this->getProduct()) {
            return $this;
        }
        
        $this->getProduct()->getTypeInstance(true)
            ->getUsedProducts($this->getColumnValues('attribute_id'), $this->getProduct()); // Filter associated products
        return $this;
    }
}
