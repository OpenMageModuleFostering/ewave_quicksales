<?php

$installer = $this;
$installer->startSetup();

if (Mage::getModel('sales/order_status')->getResource()) {

    $status = Mage::getModel('sales/order_status')->load('qpaid');

    $status->setData(
        array(
            'status' => 'qpaid',
            'label' => 'QS Paid',
            'state' => 'qpaid',
            'state_label' => 'QS Paid',
        )
    );

    $status->assignState('qpaid');

    $status->save();
}

$installer->endSetup();
