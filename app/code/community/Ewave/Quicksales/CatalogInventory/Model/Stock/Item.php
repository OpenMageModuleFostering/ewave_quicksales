<?php

class Ewave_Quicksales_CatalogInventory_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    protected function _beforeSave()
    {

        if (!Mage::registry('quicksales_data')) {


            $item = $this;

            $collection = Mage::getResourceModel('quicksales/listing_product_collection');
            $collection->addFieldToFilter('product_id', $item->getProductId());

            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            foreach ($collection as $assigned_data) {
                $listing = Mage::getModel('quicksales/listing')->load($assigned_data->getListingId());
                $listing->setData('assigned_products', array($item->getProductId()));


                if (Mage::getStoreConfig('quicksales/stock/update_quicksale_qty_magento_change')) {

                    $quicksalesApi = Mage::getModel('quicksales/api_createitem');
                    $quicksalesApi->setQuiet(true);

                    $quicksalesApi->send($listing, $item->getProductId());
                }

                if ($item->getQty() == 0 && Mage::getStoreConfig('quicksales/stock/stop_quicksale_magento_qty_less') && $assigned_data->getStatus() == 1) {

                    $quicksalesApi = Mage::getModel('quicksales/api_action');
                    $quicksalesApi->setQuiet(true);

                    $quicksalesApi->send($listing, 1, $item->getProductId());

                }

                if ($item->getQty() > 0 && Mage::getStoreConfig('quicksales/stock/start_quicksale_magento_qty_great') && $product->getIsInStock() == 1 && $assigned_data->getStatus() != 1) {

                    $quicksalesApi = Mage::getModel('quicksales/api_action');
                    $quicksalesApi->setQuiet(true);

                    $quicksalesApi->send($listing, 0, $item->getProductId());

                }
            }

        }
        return parent::afterCommitCallback();
    }

}