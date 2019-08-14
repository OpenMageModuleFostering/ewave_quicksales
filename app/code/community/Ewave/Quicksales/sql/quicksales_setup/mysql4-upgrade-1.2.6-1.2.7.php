<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup->updateAttribute('quicksales_listing',
                        'payments',
                        'backend_model',
                        'quicksales/attribute_backend_multiselect'
);

$setup->updateAttribute('quicksales_listing',
                        'posttolocation',
                        'backend_model',
                        'quicksales/attribute_backend_multiselect'
);

$setup
        ->addAttribute('quicksales_listing', 'vshop_category', array(
            'group' => 'Step 2',
            'input' => 'select',
            'label' => 'Vshop Category',
            'source' => 'quicksales/source_vshopcategory',
            'required' => false,
            'position' => 1,
            'sort_order' => 45,
            'visible' => 1,
            )
        );

$installer->endSetup();