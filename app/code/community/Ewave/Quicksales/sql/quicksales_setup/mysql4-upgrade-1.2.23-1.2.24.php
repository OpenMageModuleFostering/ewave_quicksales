<?php

$installer = $this;
$installer->startSetup();

$setup = new Ewave_Quicksales_Model_Entity_Setup('core_setup');

$types = array(
    'datetime',
    'decimal',
    'int',
    'text',
    'varchar'
);
$baseTableName = 'listing';

foreach ($types as $type) {
    $eavTableName = $baseTableName . '_' . $type;

    $readConnection = $setup->getConnection();

    $query = 'SELECT  `table`.`entity_id`, `table`.`attribute_id`, COUNT(*) AS cnt
      FROM `' . $setup->getTable($eavTableName) .'` AS `table`
      GROUP BY  `table`.`entity_id`, `table`.`attribute_id` HAVING cnt > 1;';

    $results = $readConnection->fetchAll($query);

    if (!empty($results)) {
        foreach ($results as $data) {
            $query = 'DELETE FROM
              FROM `' . $setup->getTable($eavTableName) . '` AS `table`
              WHERE `table`.`entity_id` = '.$data['entity_id'].' AND `table`.`attribute_id` = ' . $data['attribute_id'] . '
              LIMIT '.($data['cnt'] - 1).';';

        }
    }
    /**
     * Add missed indexes (unique attribute value for each listing)
     */

/*
    $setup->getConnection()->addIndex(
        $setup->getTable($eavTableName),
        $setup->getIdxName(
            $eavTableName,
            array('entity_id', 'attribute_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'attribute_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
*/
}
$installer->endSetup();