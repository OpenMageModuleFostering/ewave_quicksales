<?php

$installer = $this;
$installer->startSetup();

$status = Mage::getModel('sales/order_status')->load('qnot_checked_out');

$status->setData(
    array(
        'status' => 'qnot_checked_out',
        'label' => 'Not Checked Out',
        'state' => 'qnot_checked_out',
        'state_label' => 'Not Checked Out',
    )
);

$status->assignState('qnot_checked_out');

$status->save();

$installer->endSetup();