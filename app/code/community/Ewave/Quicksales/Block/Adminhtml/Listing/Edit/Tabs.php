<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 10:21
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    protected $_attributeTabBlock = 'quicksales/adminhtml_listing_edit_tab_attributes';

    /*
     * array (groupName => array(updates))
     */
    protected $_groupData = array(
        'Step 1' => array(
            'sort_order' => 10,
            'label' => 'Assign products to listing',
        ),
        'Step 2' => array(
            'sort_order' => 20,
            'label' => 'Listing details',
        ),
        'Step 3' => array(
            'sort_order' => 30,
            'label' => 'Listing attributes (Tags)',
        )
    );

    public function __construct()
    {
        parent::__construct();
        $this->setId('listing_info_tabs');
        $this->setDestElementId('listing_edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Listing Information'));
    }

    /**
     * Enter description here...
     *
     * @param Varien_Simplexml_Element $a
     * @param Varien_Simplexml_Element $b
     * @return boolean
     */
    protected function _sortForm($a, $b)
    {
        return (int)$a->sort_order < (int)$b->sort_order ? -1 : ((int)$a->sort_order > (int)$b->sort_order ? 1 : 0);

    }

    public function applyAdditionalGroupData($groups)
    {
        foreach ($groups as $group) {

        }
    }

    protected function prepareDefaultConfigFields()
    {

        $config = Mage::getSingleton('adminhtml/config');
        $sections = $config->getSection(
            'quicksales_default'

        );
        if (empty($sections)) {
            $sections = array();
        }
        foreach ($sections as $section) {

            /*
            if (!$this->_canShowField($section)) {
                continue;
            }
            */
            foreach ($section->groups as $groups) {

                $groups = (array)$groups;

                $groups = $this->applyAdditionalGroupData($groups);
                usort($groups, array($this, '_sortForm'));

                /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
                foreach ($groups as $group) {

                    if ($group->frontend_model) {
                        $fieldsetRenderer = Mage::getBlockSingleton((string)$group->frontend_model);
                    } else {
                        $fieldsetRenderer = $this->_defaultFieldsetRenderer;
                    }

                    $fieldsetRenderer->setForm($this);
                    $fieldsetRenderer->setConfigData($this->_configData);
                    $fieldsetRenderer->setGroup($group);

                    if ($this->_configFields->hasChildren($group, $this->getWebsiteCode(), $this->getStoreCode())) {

                        $helperName = $this->_configFields->getAttributeModule($section, $group);

                        $fieldsetConfig = array('legend' => Mage::helper($helperName)->__((string)$group->label));
                        if (!empty($group->comment)) {
                            $fieldsetConfig['comment'] = Mage::helper($helperName)->__((string)$group->comment);
                        }
                        if (!empty($group->expanded)) {
                            $fieldsetConfig['expanded'] = (bool)$group->expanded;
                        }

                        $fieldset = $this->addFieldset(
                            $section->getName() . '_' . $group->getName(), $fieldsetConfig)
                                ->setRenderer($fieldsetRenderer);
                        $this->_prepareFieldOriginalData($fieldset, $group);
                        $this->_addElementTypes($fieldset);

                        if ($group->clone_fields) {
                            if ($group->clone_model) {
                                $cloneModel = Mage::getModel((string)$group->clone_model);
                            } else {
                                Mage::throwException(
                                    'Config form fieldset clone model required to be able to clone fields'
                                );
                            }
                            foreach ($cloneModel->getPrefixes() as $prefix) {
                                $this->initFields($fieldset, $group, $section, $prefix['field'], $prefix['label']);
                            }
                        } else {
                            $this->initFields($fieldset, $group, $section);
                        }

                        $this->_fieldsets[$group->getName()] = $fieldset;

                    }
                }
            }
        }
    }

    protected function _prepareLayout()
    {
        $listing = $this->getListing();
        //quicksales_listing
        $listingAttributes = $listing->getAttributes();
        if (!$listing->getId()) {
            foreach ($listingAttributes as $attribute) {
                $default = $attribute->getDefaultValue();
                if ($default != '') {
                    $this->getListing()->setData($attribute->getAttributeCode(), $default);
                }
            }
        }

        $attributeSetId = $this->getListing()->getDefaultAttributeSetId();
        /** @var $groupCollection Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection */
        $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($attributeSetId)
            ->setOrder('sort_order', 'ASC')
            ->load();


        $defaultGroupId = 0;
        foreach ($groupCollection as $group) {
            /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
            if ($defaultGroupId == 0 or $group->getIsDefault()) {
                $defaultGroupId = $group->getId();
            }
        }

        //if (!$listing->getId()) {
        $listing->setAttributeSetId($attributeSetId);
        //}

        $i = 0;
        foreach ($groupCollection as $group) {

            $attributes = $listing->getAttributes($group->getId(), true);

            // do not add grops without attributes
            if (!$attributes) {
                continue;
            }
            $i++;

            $active = $defaultGroupId == $group->getId();
            $groupName = strtolower(str_replace(' ', '', $group->getAttributeGroupName()));
            $block = $this->getLayout()->createBlock($this->getAttributeTabBlock())
                ->setGroup($group)
                ->setAttributes($attributes)
                ->setGroupAttributes($attributes)
                ->setAddHiddenFields($active)
                ->setChild('form_after', $this->getLayout()->createBlock('quicksales/adminhtml_listing_edit_tab_additional_' . $groupName));

            $group->addData($this->_groupData[$group->getAttributeGroupName()]);
            if (!$group->getLabel()) {
                $group->setLabel($group->getAttributeGroupName());
            }

            $this->addTab(
                'group_' . $group->getId(),
                array(
                    'label' => Mage::helper('quicksales')->__($group->getLabel()),
                    'content' => $block->toHtml() . $this->getAdditionalScript($group),
                    'active' => $active
                )
            );


        }

        return parent::_prepareLayout();
    }


    /*
     * @comment add event for tab changing
     * */
    protected function getAdditionalScript($group)
    {
        $tab = clone $group;
        $tab->setId('group_' . $group->getId());

        $currentGroup =
        $html = '
            <script type="text/javascript">
            Event.observe("'.$this->getTabId($tab).'", "click", function() {
                showHideButtons(this.id)
            })
            </script>
        ';

        return $html;
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $tab = $this->getRequest()->getParam('tab');

        $html .= '
                    <script type="text/javascript">

                    document.observe("dom:loaded", function() {
                        showHideButtons("'.($tab ? $tab : $this->getActiveTabId()).'")
                    });

                    </script>
                ';
        return $html;
    }


    public function getListing()
    {

        return Mage::registry('current_listing');
    }

    public function getAttributeTabBlock()
    {
        if (is_null(Mage::helper('quicksales/listing')->getAttributeTabBlock())) {
            return $this->_attributeTabBlock;
        }
        return Mage::helper('quicksales/listing')->getAttributeTabBlock();
    }

    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }
}
