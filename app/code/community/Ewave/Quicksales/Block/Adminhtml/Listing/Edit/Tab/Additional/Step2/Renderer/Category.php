<?php
class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step2_Renderer_Category extends Varien_Data_Form_Element_Select
{
    public function getElementHtml()
    {
        $data = array(
            'name' => 'category',
        );
        $hidden = new Varien_Data_Form_Element_Hidden($data);
        $hidden->setForm($this->getForm());

        $hidden->setId('category');
        $hidden->setValue($this->getValue());
        $hidden->addClass('required-entry');

        $this->setName('category_selector');
        $this->setId('category_selector');
	$this->removeClass('required-entry');

        $html = parent::getElementHtml();


        $changeButton = $this->_renderer->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData(array(
                               'label' => Mage::helper('quicksales')->__('Change'),
                               'onclick' => '$(\'category_selector\').show();',
                          ));

        $applyButton = $this->_renderer->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData(array(
                               'label' => Mage::helper('quicksales')->__('Apply'),
                               'style' => 'display: none',
                               'id' => 'apply_category',
                               'onclick' => 'listingSettingsObj.changeCategory();',
                          ));

        $allCategories = Mage::getModel('quicksales/api_getcategories')->getAllCategories();

        $allCategoriesCache = Mage::getModel('quicksales/api_getcategories')->getCategoryCache();

        $currentCategoryLabel = $allCategoriesCache[$this->getValue()];


        $currentCategory = array();
        foreach ($allCategories as $category) {
            if ($category['value'] == $this->getValue()) {
                $currentCategory = $category;
                break;
            }
        }

        while(!empty($allCategoriesCache[$currentCategory['parent']])) {
            $currentCategoryLabel = $allCategoriesCache[$currentCategory['parent']] . ' / ' . $currentCategoryLabel;
            foreach ($allCategories as $category) {
                if ($category['value'] == $currentCategory['parent']) {
                    $currentCategory = $category;
                    break;
                }
            }
        }


        return '<span id="category_label">' . $currentCategoryLabel . '</span>' . $hidden->toHtml() . $changeButton->toHtml() . $html . $applyButton->toHtml();
    }

}
