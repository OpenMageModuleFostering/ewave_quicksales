<?php

class Ewave_Quicksales_Model_System_Payment_Method extends Mage_Core_Model_Abstract
{


    public function toOptionArray()
    {
        $input = array(
              'BankCheque' => 'Bank Cheque'
            , 'BankDeposit' => 'Bank Deposit'
            , 'Cash' => 'Cash'
            , 'COD' => 'C.O.D'
            , 'CreditCard' => 'Credit Card'
            , 'Escrow' => 'Escrow'
            , 'MoneyOrder' => 'Money Order'
            , 'Paymate' => 'Paymate'
            , 'PayPal' => 'PayPal'
            , 'PersonalCheque' => 'Personal Cheque'
            , 'Other' => 'Other'
        );

        $array = array();
        foreach ($input as $key => $value) {
            $array[] = array(
            	'value' => $key,
                'label' => $value,
            );
        }

        return $array;
    }


}
