<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');
/*
$setup->updateAttribute('quicksales_listing',
                        'payments',
                        'backend_model',
                        'quicksales/attribute_backend_multiselect'
);
*/
$setup->removeAttribute('quicksales_listing', 'default_pricing_conf');
$setup->removeAttribute('quicksales_listing', 'pricing_information');

$setup->addAttribute('quicksales_listing', 'default_pricing_conf', array(
    'group'             => 'Step 2',
    'type'              => 'int',
    'label'             => 'Use default Pricing Configuration',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'required'          => false,
));

// recurring payment profile
$setup->addAttribute('quicksales_listing', 'pricing_information', array(
    'group'             => 'Step 2',
    'type'              => 'text',
    'backend'           => 'quicksales/listing_attribute_backend_pricing',
    'label'             => 'Pricing information',
    'input'             => 'text', // doesn't matter
    'required'          => false,
));

$installer->endSetup();