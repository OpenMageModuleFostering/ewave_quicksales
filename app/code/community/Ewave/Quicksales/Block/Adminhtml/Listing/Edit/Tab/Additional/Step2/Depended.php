<?php

class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Additional_Step2_Depended extends Mage_Adminhtml_Block_Abstract
{
    protected $_sectionName = 'quicksales_default';
    
    protected $_group = null;

    /**
     * Reference to the parent element (optional)
     *
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_parentElement = null;

    /**
     * Whether the form contents can be editable
     *
     * @var bool
     */
    protected $_isReadOnly = false;

    /**
     *
     * @var Ewave_Quicksales_Model_Listing
     */
    protected $_listing = null;

    protected $_configRoot = null;

    protected $_configDataObject = null;

    protected $_configData = null;

    protected $_configFields = null;

    protected $_defaultFieldsetRenderer = null;

    protected $_defaultFieldRenderer = null;

    public function setGroup($group) {
        $this->_group = $group;
        return $this;
    }


    /**
     * Enter description here...
     *
     * @return Mage_Adminhtml_Block_System_Config_Form
     */
    protected function _initObjects()
    {
        $this->_configRoot = Mage::getConfig()->getNode(null, $this->getScope(), $this->getScopeCode());

        $this->_configDataObject = Mage::getModel('adminhtml/config_data');

        $this->_configData = $this->_configDataObject->load();

        $this->_configFields = Mage::getSingleton('adminhtml/config');

        $this->_defaultFieldsetRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_fieldset');
        $this->_defaultFieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        return $this;
    }

    /**
     * Setter for parent element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     */
    public function setParentElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_parentElement = $element;
        return $this;
    }

    /**
     * Setter for current product
     *
     * @param Ewave_Quicksales_Model_Listing $product
     */
    public function setListingEntity(Ewave_Quicksales_Model_Listing $listing)
    {
        $this->_listing = $listing;
        return $this;
    }

    /**
     * Prepare and render the form
     *
     * @return string
     */
    protected function _toHtml()
    {

        $form = $this->_prepareForm();

        return $form->toHtml() . $this->_getDependence()->toHtml();
    }

    public function prepareElements($elements, $group, $fieldset)
    {

        foreach ($elements as $e) {

		$data = array();

            /**
             * Look for custom defined field path
             */
            $path = (string)$e->config_path;
            if (empty($path)) {
                $path = $this->_section . '/' . $group->getName() . '/' . $e->getName();
            } elseif (strrpos($path, '/') > 0) {
                // Extend config data with new section group
                $groupPath = substr($path, 0, strrpos($path, '/'));
                if (!isset($configDataAdditionalGroups[$groupPath])) {
                    $this->_configData = $this->_configDataObject->extendConfig(
                        $groupPath,
                        false,
                        $this->_configData
                    );
                    $configDataAdditionalGroups[$groupPath] = true;
                }
            }

            $id = $this->_section->getName() . '_' . $group->getName() . '_' . $e->getName();


            if (isset($this->_configData[$path])) {
                $data = $this->_configData[$path];
                $inherit = false;
            } else {
                //                $data = $this->_configRoot->descend($path);
                $inherit = true;
            }
            if ($e->frontend_model) {
                $fieldRenderer = Mage::getBlockSingleton((string)$e->frontend_model);
            } else {
                $fieldRenderer = $this->_defaultFieldRenderer;
            }

            $fieldRenderer->setForm($this);
            $fieldRenderer->setConfigData($this->_configData);

            //'quicksales' = $this->_configFields->getAttributeModule($this->_section, $group, $e);
            $fieldType = (string)$e->frontend_type ? (string)$e->frontend_type : 'text';
            $name = $e->getName();
            $label = Mage::helper('quicksales')->__((string)$e->label);
            $hint = (string)$e->hint ? Mage::helper('quicksales')->__((string)$e->hint) : '';

            if ($e->backend_model) {
                $model = Mage::getModel((string)$e->backend_model);
                if (!$model instanceof Mage_Core_Model_Config_Data) {
                    Mage::throwException('Invalid config field backend model: ' . (string)$e->backend_model);
                }
                $model->setPath($path)
                    ->setValue($data)
                    ->setWebsite($this->getWebsiteCode())
                    ->setStore($this->getStoreCode())
                    ->afterLoad();
                $data = $model->getValue();
            }

            $comment = $this->_prepareFieldComment($e, 'quicksales', $data);
            $tooltip = $this->_prepareFieldTooltip($e, 'quicksales');

            if ($e->depends) {
                foreach ($e->depends->children() as $dependent) {
                    $dependentId = $this->_section->getName()
                        . '_' . $group->getName()
                        . '_'
                        . $dependent->getName();
                    $shouldBeAddedDependence = true;
                    $dependentValue = (string)$dependent;
                    $dependentFieldName = $dependent->getName();
                    $dependentField = $group->fields->$dependentFieldName;
                    /*
                    * If dependent field can't be shown in current scope and real dependent config value
                    * is not equal to preferred one, then hide dependence fields by adding dependence
                    * based on not shown field (not rendered field)
                    */
                    //                    if (!$this->_canShowField($dependentField)) {
                    $dependentFullPath = $this->_section
                        . '/' . $group->getName()
                        . '/'
                        . $dependent->getName();
                    $shouldBeAddedDependence = $dependentValue != Mage::getStoreConfig(
                        $dependentFullPath
                    );

                    $idTmp = $this->_group . '_information' . $id;
                    $dependentIdTmp = $this->_group . '_information' . $dependentId;

                    $nameTmp = 'listing[' . $this->_group . '_information][' . $name . ']';
                    $dependentNameTmp = 'listing[' . $this->_group . '_information][' . $dependentFieldName . ']';
                    //                    }
                    //                    if ($shouldBeAddedDependence) {
                    $this->_getDependence()
                        ->addFieldMap($idTmp, $nameTmp)
                        ->addFieldMap($dependentIdTmp, $dependentNameTmp)
                        ->addFieldDependence($nameTmp, $dependentNameTmp, $dependentValue);
                    //                    }
                }
            }

            $field = $fieldset->addField($id, $fieldType, array(
                'name' => $name,
                'label' => $label,
                'comment' => $comment,
                'tooltip' => $tooltip,
                'hint' => $hint,
                'value' => $data,
                'inherit' => $inherit,
                'class' => $e->frontend_class,
                'field_config' => $e,
                'scope' => $this->getScope(),
                'scope_id' => $this->getScopeId(),
                'scope_label' => $this->getScopeLabel($e),
                //                                                               'can_use_default_value' => $this->canUseDefaultValue((int)$e->show_in_default),
                //                                                               'can_use_website_value' => $this->canUseWebsiteValue((int)$e->show_in_website),
            ));
            //            $this->_prepareFieldOriginalData($field, $e);
            /*
            if (isset($e->validate)) {
                $field->addClass($e->validate);
            }
            */

            if (isset($e->frontend_type)
                && 'multiselect' === (string)$e->frontend_type
                && isset($e->can_be_empty)
            ) {
                $field->setCanBeEmpty(true);
            }

            $field->setRenderer($fieldRenderer);

            if ($e->source_model) {
                // determine callback for the source model
                $factoryName = (string)$e->source_model;
                $method = false;
                if (preg_match('/^([^:]+?)::([^:]+?)$/', $factoryName, $matches)) {
                    array_shift($matches);
                    list($factoryName, $method) = array_values($matches);
                }

                $sourceModel = Mage::getSingleton($factoryName);
                if ($sourceModel instanceof Varien_Object) {
                    $sourceModel->setPath($path);
                }
                if ($method) {
                    if ($fieldType == 'multiselect') {
                        $optionArray = $sourceModel->$method();
                    } else {
                        $optionArray = array();
                        foreach ($sourceModel->$method() as $value => $label) {
                            $optionArray[] = array('label' => $label, 'value' => $value);
                        }
                    }
                } else {
                    $optionArray = $sourceModel->toOptionArray($fieldType == 'multiselect');
                }
                $field->setValues($optionArray);

            }


            if ($this->_listing->getData('default_' . $this->_group . '_conf') != 1 && $this->_listing->getData($this->_group . '_information')) {

                $values = $this->_listing->getData($this->_group . '_information');

                if (isset($values[$name])) {
                    $field->setValue($values[$name]);
                }

            } elseif ($this->_listing->getData('default_' . $this->_group . '_conf') == 1) {
                $field->setValue(Mage::getStoreConfig($this->_sectionName . '/' . $this->_group . '/' . $name));
            }

        }
    }

    /**
     * Instantiate form and fields
     *
     * @return Varien_Data_Form
     */
    protected function _prepareForm()
    {

        if (!$this->_configDataObject) {
            $this->_initObjects();
        }

        $form = new Varien_Data_Form();
        $form->setFieldsetRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset'));
        $form->setFieldsetElementRenderer($this->getLayout()
            ->createBlock('adminhtml/widget_form_renderer_fieldset_element'));

        /**
         * if there is a parent element defined, it will be replaced by a hidden element with the same name
         * and overriden by the form elements
         * It is needed to maintain HTML consistency of the parent element's form
         */
        if ($this->_parentElement) {
            $form->setHtmlIdPrefix($this->_parentElement->getHtmlId())
                ->setFieldNameSuffix($this->_parentElement->getName());
            $form->addField('', 'hidden', array('name' => ''));
        }

        $this->_section = Mage::getSingleton('adminhtml/config')->getSection($this->_sectionName);
        //$sectionName = $this->_sectionName;
        $groups = $this->_section->groups;

        $groups = (array)$groups;
        if ($group = $groups[$this->_group]) {
            $fieldset = $form->addFieldset(
                $this->_group,
                array(
                    'legend' => (string)$group->label,
                ));


            foreach ($group->fields as $elements) {
                $elements = (array)$elements;
                $this->prepareElements($elements, $group, $fieldset);

            }
        }

        //$form->setValues(Mage::getStoreConfig($this->_sectionName . '/' . $this->_group));


        return $form;
    }

    /**
     * Return dependency block object
     *
     * @return Mage_Adminhtml_Block_Widget_Form_Element_Dependence
     */
    protected function _getDependence()
    {
        if (!$this->getChild('element_dependense')) {
            $this->setChild('element_dependense',
                            $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence'));
        }
        return $this->getChild('element_dependense');
    }

    /**
     * Add a field to the form or fieldset
     * Form and fieldset have same abstract
     *
     * @param Varien_Data_Form|Varien_Data_Form_Element_Fieldset $formOrFieldset
     * @param string $elementName
     * @param array $options
     * @param string $type
     * @return Varien_Data_Form_Element_Abstract
     */
    protected function _addField($formOrFieldset, $elementName, $options = array(), $type = 'text')
    {
        $options = array_merge($options, array(
                                              'name' => $elementName,
                                              //'label' => $this->_profile->getFieldLabel($elementName),
                                              //'note' => $this->_profile->getFieldComment($elementName),
                                              'disabled' => $this->_isReadOnly,
                                         ));
        if (in_array($elementName, array('period_unit', 'period_frequency'))) {
            $options['required'] = true;
        }
        return $formOrFieldset->addField($elementName, $type, $options);
    }

    /**
     * Set readonly flag
     *
     * @param boolean $isReadonly
     * @return Mage_Sales_Block_Adminhtml_Recurring_Profile_Edit_Form
     */
    public function setIsReadonly($isReadonly)
    {
        $this->_isReadOnly = $isReadonly;
        return $this;
    }

    /**
     * Get readonly flag
     *
     * @return boolean
     */
    public function getIsReadonly()
    {
        return $this->_isReadOnly;
    }

     /**
     * Support models "getCommentText" method for field note generation
     *
     * @param Mage_Core_Model_Config_Element $element
     * @param string $helper
     * @return string
     */
    protected function _prepareFieldComment($element, $helper, $currentValue)
    {
        $comment = '';
        if ($element->comment) {
            $commentInfo = $element->comment->asArray();
            if (is_array($commentInfo)) {
                if (isset($commentInfo['model'])) {
                    $model = Mage::getModel($commentInfo['model']);
                    if (method_exists($model, 'getCommentText')) {
                        $comment = $model->getCommentText($element, $currentValue);
                    }
                }
            } else {
                $comment = Mage::helper($helper)->__($commentInfo);
            }
        }
        return $comment;
    }

    protected function _prepareFieldTooltip($element, $helper)
    {
        if ($element->tooltip) {
            return Mage::helper($helper)->__((string)$element->tooltip);
        } elseif ($element->tooltip_block) {
            return $this->getLayout()->createBlock((string)$element->tooltip_block)->toHtml();
        }
        return '';
    }
}
