<?php
class Ewave_Quicksales_Model_Listing_Attribute_Backend_Listing
extends Mage_Eav_Model_Entity_Attribute_Backend_Serialized
{
    /**
     * Serialize or remove before saving
     * @param Ewave_Quicksales_Model_Listing $listing
     */
    public function beforeSave($listing)
    {
        if (!$listing->hasDefaultListingConfiguration() || !$listing->getDefaultListingConfiguration()) {
            parent::beforeSave($listing);
        } else {
            $listing->unsetListingInformation();
        }
    }

    /**
     * Unserialize or remove on failure
     * @param Ewave_Quicksales_Model_Listing $listing
     */
    protected function _unserialize(Varien_Object $listing)
    {
        if (!$listing->hasDefaultListingConfiguration() || !$listing->getDefaultListingConfiguration()) {
            parent::_unserialize($listing);
        } else {
            $listing->unsetListingInformation();
        }

    }
}