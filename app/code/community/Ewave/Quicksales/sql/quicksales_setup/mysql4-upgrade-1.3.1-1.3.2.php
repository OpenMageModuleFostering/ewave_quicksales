<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');


$setup->addAttribute('order', 'qpayment_info', array(
    'type'              => 'text',
    'input'             => 'hidden',
    'required'          => false,
));

$installer->endSetup();