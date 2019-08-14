<?php
class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step2_Renderer_Depended
    extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
{
    protected $_group = null;

    public function setGroup($group) {
        $this->_group = $group;
        return $this;
    }
    /**
     * Element output getter
     *
     * @return string
     */
    public function getElementHtml()
    {
        $result = new StdClass;
        $result->output = '';

        // replace the element field with a form
        $element = $this->_element;
        $content = Mage::app()->getLayout()->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step2_depended',
                                                       'adminhtml_listing_edit_tab_additional_step2_' . $this->_group);
        $block = $content
                ->setParentElement($element)
                ->setGroup($this->_group)
                ->setListingEntity(Mage::registry('current_listing')

        );
        $result->output = $block->toHtml();

        // make the profile element dependent on is_recurring


        $dependencies = Mage::app()->getLayout()->createBlock(
            'adminhtml/widget_form_element_dependence',
            'adminhtml_listing_pricing_dependence')
                ->addFieldMap('default_'.$this->_group.'_conf', 'listing[default_'.$this->_group.'_conf]')
                ->addFieldMap($element->getHtmlId(), $element->getName())
                ->addFieldDependence($element->getName(), 'listing[default_'.$this->_group.'_conf]', '0')
                ->addConfigOptions(array('levels_up' => 2));
        $result->output .= $dependencies->toHtml();

        return $result->output;
    }
}
 
