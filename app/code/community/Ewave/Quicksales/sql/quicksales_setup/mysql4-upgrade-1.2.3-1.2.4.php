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
        ->removeAttribute('quicksales_listing', 'fixedpostage')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_nsw')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_vic')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_qld')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_act')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_nt')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_tas')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_sa')
        ->removeAttribute('quicksales_listing', 'fixedpostagebylocation_wa')
        ->removeAttribute('quicksales_listing', 'offerapregular')
        ->removeAttribute('quicksales_listing', 'offerapexpress')
        ->removeAttribute('quicksales_listing', 'providereturnrefundpolicy')
        ->removeAttribute('quicksales_listing', 'posttolocation')
        ->removeAttribute('quicksales_listing', 'postinst')
        ->removeAttribute('quicksales_listing', 'description')

        ->addAttribute('quicksales_listing', 'fixedpostage', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'Fixed Postage',
            'position' => 1,
            'sort_order' => 80,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_nsw', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'NSW',
            'position' => 1,
            'sort_order' => 90,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_vic', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'VIC',
            'position' => 1,
            'sort_order' => 100,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_qld', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'GLD',
            'position' => 1,
            'sort_order' => 110,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_act', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'ACT',
            'position' => 1,
            'sort_order' => 120,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_nt', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'NT',
            'position' => 1,
            'sort_order' => 130,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_tas', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'TAS',
            'position' => 1,
            'sort_order' => 140,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_sa', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'SA',
            'position' => 1,
            'sort_order' => 150,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'fixedpostagebylocation_wa', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'WA',
            'position' => 1,
            'sort_order' => 160,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'offerapregular', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'OfferAPRegular',
            'position' => 1,
            'sort_order' => 170,
            'visible' => 1,
            'source' => 'eav/entity_attribute_source_boolean',
            )
        )
        ->addAttribute('quicksales_listing', 'offerapexpress', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'OfferAPExpress',
            'position' => 1,
            'sort_order' => 180,
            'visible' => 1,
            'source' => 'eav/entity_attribute_source_boolean',
            )
        )
        ->addAttribute('quicksales_listing', 'providereturnrefundpolicy', array(
            'group' => 'Step 2',
            'input' => 'text',
            'label' => 'ProvideReturnRefundPolicy',
            'position' => 1,
            'sort_order' => 190,
            'visible' => 1,
            'source' => 'eav/entity_attribute_source_boolean',
            )
        )
        ->addAttribute('quicksales_listing', 'posttolocation', array(
            'group' => 'Step 2',
            'input' => 'multiselect',
            'label' => 'PostToLocation',
            'source' => 'quicksales/source_posttolocation',
            'position' => 1,
            'sort_order' => 200,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'postinst', array(
            'group' => 'Step 2',
            'input' => 'textarea',
            'label' => 'Postage instructions',
            'note' => '1,000 chars maximum',
            'class' => 'validate-length maximum-length-1000',
            'frontend_class' => 'validate-length maximum-length-1000',
            'required' => false,
            'position' => 1,
            'sort_order' => 210,
            'visible' => 1,
            )
        )
        ->addAttribute('quicksales_listing', 'description', array(
            'group' => 'Step 2',
            'input' => 'textarea',
            'label' => 'Listing description',
            'note' => '600,000 chars maximum',
            'class' => 'validate-length maximum-length-600000',
            'frontend_class' => 'validate-length maximum-length-600000',
            'required' => false,
            'position' => 1,
            'sort_order' => 220,
            'visible' => 1,
            )
        )

;

$installer->endSetup();