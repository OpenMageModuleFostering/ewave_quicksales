<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 26.09.11
 * Time: 14:26
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step1_Grid_Renderer_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{
    public function render(Varien_Object $row)
    {
        $result = parent::render($row);
        return $result.'<input type="hidden" class="value-json" value="'.htmlspecialchars($this->getAttributesJson($row)).'" />';
    }

    public function getAttributesJson(Varien_Object $row)
    {
        $result = array(
            'id' => $row->getEntityId(),
            'name' => $row->getName()
        );

        return Mage::helper('core')->jsonEncode($result);
    }
}
