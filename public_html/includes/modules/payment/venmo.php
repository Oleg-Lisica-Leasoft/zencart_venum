<?php

require(DIR_FS_CATALOG . DIR_WS_MODULES . '/payment/venmo/Helper.php');
require(DIR_FS_CATALOG . DIR_WS_MODULES . '/payment/venmo/Collection.php');

use public_html\includes\modules\payment\venmo\Helper;
use public_html\includes\modules\payment\venmo\Collection;

class venmo
{
    var $code, $title, $enabled, $issuers;

    function __construct()
    {
        $this->code = 'venmo';
        $this->title = MODULE_PAYMENT_VENMO_TEXT_TITLE;
        $this->enabled = (defined('MODULE_PAYMENT_VENMO_STATUS') && MODULE_PAYMENT_VENMO_STATUS == 'True');

        if ($this->enabled == 'True' && empty($this->issuers)) {
            (new helper())->writeIssuers($this);
        }
    }

    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_VENMO_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    function javascript_validation()
    {
        return false;
    }

    function selection()
    {
        $form = '<select name="bank_id">';
        for ($i = 0; $i < sizeof($this->issuers); $i++) {
            $form .= "<option value='{$this->issuers[$i]['id']}'>{$this->issuers[$i]['name']}</option>";
        }
        $form .= "</select>";
        $selection = array('id' => $this->code,
            'module' => $this->title);
        $selection['fields'][0]['title'] = $form;
        return $selection;
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function confirmation()
    {
        for ($i = 0; $i < sizeof($this->issuers); $i++) {
            if($this->issuers[$i]['id'] == $_POST['bank_id']) {
                $method = $this->issuers[$i]['name'];
                break;
            }
        }
        return array('title' => $method);
    }

    public function process_button()
    {
        return zen_draw_hidden_field('bank_id', $_POST['bank_id']);
    }

    public function before_process()
    {
        if(!isset($_COOKIE['venmo_transaction'])) {
            setcookie('venmo_transaction', 1);
            $request_data = (new Collection())->getRequestData();
            $response = (new Helper())->client->createOrder($request_data);
            header("Location: " . $response['transactions'][0]['payment_url']);
            die();
        } else {
            setcookie('venmo_transaction', 0, time());
        }
    }

    public function after_process()
    {
        return false;
    }

    public function install()
    {
        global $db, $messageStack;
        if (defined('MODULE_PAYMENT_VENMO_STATUS')) {
            $messageStack->add_session('Venmo module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=venmo', 'NONSSL'));
            return 'failed';
        }
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, set_function, date_added) VALUES ('Currency Supported', 'MODULE_PAYMENT_VENMO_CURRENCY', 'EUR', 'Which currency is your Module Venmo configured to accept?<br>(Purchases in any other currency will be pre-converted to this currency)', '6', 'zen_cfg_select_option(array(\'EUR\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added, use_function) VALUES ('Api Key', 'MODULE_PAYMENT_VENMO_APIKEY', 'Test', 'Api key:', '6', now(), 'zen_cfg_password_display')");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added) VALUES ('Enable venmo payment module', 'MODULE_PAYMENT_VENMO_STATUS', 'True', 'Do you want to access venmo module payments?', '6', now());");
    }

    public function remove()
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode( "', '", $this->keys()) . "')");
    }

    public function keys()
    {
        return array('MODULE_PAYMENT_VENMO_STATUS', 'MODULE_PAYMENT_VENMO_APIKEY', 'MODULE_PAYMENT_VENMO_CURRENCY');
    }
}
