<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->updateAttribute('quicksales_listing', 'default_shipping_conf', 'default_value', 1);
$setup->updateAttribute('quicksales_listing', 'default_listing_conf', 'default_value', 1);
$setup->updateAttribute('quicksales_listing', 'default_listing_upgrade_conf', 'default_value', 1);
$setup->updateAttribute('quicksales_listing', 'default_payment_conf', 'default_value', 1);
$setup->updateAttribute('quicksales_listing', 'default_pricing_conf', 'default_value', 1);


$installer->endSetup();