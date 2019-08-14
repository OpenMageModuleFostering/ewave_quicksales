<?php
class Ewave_Quicksales_Model_Api_Getvshopcategory extends Mage_Core_Model_Abstract
{

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    public function getVshopCategories()
    {
/*
        $xmlOblect = new Varien_Simplexml_Config($path = Mage::getModuleDir('', 'Ewave_Quicksales') . DS . 'xml' . DS . 'GetvShopCategories_Request.xml');
        $param = $xmlOblect->getNode('SellerID');
        */

        $xml = new Varien_Simplexml_Element('<GetVshopCategoryRequest></GetVshopCategoryRequest>');
        $xmlOblect = new Varien_Simplexml_Config($xml);


        $this->_is_sand = Mage::getStoreConfig('quicksales/settings/sandbox');

        $seller = Mage::getStoreConfig('quicksales/settings/vshop_seller');
        $xmlOblect->setNode('SellerID', $seller);

        $xmlResponce = Mage::getModel('quicksales/api')->GetVshopCategories($xmlOblect);

        if (!($xmlResponce instanceof Varien_Simplexml_Config)) {
            
            return false;
        }

        $vCategories = $xmlResponce->getNode('CategoryArray');

        $result = array();
        foreach ($vCategories->Category as $vCategory) {
            $data = $vCategory->asArray();
            $result[] = array(
                'id' => $data['CategoryNum'],
                'level' => $data['CategoryLevel'],
                'parent' => $data['CategoryParentNum'],
                'leaf' => $data['LeafCategory'],
                'name' => $data['CategoryName']
            );
        }

        return $result;
    }

}

?>
 
