<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');


$setup->addAttribute('order_item', 'qitem_id', array(
    'type'              => 'varchar',
    'required'          => false,
));

$installer->endSetup();