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

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$setup
        ->addAttribute('quicksales_listing', 'payments', array(
            'group' => 'Step 2',
            'input' => 'multiselect',
            'label' => 'Payments',
            'source' => 'quicksales/source_payments',
            'required' => true,
            'position' => 1,
            'sort_order' => 50,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'payments_instruction', array(
            'group' => 'Step 2',
            'input' => 'textarea',
            'label' => 'Payments Instruction',
            'note' => '1,000 chars maximum',
            'class' => 'validate-length maximum-length-1000',
            'frontend_class' => 'validate-length maximum-length-1000',
            'required' => false,
            'position' => 1,
            'sort_order' => 60,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'shipping', array(
            'group' => 'Step 2',
            'input' => 'select',
            'label' => 'Shipping',
            'required' => true,
            'position' => 1,
            'sort_order' => 70,
            'visible' => 1,
            'source' => 'quicksales/source_shipping',
            )
        )

;

$installer->endSetup();