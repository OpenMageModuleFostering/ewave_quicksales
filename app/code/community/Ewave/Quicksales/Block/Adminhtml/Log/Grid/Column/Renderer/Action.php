<?php

class Ewave_Quicksales_Block_Adminhtml_Log_Grid_Column_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    /**
     * Renders column
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        if (!$row->getListingId()) {
            return '&nbsp;';
        } else {
            return parent::render($row);
        }

    }
}