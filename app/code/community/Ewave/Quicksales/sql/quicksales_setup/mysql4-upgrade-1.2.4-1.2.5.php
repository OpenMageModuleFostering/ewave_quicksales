<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 11:34
 * To change this template use File | Settings | File Templates.
 */
 
$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core  _setup');

$setup
        ->addAttribute('quicksales_listing', 'associate_attributes_label', array(
            'group' => 'Step 3',
            'input' => 'multiline',
            'label' => 'Associate attributes',
            'count' => 1,
            'multiline_count' => 1,
            'required' => false,
            'position' => 1,
            'sort_order' => 80,
            'visible' => 1,
            )
        )
;

$installer->endSetup();