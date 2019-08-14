<?php

class Ewave_Quicksales_Helper_Listing extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SITEMAP_VALID_PATHS = 'general/file/sitemap_generate_valid_paths';

    /**
     * Attribute Tab block name for product edit
     *
     * @var string
     */
    protected $_attributeTabBlock = null;

    /**
     * Retrieve Attribute Tab Block Name for Product Edit
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        return $this->_attributeTabBlock;
    }

    /**
     * Set Custom Attribute Tab Block Name for Product Edit
     *
     * @param string $attributeTabBlock
     * @return Mage_Adminhtml_Helper_Catalog
     */
    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }

    /**
     * Get list valid paths for generate a sitemap XML file
     *
     * @return array
     */
    public function getSitemapValidPaths()
    {
        $path = Mage::getStoreConfig(self::XML_PATH_SITEMAP_VALID_PATHS);
        /** @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');
        $path = array_merge($path, $helper->getPublicFilesValidPath());
        return $path;
    }
}
