<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 27.09.11
 * Time: 12:18
 * To change this template use File | Settings | File Templates.
 */

class Ewave_Quicksales_Model_Source_Payments extends Mage_Eav_Model_Entity_Attribute_Source_Table
{

    protected $payments = array(
        'BankCheque',
        'BankDeposit',
        'Cash',
        'COD',
        'CreditCard',
        'Escrow',
        'MoneyOrder',
        'Paymate',
        'PayPal',
        'PersonalCheque',
        'Other',
        'ProvideBankDetailsToBuyer',
    );

    public function getAllOptions()
    {
        if (!$this->_options) {
            
            $options = array();

            foreach ($this->payments as $payment) {

                $options[] = array(
                    'value' => $payment,
                    'label' => $payment
                );
            }
            $this->_options = $options;
        }
        return $this->_options;
    }

    public function getPayments() {
        return $this->payments;
    }

}
