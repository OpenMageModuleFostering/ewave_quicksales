<?php
class Ewave_Quicksales_Model_Listing_Attribute_Backend_Listingupgrade
extends Mage_Eav_Model_Entity_Attribute_Backend_Serialized
{
    /**
     * Serialize or remove before saving
     * @param Ewave_Quicksales_Model_Listing $listing
     */
    public function beforeSave($listing)
    {
        if (!$listing->hasDefaultListingUpgConfiguration() || !$listing->getDefaultListingUpgConfiguration()) {
            parent::beforeSave($listing);
        } else {
            $listing->unsetListingUpgradeInformation();
        }
    }

    /**
     * Unserialize or remove on failure
     * @param Ewave_Quicksales_Model_Listing $listing
     */
    protected function _unserialize(Varien_Object $listing)
    {
        if (!$listing->hasDefaultListingUpgConfiguration() || !$listing->getDefaultListingUpgConfiguration()) {
            parent::_unserialize($listing);
        } else {
            $listing->unsetListingUpgradeProfile();
        }

    }
}