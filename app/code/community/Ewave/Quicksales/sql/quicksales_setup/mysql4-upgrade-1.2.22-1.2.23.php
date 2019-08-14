<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');


$setup->addAttribute('order', 'quicksales_order_id', array(
    'type'              => 'int',
    'input'             => 'hidden',
    'required'          => false,
    'default'           => 0
));

$installer->endSetup();