<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 11:30
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{

    /**
     * Enter description here...
     *
     * @param unknown_type $baseName
     * @param array $options
     * - no-main
     * - no-default-types
     * - types
     * @return unknown
     */
    public function createEntityTables($baseName, array $options = array())
    {
        $sql = '';

        if (empty($options['no-main'])) {
            $sql = "
DROP TABLE IF EXISTS `{$baseName}`;
CREATE TABLE `{$baseName}` (
`entity_id` int(10) unsigned NOT NULL auto_increment,
`entity_type_id` smallint(8) unsigned NOT NULL default '0',
`attribute_set_id` smallint(5) unsigned NOT NULL default '0',
`increment_id` varchar(50) NOT NULL default '',
`parent_id` int(10) unsigned NULL default '0',
`store_id` smallint(5) unsigned NOT NULL default '0',
`created_at` datetime NOT NULL default '0000-00-00 00:00:00',
`updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
`is_active` tinyint(1) unsigned NOT NULL default '1',
PRIMARY KEY  (`entity_id`),
CONSTRAINT `FK_{$baseName}_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        }

        $types = array(
            'datetime' => 'datetime',
            'decimal' => 'decimal(12,4)',
            'int' => 'int',
            'text' => 'text',
            'varchar' => 'varchar(255)',
        );
        if (!empty($options['types']) && is_array($options['types'])) {
            if ($options['no-default-types']) {
                $types = array();
            }
            $types = array_merge($types, $options['types']);
        }

        foreach ($types as $type => $fieldType) {
            $sql .= "
DROP TABLE IF EXISTS `{$baseName}_{$type}`;
CREATE TABLE `{$baseName}_{$type}` (
`value_id` int(11) NOT NULL auto_increment,
`entity_type_id` smallint(8) unsigned NOT NULL default '0',
`attribute_id` smallint(5) unsigned NOT NULL default '0',
`store_id` smallint(5) unsigned NOT NULL default '0',
`entity_id` int(10) unsigned NOT NULL default '0',
`value` {$fieldType} NOT NULL,
PRIMARY KEY  (`value_id`),
UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
" . ($type !== 'text' ? "
KEY `value_by_attribute` (`attribute_id`,`value`),
KEY `value_by_entity_type` (`entity_type_id`,`value`),
" : "") . "
CONSTRAINT `FK_{$baseName}_{$type}` FOREIGN KEY (`entity_id`) REFERENCES `{$baseName}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_{$type}_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_{$type}_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
CONSTRAINT `FK_{$baseName}_{$type}_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        }

        try {
            $this->_conn->multi_query($sql);
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }


    public function getDefaultEntities()
    {
        return array(
            'quicksales_listing' =>
            array(
                'entity_model' => 'quicksales/listing',
                'attribute_model' => 'catalog/resource_eav_attribute',
                'table' => 'quicksales/listing',
                'default_group' => 'General Information',
                'default_group' => 'Step 1',
                'attributes' => array(
                    'hot_deals' => array(
                        'group' => 'Assigned Tabs',
                        'label' => 'Hot deals',
                        'required' => false,
                        'position' => 1,
                        'sort_order' => 40,
                        'visible' => 1,
                    )
                )

            )
        );
    }

}
