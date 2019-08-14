<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->removeAttribute('quicksales_listing', 'default_shipping_conf');
$setup->removeAttribute('quicksales_listing', 'shipping_information');

$setup->removeAttribute('quicksales_listing', 'shipping');


$setup->addAttribute('quicksales_listing', 'default_shipping_conf', array(
    'group'             => 'Step 2',
    'type'              => 'int',
    'label'             => 'Use default shipping information',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
));


// recurring shipping profile
$setup->addAttribute('quicksales_listing', 'shipping_information', array(
    'group'             => 'Step 2',
    'type'              => 'text',
    'backend'           => 'quicksales/listing_attribute_backend_shipping',
    'label'             => 'shipping information',
    'input'             => 'text', // doesn't matter
    'required'          => false,
));

$installer->endSetup();