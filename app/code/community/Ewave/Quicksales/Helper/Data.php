<?php

class Ewave_Quicksales_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_products = null;

    public function getListing() {

        return Mage::registry('current_listing');
    }
    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductsCollection()
    {
        if (is_null($this->_products)) {
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $this->_products = Mage::getModel('catalog/product')->getCollection();
            $this->_products->addIdFilter($this->getProductIds());
        }

        return $this->_products;
    }

    public function getProductIds()
    {
        $session = Mage::getSingleton('admin/session');
        $product_ids = $session->getQuicksalesProductIds();
        
        $post = Mage::helper('core/http')->_getRequest()->getPost();

        if (!is_array($product_ids) && !empty($post) && $session->hasQuicksalesProductIds()) {
            return array();
        } elseif (!is_array($product_ids) || (count($product_ids) == 1 && $product_ids[0] == "")) {
            $listing = $this->getListing();

            $products = $listing->getAssignedProducts();

            $product_ids = array();
                foreach ($products as $product) {
                    $product_ids[] = $product->getId();
                }
            
        }

        return $product_ids;
    }

    public function getUsedMagentoAttributeId($qs_attribute_id)
    {
        $session = Mage::getSingleton('admin/session');
        $attributes = $session->getQuicksalesAttrubutesAssign();
        if (!is_array($attributes) || !isset($attributes[$qs_attribute_id])) {

            $listing = $this->getListing();
            $associatedAttributes = $listing->getAttributesAssociation();
            if (isset($associatedAttributes[$qs_attribute_id])) {
                return $associatedAttributes[$qs_attribute_id];
            } else {
                return '';
            }
        }

        return $attributes[$qs_attribute_id];
    }

    public function getAssociatedGridHtml($qc_id, $code = null)
    {
        if (!$code) {
            $code = $this->getUsedMagentoAttributeId($qc_id);
        }

        if (!$code) {
            return '';
        }

        $attribute = Mage::getModel('eav/entity_attribute')->load($code);

        $collection = $this->getProductsCollection();
//        $collection->addAttributeToSelect($attribute->getAttributeCode());
        $collection->addAttributeToSelect('*');

        $str = '';
        list($qAttributes, $qValues) = Mage::getModel('quicksales/api_gettags')->getTagsValues();
        $used = array();

        $listing = $this->getListing();
        $associatedValues = $listing->getAttributeValuesAssociation();

        foreach ($collection->getItems() as $i) {
            /* @var $i Mage_Catalog_Model_Product */
            $label = $value = $i->getData($attribute->getAttributeCode());
            /*
            if (!$value && $i->getTypeId() == 'configurable') {
                $configurableAttributes = $i->getTypeInstance(true)->getConfigurableAttributesAsArray($i);
                foreach($configurableAttributes as $configurableAttribute) {
                    if ($configurableAttribute['attribute_id'] == $attribute->getId()) {
                        $label = $value = $configurableAttribute['values'];
                    }
                }
            }
            */

            if (!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $v) {
                if (isset($used[$v]) || empty($v)) {
                    continue;
                }
                if (is_array($v)) {
                    $label = $v['label'];
                    $v = $v['value_index'];
                }

                $used[$v] = true;
                if ($label && $i->getAttributeText($attribute->getAttributeCode())) {
                    $label = $i->getAttributeText($attribute->getAttributeCode());
                }


                $selected = "\n";
                if (!empty($associatedValues[$qc_id])) {
                    $selected = $associatedValues[$qc_id][$v];
                }
                $str .= '<tr><td class="half">' . htmlspecialchars($label) . '</td><td class="half"><select name="listing[attribute_values_association][' . $qc_id . '][' . $v . ']">';
                foreach ($qValues[$qc_id] as $valueInfo) {
                    $str .= "<option value='" . $valueInfo['id'] . "' " . (($selected == $valueInfo['id'])
                            ? " selected='selected' " : "") . ">" . htmlspecialchars($valueInfo['name']) . "</option>";
                }

                $str .= '</select></td></tr>';

                $str .= "\n";
            }
        }

        return $str;
    }

}
