<?php

class Ewave_Quicksales_Model_Api_Getcategories extends Ewave_Quicksales_Model_Api_Action
{

    protected $_options = null;

    protected function prepareCategories($category1, $category2) {

        return strcmp($category1['label'], $category2['label']);

    }


    public function getAllCategories()
    {
        if (!$this->_options) {

            try {
                $session = Mage::getSingleton('admin/session');

                $this->_options = $session->getQuicksalesCategories();

                if (!$this->_options) {
                    $xmlOblect = $this->_api->GetCategories();
                    //$xmlOblect = Mage::getModel('quicksales/api')->GetCategories();

                    if (!($xmlOblect instanceof Varien_Simplexml_Config)) {
                        return null;
                    }
                    $categories = $xmlOblect->getNode('CategoryArray');

                    $categoriesArray = array(

                        array(
                            'value' => '',
                            'label' => '',
                            'parent' => ''
                        )

                    );


                    foreach ($categories->Category as $category) {
                        if ($category->CategoryNum == 0) {
                            continue;
                        }

                        $cat = $category->asArray();
                        $categoriesArray[] = array(
                            'value' => $cat['CategoryNum'],
                            'label' => $cat['CategoryName'],
                            'parent' => (int)$cat['CategoryParentNum']
                        );
                    }
                    $this->_options = $categoriesArray;
                    $session->setQuicksalesCategories($categoriesArray);
                }
            } catch (Exception $e) {

                Mage::logException($e);
                return null;
            }
        }

        return $this->_options;
    }

    public function getAllOptions($withEmpty = true, $defaultValues = false, $parentId = 0) {
        $session = Mage::getSingleton('admin/session');
        $categories = $session->getQuicksalesCategories();
        $return = array();
        foreach ($categories as $v) {
            if ($v['parent'] == $parentId) {
                $return[] = $v;
            }
        }

        usort($return, array($this, 'prepareCategories'));

        return $return;
    }

    public function getCategoryCache()
    {

        $categories = array();

        if (!$this->_options) {
            $this->getAllCategories();
        }

        if (count($this->_options) == 0) {
            return null;
        }

        foreach ($this->_options as $cat) {
            if (empty($cat['value'])) {
                continue;
            }
		if (!isset($categories[$cat['value']])) {
			$categories[$cat['value']] = '';
		}
            if (isset($categories[$cat['parent']])) {
                $categories[$cat['value']] = $categories[$cat['parent']] . '/';
            }

            if (isset($cat['label'])) {
	            $categories[$cat['value']] .= $cat['label'];
		}

        }

        return $categories;
    }
}

