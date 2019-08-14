<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 27.09.11
 * Time: 17:42
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Model_Listing_Action extends Mage_Core_Model_Abstract {


    public function stop($listingId)
    {
        $listing = Mage::getModel('quicksales/listing')->load($listingId);

        Mage::getModel('quicksales/api_action')->send($listing, 1);

        $listing->setStatus(0)->save();
    }

    public function start($listingId)
    {
        $listing = Mage::getModel('quicksales/listing')->load($listingId);

        Mage::getModel('quicksales/api_action')->send($listing, 0);

        $listing->setStatus(1)->save();
    }

    public function relist($listingId)
    {
        $listing = Mage::getModel('quicksales/listing')->load($listingId);
        Mage::getModel('quicksales/api_action')->send($listing, null, null, true);
        $listing->setStatus(1)->save();

    }
}
