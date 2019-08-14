<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 01.12.11
 * Time: 17:13
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_CatalogInventory_Model_Resource_Stock extends Mage_CatalogInventory_Model_Resource_Stock
{

    private function synchronizeQty($productId)
    {
        if (Mage::registry('quicksales_data')) {
            return false;
        }
        $collection = Mage::getResourceModel('quicksales/listing_product_collection');
        $collection->addFieldToFilter('product_id', $productId);

        $product = Mage::getModel('catalog/product')->load($productId);

        foreach ($collection as $assigned_data) {
            $listing = Mage::getModel('quicksales/listing')->load($assigned_data->getListingId());
            $listing->setData('assigned_products', array($productId));


            if (Mage::getStoreConfig('quicksales/stock/update_quicksale_qty_magento_change')) {

                $quicksalesApi = Mage::getModel('quicksales/api_createitem');
                $quicksalesApi->setQuiet(true);

                $quicksalesApi->send($listing, $productId);
            }

            if ($product->getStockItem()->getQty() == 0 && Mage::getStoreConfig('quicksales/stock/stop_quicksale_magento_qty_less') && $assigned_data->getStatus() == 1) {

                $quicksalesApi = Mage::getModel('quicksales/api_action');
                $quicksalesApi->setQuiet(true);

                $quicksalesApi->send($listing, 1, $productId);

            }

            if ($product->getStockItem()->getQty() > 0 && Mage::getStoreConfig('quicksales/stock/start_quicksale_magento_qty_great') && $product->getIsInStock() == 1 && $assigned_data->getStatus() != 1) {

                $quicksalesApi = Mage::getModel('quicksales/api_action');
                $quicksalesApi->setQuiet(true);

                $quicksalesApi->send($listing, 0, $productId);

            }

        }
    }

    public function correctItemsQty($stock, $productQtys, $operator = '-')
    {
        parent::correctItemsQty($stock, $productQtys, $operator);

        if (empty($productQtys)) {
            return $this;
        }

        foreach (array_keys($productQtys) as $productId) {
            $this->synchronizeQty($productId);
        }

        return $this;
    }


}