<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 19.09.11
 * Time: 15:41
 * To change this template use File | Settings | File Templates.
 */
 
class Ewave_Quicksales_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {

        //Ewave_Quicksales_Model_Observer::getQuicksalesItems();
        Ewave_Quicksales_Model_Observer::getQuicksalesOrders();
    }

}
