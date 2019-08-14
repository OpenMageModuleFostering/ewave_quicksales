<?php
/*
class DebugSoapClient extends SoapClient
{


	public function __doRequest($request, $location, $action, $version, $one_way = 0)
	{
		$return = parent::__doRequest($request, $location, $action, $version, $one_way);
//        echo print_r($request); exit;
//        $fd = fopen('res.zip', 'wb');
//        fwrite($fd, $return);
//        fclose($fd);
//        exit;
        echo $request . "\n\n" . $return; exit;
		return $return;
	}


}
*/
class Ewave_Quicksales_Model_Api extends Mage_Core_Model_Abstract
{

    protected $_client = null;

    protected $_is_sand = null;

    protected $_quiet = false;

    protected function _construct()
    {
        parent::_construct();
        $this->_is_sand = Mage::getStoreConfig('quicksales/settings/sandbox');
    }

    public function isSand() {
        return $this->_is_sand;
    }

    public function setQuiet($flag) {
        $this->_quiet = $flag;
    }

    public function createClient()
    {
        try {
//            $this->_is_sand = Mage::getStoreConfig('quicksales/settings/sandbox');

            if ($this->isSand()) {
                $url = "http://sandboxapi.quicksales.com.au/ws/wsapi.asmx?WSDL";
            } else {
                $url = "http://developer.quicksales.com.au/ws/wsapi.asmx?WSDL";
            }

            // The WSDL cache should be set to on to prevent the WSDL being loaded everytime.
            ini_set("soap.wsdl_cache_enabled", "1");

            // Create a new SoapClient referencing the Quicksales WSDL file.
            $this->_client = new SoapClient($url, array('soap_version' => SOAP_1_2, 'trace' => TRUE));

            return $this;

        } catch (SoapFault $e) {

            Mage::logException($e);
            return null;

        } catch (Exception $e) {

            Mage::logException($e);
            return null;
        }
    }

    public function __call($method, $params)
    {
        $this->_onDate = date('d/m/Y h:m:s t');
        try {

            $xmlOblect = null;
            if ($this->_client == null) {
                $this->createClient();
            }

            if (!empty($params)) {
                $xmlOblect = $params[0];
            }

            /*
            $headerSecurityStr = "<AuthHeader>
                                    <cre>
                                        <APIName>" . $method . "</APIName>
                                        <APIVersion>" . Mage::getStoreConfig('quicksales/settings/api_version') . "</APIVersion>
                                        <DevID>" . Mage::getStoreConfig('quicksales/settings/dev_id') . "</DevID>
                                        <DevToken>" . Mage::getStoreConfig('quicksales/settings/dev_token') . "</DevToken>
                                    </cre>
                                  </AuthHeader>";

            // Create a new SoapVar using the header security string.
            $headerSecurityVar = new SoapVar($headerSecurityStr, XSD_ANYXML);

            // Create a new SoapHeader containing all your login details.
            $soapHeader = new SoapHeader('wsse:http://schemas.xmlsoap.org/ws/2002/04/secext', 'soapenv:Header', $headerSecurityVar);

            // Add the SoapHeader to your SoapClient.
            $this->_client->__setSoapHeaders(array($soapHeader));
            */

            $request['cre'] = array(
                'APIName' => $method,
                'APIVersion' => Mage::getStoreConfig('quicksales/settings/api_version'),
                'DevID' => Mage::getStoreConfig('quicksales/settings/dev_id'),
                'DevToken' => Mage::getStoreConfig('quicksales/settings/dev_token')
            );


            if (!($xmlOblect instanceof Varien_Simplexml_Config)) {
                $xmlOblect = new Varien_Simplexml_Config(Mage::getModuleDir('', 'Ewave_Quicksales') . DS . 'xml' . DS . $method . '_Request.xml');
            }
            //            $request['xmlDocReq'] = new SoapVar('<ns1:xmlDocReq>' . $xmlOblect->getXmlString() .'</ns1:xmlDocReq>', XSD_ANYXML);
            $request['sXMLDocReq'] = $xmlOblect->getXmlString(); //new SoapVar('<ns1:sXMLDocReq>' . $xmlOblect->getXmlString() .'</ns1:sXMLDocReq>', XSD_ANYXML);

            file_put_contents($method . '.xml', $xmlOblect->getXmlString());

            $result = $this->_client->__soapCall($method, array($request));

            if ($method == 'GetVshopCategories') {
                $method = 'GetvShopCategories';
            }

            $responce = $method . 'Result';

            if ($result->$responce) {
                $xmlStr = $result->$responce;
            } else {
                $xmlStr = $result->ErrorResponse;
            }

            $xmlResult = new Varien_Simplexml_Config();
            $xmlResult->loadString($xmlStr);
            $this->_onDate = $xmlResult->getNode('OnDate');

            file_put_contents($method . '_Result.xml', $xmlResult->getXmlString());
            if ($xmlResult->getNode('ErrorCode')) {
                throw new Exception($xmlResult->getNode('ErrorCode') . ': ' . $xmlResult->getNode('ErrorMsg'));
            }

            return $xmlResult;

        } catch (Exception  $e) {
            
            if (!$this->_quiet) {
                $this->_getSession()->addError($method . ': ' .$e->getMessage());
            }

            Mage::logException($e);
            return $e->getMessage();
        }
    }

    public function getOnDate() {
        
        return $this->_onDate;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

}
