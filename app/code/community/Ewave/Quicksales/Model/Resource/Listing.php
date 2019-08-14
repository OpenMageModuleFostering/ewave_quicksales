<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 11:22
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Model_Resource_Listing extends Mage_Eav_Model_Entity_Abstract {

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType(Ewave_Quicksales_Model_Listing::ENTITY)
                ->setConnection(
            $resource->getConnection('quicksales_read'),
            $resource->getConnection('quicksales_write')
        );
    }
}
