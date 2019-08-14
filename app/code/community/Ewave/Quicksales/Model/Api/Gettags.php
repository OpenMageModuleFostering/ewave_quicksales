<?php

class Ewave_Quicksales_Model_Api_Gettags extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    private $_attributeTags = array(
        'Attr1',
        'Attr2',
        'Attr3',
        'Attr4',
        'Attr5',
        'Attr6',
        'Attr7',
        'Attr8',
        'Attr9',
        'Attr10',

    );

    private $qAttributes = array();
    private $qAttributesValues = array();

    public function getAttributes($categoryId)
    {

        if (!$categoryId) {
            return false;
        }

        if ($this->qAttributes[$categoryId]) {
            return array($this->qAttributes[$categoryId], $this->qAttributesValues[$categoryId]);
        }

        $xmlOblect = new Varien_Simplexml_Config($path = Mage::getModuleDir('', 'Ewave_Quicksales') . DS . 'xml' . DS . 'GetTags_Request.xml');
        $param = $xmlOblect->getNode('CategoryNum');
        $param[0] = $categoryId;
        $xmlResponce = Mage::getModel('quicksales/api')->GetTags($xmlOblect);

        if (!($xmlResponce instanceof Varien_Simplexml_Config)) {
            return false;
        }

        $attributes = $xmlResponce->getNode('Tags/AttrSet');

        $attrSet = $attributes->asArray();

        $session = $this->_getSession();

        $session->unsAttrSets();
        $attrSets = $session->getAttrSets();
        $attrSets[$categoryId] = $attrSet;

        $this->_getSession()->setAttrSets($attrSets);

        $this->qAttributes[$categoryId] = array();
        $this->qAttributesValues[$categoryId] = array();


        foreach ($this->_attributeTags as $attributeTag) {
            $attributeObj = $attributes->$attributeTag;
            $attributeId = $attributeObj->getAttribute('AttrID');
            $attributeName = $attributeObj->getAttribute('AttrName');
            if (!$attributeId) {
                continue;
            }
            $this->qAttributes[$categoryId][] = array(
                'id' => $attributeId,
                'name' => $attributeName
            );

            $this->qAttributesValues[$categoryId][$attributeId] = array();

            foreach ($attributeObj->Value as $valueTag) {
                $this->qAttributesValues[$categoryId][$attributeId][] = array(
                    'id' => $valueTag->getAttribute('ValueID'),
                    'name' => $valueTag->getAttribute('ValueName'),
                );
            }

        }

        $return = array($this->qAttributes[$categoryId], $this->qAttributesValues[$categoryId]);
        $this->_getSession()->setQuicksalesCategoriesTags($return);

        return $return;
    }


    public function getTagsValues()
    {
        $ses_data = $this->_getSession()->getQuicksalesCategoriesTags();
        if ($ses_data) {
            return $ses_data;
        }

        return array();
    }

    public function _getSession()
    {
        return Mage::getSingleton('admin/session');
    }

    public function getSavedAttrSet($categoryId)
    {
        $attrSets = $this->_getSession()->getAttrSets();

        if (!$attrSets[$categoryId]) {
            $this->getAttributes($categoryId);
            $attrSets = $this->_getSession()->getAttrSets();
        }
        return $attrSets[$categoryId];
    }

    public function getAttrSet($categoryId)
    {
        $savedSets = $this->getSavedAttrSet($categoryId);
        return $savedSets;
    }
}
