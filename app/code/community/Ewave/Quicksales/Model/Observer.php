<?php


class Ewave_Quicksales_Model_Observer
{

    public function updateQty($observer)
    {
        if (Mage::registry('quicksales_data')) {
            return false;
        }

        $item = $observer->getItem();

        $collection = Mage::getResourceModel('quicksales/listing_product_collection');
        $collection->addFieldToFilter('product_id', $item->getProductId());

        $product = Mage::getModel('catalog/product')->load($item->getProductId());

        foreach ($collection as $assigned_data) {
            $listing = Mage::getModel('quicksales/listing')->load($assigned_data->getListingId());
            $listing->setData('assigned_products', array($item->getProductId()));

            if (($item->getQty() <= 0 || !$item->getIsInStock()) && Mage::getStoreConfig('quicksales/stock/stop_quicksale_magento_qty_less') && $assigned_data->getStatus() !== 0) {

                $quicksalesApi = Mage::getModel('quicksales/api_action');
                $quicksalesApi->setQuiet(true);

                $quicksalesApi->send($listing, 1, $item->getProductId());

            }

            if ($item->getQty() > 0 && Mage::getStoreConfig('quicksales/stock/start_quicksale_magento_qty_great') && $product->getIsInStock() == 1 && $assigned_data->getStatus() != 1) {

                $quicksalesApi = Mage::getModel('quicksales/api_action');
                $quicksalesApi->setQuiet(true);

                $quicksalesApi->send($listing, null, $item->getProductId(), 1);

            }


            if (Mage::getStoreConfig('quicksales/stock/update_quicksale_qty_magento_change')) {

                $quicksalesApi = Mage::getModel('quicksales/api_createitem');
                $quicksalesApi->setQuiet(true);

                $quicksalesApi->send($listing, $item->getProductId());
            }

        }

        return $this;
    }

    public function updateProduct($observer)
    {

        if (Mage::registry('quicksales_data')) {
            return false;
        }

        $product = $observer->getProduct();

        if ($product->getStockData('is_in_stock') && $product->getIsInStock() == 1 && Mage::getStoreConfig('quicksales/stock/stop_quicksale_magento_out_stock')) {

            $collection = Mage::getResourceModel('quicksales/listing_product_collection');
            $collection->addFieldToFilter('product_id', $product->getProductId());

            foreach ($collection as $assigned_data) {
                $listing = Mage::getModel('quicksales/listing')->load($assigned_data->getListingId());
                $listing->setData('assigned_products', array($product->getProductId()));
                $quicksalesApi = Mage::getModel('quicksales/api_action');
                $quicksalesApi->setQuiet(true);

                $quicksalesApi->send($listing, 0, $product->getProductId());
            }
        }
        return $this;
    }

    public function getQuicksalesItems()
    {
        if (!Mage::getStoreConfig('quicksales/stock/stop_quicksale_magento_out_stock')) {
            return false;
        }

        Mage::register('quicksales_data', true);

        $collection = Mage::getResourceModel('quicksales/listing_product_collection');

        foreach ($collection as $item) {
            $quicksalesApi = Mage::getModel('quicksales/api_getitem');
            $quicksalesApi->setQuiet(true);
            $quicksalesApi->synchronize($item);

        }

    }

    public function getQuicksalesOrders()
    {

        /* @var $quicksalesApi Ewave_Quicksales_Model_Api_Getorders*/
        $quicksalesApi = Mage::getModel('quicksales/api_getorders');

        $data = array(
            'RequestingRole' => 'SELLER',
            'IncludeAwaitingPayment' => 1,
//            'FromDate' => null,
//            'FromDate' => null,
//            'ToDate' => null,
            //'NumberOfHours' => 5000
        );
        $quicksalesApi->import($data);

    }

    public function paidOrder($order)
    {

        $quicksalesApi = Mage::getModel('quicksales/api_updateorder');

        $data = array(
            'InvoiceID' => $order->getQuicksalesOrderId(),
            'Action' => 1,
            'NotifyBuyer' => 1 ,
        );
        $quicksalesApi->update($data, $order->getId());


        return $order;

    }

    public function sendQuicksaleOrder($observer)
    {

        $order = $observer->getOrder();
        /*@var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getInvoice();

        if (empty($order) || $order->getQuicksalesOrderId()) {
            return false;
        }


        /*@var $invoice Mage_Sales_Model_Order_Invoice */
        if ($order->getQhash()) {
            Mage::getModel('quicksales/api_sendinvoice')->send($order, $invoice);
        } else {
            return null;
        }

        return $this;
    }

    public function checkShippedOrder($observer){
        $shipment = $observer->getShipment();
        $order = $shipment->getOrder();

        if (!$order->getQuicksalesOrderId()) {
            if ($order->getQhash()) {
                Mage::throwException(Mage::helper('quicksales')->__('Please, create invoice first'));
            }
        }
    }


    public function shippedOrder($observer)
    {
        $shipment = $observer->getShipment();
        $order = $shipment->getOrder();

        if (!$order->getQuicksalesOrderId()) {
            if ($order->getQhash()) {
                Mage::throwException(Mage::helper('quicksales')->__('Please, create invoice first'));
            } else {
                return false;
            }
        }

        $message = "Shipping information: <br />\n";

        foreach ($shipment->getAllTracks() as $track) {
            $message .= $track->getTitle();
            $message .= '(' . $track->getCarrierCode() . ')';
            $message .= ': ';
            $message .= $track->getTrackNumber();
            $message .= "\n";
        }

        $quicksalesApi = Mage::getModel('quicksales/api_updateorder');

        $data = array(
            'InvoiceID' => $order->getQuicksalesOrderId(),
            'Action' => 2,
            'Message' => $message,
            'NotifyBuyer' => 1 ,
        );
        $quicksalesApi->update($data, $order->getId());

        return $this;

    }
}
