<?php

namespace public_html\includes\modules\payment\venmo;

class Collection
{
    private $order;

    public function __construct()
    {
        $this->order = $GLOBALS['order'];
    }

    public function getRequestData()
    {
        $request_data['merchant_order_id'] = $this->getId();
        $request_data['currency'] = $this->getCurrency();
        $request_data['amount'] = $this->getAmount();
        $request_data['return_url'] = $this->getReturnUrl();
        $request_data['transactions'] = $this->getTransactions();
        return $request_data;
    }

    private function getId()
    {
        global $db;
        $result = $db->Execute("SELECT max(orders_id)+1 AS orders_id FROM " . TABLE_ORDERS . " ORDER BY orders_id");
        return $result->fields['orders_id'] . '-' . zen_create_random_value(6, 'chars');
    }

    private function getCurrency()
    {
        return MODULE_PAYMENT_VENMO_CURRENCY;
    }

    private function getAmount()
    {
        global $currencies;
        if ($this->order->info['currency'] != MODULE_PAYMENT_VENMO_CURRENCY) {
            $coef = $currencies->get_value(MODULE_PAYMENT_VENMO_CURRENCY);
        } else {
            $coef = 1;
        }
        return (int)(round($this->order->info['total'] * $coef, 2)*100);
    }

    private function getReturnUrl()
    {
        return zen_href_link (FILENAME_CHECKOUT_PROCESS, '', 'SSL');
    }

    private function getTransactions()
    {
        if ($_POST['bank_id'] === 'credit-card') {
            return [
                [
                    'payment_method' => $_POST['bank_id']
                ]
            ];
        } else {
            return [
                [
                    'payment_method' => 'ideal',
                    'payment_method_details' => [
                        'issuer_id' => $_POST['bank_id']
                    ]
                ]
            ];
        }
    }
}