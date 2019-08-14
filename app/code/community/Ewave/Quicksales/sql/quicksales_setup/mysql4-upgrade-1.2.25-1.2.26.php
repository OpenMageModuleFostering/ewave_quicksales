<?php
$installer = $this;
$installer->startSetup();

$installer->run("
        ALTER TABLE
        {$this->getTable('listing_log')}
          ADD `type` int(1) unsigned NOT NULL default 1
")
/*
->run("
        UPDATE
          {$this->getTable('listing_log')}
          SET
            result = 0
          WHERE
            message LIKE '%Errors: 1%'
            OR
            message LIKE '%Item not found%'
            OR
            message LIKE '%o payment methods detec%'
            OR
            message LIKE '%uto1Min cannot be selected for BuyNow Only item%'
            ;
")
*/
;

$installer->endSetup();