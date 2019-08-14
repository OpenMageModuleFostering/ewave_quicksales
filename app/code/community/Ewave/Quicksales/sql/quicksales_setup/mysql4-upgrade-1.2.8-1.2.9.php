<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->removeAttribute('quicksales_listing', 'default_listing_conf');
$setup->removeAttribute('quicksales_listing', 'listing_information');

$setup->removeAttribute('quicksales_listing', 'default_pricing_configuration');
$setup->removeAttribute('quicksales_listing', 'default_listing_configuration');

$setup->addAttribute('quicksales_listing', 'default_listing_conf', array(
    'group'             => 'Step 2',
    'type'              => 'int',
    'label'             => 'Use default Listing Configuration',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
));

$setup->removeAttribute('quicksales_listing', 'description');
// recurring payment profile
$setup->addAttribute('quicksales_listing', 'listing_information', array(
    'group'             => 'Step 2',
    'type'              => 'text',
    'backend'           => 'quicksales/listing_attribute_backend_listing',
    'label'             => 'Listing information',
    'input'             => 'text', // doesn't matter
    'required'          => false,
));

$installer->endSetup();