<?php
/*
<SendInvoiceRequest>
  <SellerID>string</SellerID>
  <SellerPwd>string</SellerPwd>
  <BuyerID>string</BuyerID>
  <InvoiceID>integer</InvoiceID>
  <CheckoutID>integer</CheckoutID>
  <ItemArray>
    <Item>
      <ItemID>integer</ItemID>
      <Quantity>integer</Quantity>
    </Item>
  </ItemArray>
  <Postage>decimal</Postage>
  <Insurance>decimal</Insurance>
  <SellerDiscountORCharges>decimal</SellerDiscountORCharges>
  <GSTType>integer</GSTType>
  <PaymentMethods>
    <BankCheque>integer</BankCheque>
    <BankDeposit>integer</BankDeposit>
    <Cash>integer</Cash>
    <COD>integer</COD>
    <CreditCard>integer</CreditCard>
    <Escrow>integer</Escrow>
    <MoneyOrder>integer</MoneyOrder>
    <Paymate>integer</Paymate>
    <PayPal>integer</PayPal>
    <PersonalCheque>integer</PersonalCheque>
    <Other>integer</Other>
  </PaymentMethods>
  <PayPalEmailAddress>string</PayPalEmailAddress>
  <PaymentInstructions>string</PaymentInstructions>
  <PostageInstructions>string</PostageInstructions>
  <MessageToBuyer>string</MessageToBuyer>
  <SendCopyToSeller>integer</SendCopyToSeller>
  <ResendInvoice>integer</ResendInvoice>
</SendInvoiceRequest>
*/

class Ewave_Quicksales_Model_Api_Sendinvoice extends Mage_Core_Model_Abstract
{

    protected $_is_sand = false;

    protected $_test_mode = false;

    protected $_quiet = false;

    protected $_api = null;

    protected $_added = 0;
    protected $_updated = 0;
    protected $_errors = 0;


    protected function _construct()
    {
        parent::_construct();
        $this->_is_sand = Mage::getStoreConfig('quicksales/settings/sandbox');
        $this->_api = Mage::getModel('quicksales/api');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    public function setQuiet($flag)
    {
        $this->_quiet = $flag;
        $this->_api->setQuiet($flag);
        return $this;
    }

    public function sendInvoice($order, $invoice, $data = null)
    {
        $api = $this->_api;

        $listing_log = Mage::getModel('quicksales/listing_log');

        $date = Zend_Date::now()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $rez = 1;

        $listing_log
            ->setOrderId($order->getId())
            ->setType(2)
            ->setDate($date);
        $listing_log->save();

        $sellerId = Mage::getStoreConfig('quicksales/settings/vshop_seller');
        $sellerPwd = Mage::getStoreConfig('quicksales/settings/seller_password');

        $Item = new Varien_Simplexml_Element('<SendInvoiceRequest></SendInvoiceRequest>');
        $xmlItem = new Varien_Simplexml_Config($Item);

        $xmlItem->setNode('SellerID', $sellerId);
        $xmlItem->setNode('SellerPwd', $sellerPwd);

        if (!empty($data)) {
            foreach ($data as $field => $value) {
                $xmlItem->setNode($field, $value);
            }
        }

        $qhash = $order->getQhash();
        $qItems = explode('-', $qhash);
        $xmlItem->setNode('ItemArray', null);


        $ItemArray = $xmlItem->getNode('ItemArray');
        foreach ($invoice->getAllItems() as $qItem) {
            if ($qItem->getQty() <= 0 || !$qItem->getOrderItem()->getQitemId()) {
                continue;
            }
            $item = $ItemArray->addChild('Item', null);
            $item->addChild('ItemID', $qItem->getOrderItem()->getQitemId());
            $item->addChild('Quantity', (int)$qItem->getQty());

        }
        /*
        foreach ($qItems as $qItem) {
            $item = $ItemArray->addChild('Item', null);
            $item->addChild('ItemID', $qItem);
        }
        */

        $result = $api->SendInvoice($xmlItem);

        if ($result instanceof Varien_Simplexml_Config) {
            $resultInformation = $result->getNode()->asArray();
            if ($resultInformation['InvoiceID']) {

                $this->_updated++;

            } else {
                $this->_errors++;
                $rez = 0;
            }

            $message = $resultInformation['Message'];
        } else {
            $this->_errors++;
            $rez = 0;
            $message = $result;
        }

        $listing_log
            ->setMessage($message)
            ->setResult($rez)
        ;
        $listing_log->save();


        if (!$this->_quiet) {
            //$this->_getSession()->addNotice($message . 'Show detailed log: <a href="' . Mage::helper('adminhtml')->getUrl('quicksales/adminhtml_listing/productlog', array('id' => $listing_log->getId())) . '">Details</a>');
            $this->_getSession()->addNotice($message);
        }

        return $result;

    }

    public function send($order = null, $invoice = null)
    {
        $data = array();

        if ($order->getInsurance()) {
            $data['Insurance'] = $order->getInsurance();
        }

        if ($order->getShippingAmount()) {
            $data['Postage'] = $order->getShippingAmount();
        }

        if ($order->getDiscountAmount()) {
            $data['SellerDiscountORCharges'] = $order->getDiscountAmount();
        }

        $xmlResponce = $this->sendInvoice($order, $invoice, $data);

        if (!($xmlResponce instanceof Varien_Simplexml_Config)) {
            return false;
        }

        $quicksaleOrderId = $order->getQuicksalesOrderId();

        if ($quicksaleOrderId) {
            $quicksaleOrderId .= '-';
        }

        $quicksaleOrderId .= (string)$xmlResponce->getNode('InvoiceID');

        $invoice->setQuicksalesOrderId((string)$xmlResponce->getNode('InvoiceID'));
        $invoice->save();

        $order->setQuicksalesOrderId($quicksaleOrderId);
        $order->save();

        file_put_contents('SendInvoice_Response.xml', $xmlResponce->getXmlString());
        //die('@');
    }

}