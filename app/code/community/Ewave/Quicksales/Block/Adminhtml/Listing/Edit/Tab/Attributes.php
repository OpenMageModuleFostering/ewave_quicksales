<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 23.09.11
 * Time: 17:58
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_Block_Adminhtml_Listing_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Catalog_Form
{


    protected function _prepareForm()
    {

        if ($group = $this->getGroup()) {
            $form = new Varien_Data_Form();

            $form->setDataObject(Mage::registry('listing'));

            $fieldset = $form->addFieldset('group_fields' . $group->getId(),
                                           array(
                                                'legend' => Mage::helper('catalog')->__($group->getAttributeGroupName()),
                                                'class' => 'fieldset-wide',
                                           ));

            $attributes = $this->getGroupAttributes();
            $new_attributes = array();

            foreach ($attributes as $attribute) {


                if (!Mage::getStoreConfig('quicksales/settings/have_vshop') && $attribute->getAttributeCode() == 'vshop_category') {
                    continue;
                }

                if (!in_array($attribute->getAttributeCode(), array(
                                                                   'fixedpostage',
                                                                   'fixedpostagebylocation_act',
                                                                   'fixedpostagebylocation_nsw',
                                                                   'fixedpostagebylocation_nt',
                                                                   'fixedpostagebylocation_qld',
                                                                   'fixedpostagebylocation_sa',
                                                                   'fixedpostagebylocation_tas',
                                                                   'fixedpostagebylocation_vic',
                                                                   'fixedpostagebylocation_wa',
                                                                   'offerapexpress',
                                                                   'offerapregular',
                                                                   'postinst',
                                                                   'posttolocation',
                                                                   'providereturnrefundpolicy',
                                                              ))
                ) {


                    if ($attribute->getAttributeCode() == 'category') {
                        $attribute->getFrontend()->getAttribute()
                                ->setFrontendInputRenderer('quicksales/adminhtml_listing_edit_tab_additional_step2_renderer_category');

                    } elseif ($attribute->getAttributeCode() == 'status') {
                        $attribute->getFrontend()->getAttribute()
                                ->setFrontendInputRenderer('quicksales/adminhtml_listing_edit_tab_additional_step1_renderer_status');
                    } elseif ($attribute->getAttributeCode() == 'vshop_category') {
                        /*
                        $attribute->getFrontend()->getAttribute()
                                ->setFrontendInputRenderer('quicksales/adminhtml_listing_edit_tab_additional_step2_renderer_vshopcategory');
                        */
                    }
                    $new_attributes[] = $attribute;
                }
            }

            $this->_setFieldset($new_attributes, $fieldset);

            if ($pricing_information = $form->getElement('pricing_information')) {

                $pricing_information->setRenderer(
                    $this
                            ->getLayout()
                            ->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step2_renderer_depended')
                            ->setGroup('pricing')
                );
            }

            if ($listing_information = $form->getElement('listing_information')) {

                $listing_information->setRenderer(
                    $this
                            ->getLayout()
                            ->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step2_renderer_depended')
                            ->setGroup('listing')
                );
            }

            if ($listing_upgrade_information = $form->getElement('listing_upgrade_information')) {

                $listing_upgrade_information->setRenderer(
                    $this
                            ->getLayout()
                            ->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step2_renderer_depended')
                            ->setGroup('listing_upgrade')
                );
            }

            if ($payment_information = $form->getElement('payment_information')) {

                $payment_information->setRenderer(
                    $this
                            ->getLayout()
                            ->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step2_renderer_depended')
                            ->setGroup('payment')
                );
            }

            if ($shipping_information = $form->getElement('shipping_information')) {

                $shipping_information->setRenderer(
                    $this
                            ->getLayout()
                            ->createBlock('quicksales/adminhtml_listing_edit_tab_additional_step2_renderer_depended')
                            ->setGroup('shipping')
                );
            }

            $values = Mage::registry('listing')->getData();
            if (!Mage::registry('listing')->getId()) {
                foreach ($attributes as $attribute) {
                    if (!isset($values[$attribute->getAttributeCode()])) {
                        $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }

            if (Mage::registry('listing')->hasLockedAttributes()) {
                foreach (Mage::registry('listing')->getLockedAttributes() as $attribute) {
                    if ($element = $form->getElement($attribute)) {
                        $element->setReadonly(true, true);
                    }
                }
            }
            $form->addValues($values);
            $form->setFieldNameSuffix('listing');

            Mage::dispatchEvent('ewave_quicksales_listing_edit_prepare_form', array('form' => $form));

            $this->setForm($form);
        }
    }
}
