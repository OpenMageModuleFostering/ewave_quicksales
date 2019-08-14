<?php
/**
 */
class Ewave_Quicksales_Model_Api_Action extends Mage_Core_Model_Abstract
{

    protected $_quiet = false;
    protected $_is_sand = false;

    /* @var $_api Ewave_Quicksales_Model_Api */
    protected $_api = null;


    protected function _construct()
    {
        parent::_construct();
        $this->_is_sand = Mage::getStoreConfig('quicksales/settings/sandbox');
        $this->_api = Mage::getModel('quicksales/api');
    }

    public function setQuiet($flag)
    {
        $this->_quiet = $flag;
        $this->_api->setQuiet($flag);
        return $this;
    }

}