<?php

class Ewave_Quicksales_Model_Api_Client extends Mage_Core_Model_Abstract
{
    
    protected $_client = null;

    protected $_is_sand = null;

    public function connect($username = null, $password = null)
    {
        $this->_is_sand = Mage::getStoreConfig('quicksales/settings/quicksales_sandbox');
        

        if ($this->_is_sand) {
            $url = "https://sandboxapi.quicksales.com.au/ws/wsapi.asmx?WSDL";
        } else {
            $url = "https://developer.quicksales.com.au/ws/wsapi.asmx?WSDL";
        }

        // The WSDL cache should be set to on to prevent the WSDL being loaded everytime.
        ini_set("soap.wsdl_cache_enabled", "1");

        // Create a new SoapClient referencing the Quicksales WSDL file.
        $this->_client = new SoapClient($url, array('soap_version' => SOAP_1_2, 'trace' => TRUE));
        // Define the security string that wraps your login details. Due to limitations
        // with the PHP language this header information can only be provided via a string.
        $headerSecurityStr = 

        "<API_Name", "Category_GetCategories")
        "<API_Version", "1")
        "<DevID", "Insert your devid here")
        "<DevToken", "insert your devtoken here")

"<Security><UsernameToken><Username>" . $username . "</Username>".
                             "<Password>" . $password . "</Password></UsernameToken></Security>";

        // Create a new SoapVar using the header security string.
        $headerSecurityVar = new SoapVar($headerSecurityStr, XSD_ANYXML);

        // Create a new SoapHeader containing all your login details.
        $soapHeader = new SoapHeader('wsse:http://schemas.xmlsoap.org/ws/2002/04/secext', 'soapenv:Header', $headerSecurityVar);

        // Add the SoapHeader to your SoapClient.
        $this->_client->__setSoapHeaders(array($soapHeader));
//print_r(get_class_methods($this->_client));die;
        $result = $this->_client->__doRequest(array());
        return $this;

    }

    /**
     * Gets quotes for a delivery.
     *
     * @param array $request the request parameters, in an array format.
     *
     * @return array
     */
    public function getQuotesByRequest($request)
    {
        if(!$this->_client) {
            return false;
        }

        if (!$this->_is_sand) {
            $request['clientId'] = Mage::helper('temando')->getClientId();
        }
        $response = $this->_client->getQuotesByRequest($request);
        
        $quotes = array();
        foreach ($response->quote as $quote_details) {
            $quotes[] = Mage::getModel('temando/quote')
                ->loadResponse($quote_details);
        }
        
        return $quotes;
    }

    public function makeBookingByRequest($request)
    {
        if (!$this->_is_sand) {
            $request['clientId'] = Mage::helper('temando')->getClientId();
            $request['promotionCode'] = Ewave_Quicksales_Model_Signup_Form::AFFILATE_PROMO;
        }
        if(!$this->_client) {
            return false;
        }

        return $this->_client->makeBookingByRequest($request);
    }

    public function getRequest($request)
    {
        if (!$this->_client) {
            return false;
        }
        
        return $this->_client->getRequest($request);
    }
    
    /**
     * Gets the multiplier for insurance (currently 1%).
     *
     * To add insurance to a quote, the total price should be multiplied by
     * this value.
     */
    public function getInsuranceMultiplier()
    {
        return 1.01; // 1%
    }
    
    public function createClient($request)
    {
        if (!$this->_client) {
            return false;
        }
        
        return $this->_client->createClient($request);
    }

    public function getClient($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->getClient($request);
    }

    public function updateClient($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->updateClient($request);
    }
    
}
