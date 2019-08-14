<?php
class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step1_Renderer_Status extends Varien_Data_Form_Element_Select
{
    public function getElementHtml()
    {

        $this->setReadonly(true, true);
        $html = parent::getElementHtml();

        if ($this->_renderer->getRequest()->getParam('id')) {
            $stop = $this->_renderer->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('quicksales')->__('Stop'),
                'onclick' => 'setLocation(\'' . $this->_renderer->getUrl('*/*/stop', array('id' => $this->_renderer->getRequest()->getParam('id'))) . '\')',
            ));

            $html .= '<br />' . $stop->toHtml();


//            $start = $this->_renderer->getLayout()
//                ->createBlock('adminhtml/widget_button')
//                ->setData(array(
//                'label' => Mage::helper('quicksales')->__('Start'),
//                'onclick' => 'setLocation(\'' . $this->_renderer->getUrl('*/*/start', array('id' => $this->_renderer->getRequest()->getParam('id'))) . '\')',
//            ));
//
//            $html .= '&nbsp;&nbsp;&nbsp;' . $start->toHtml();

            $relist = $this->_renderer->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label' => Mage::helper('quicksales')->__('Relist'),
                'onclick' => 'setLocation(\'' . $this->_renderer->getUrl('*/*/relist', array('id' => $this->_renderer->getRequest()->getParam('id'))) . '\')',
            ));
            $html .= '&nbsp;&nbsp;&nbsp;' . $relist->toHtml();
        }
        return $html;
    }

}