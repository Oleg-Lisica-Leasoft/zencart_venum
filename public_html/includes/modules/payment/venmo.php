<?php

class venmo
{
    var $code, $title, $enabled;

    function __construct()
    {
        $this->code = 'venmo';
        $this->title = MODULE_PAYMENT_VENMO_TEXT_TITLE;
        $this->enabled = (defined('MODULE_PAYMENT_VENMO_STATUS') && MODULE_PAYMENT_VENMO_STATUS == 'True');
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
        require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/venmo/helper.php');
        $selection = ['id' => $this->code,
            'module' => $this->title];
        $issuers = (new helper)->client->getIdealIssuers();
        $issuers_fields = [];
        for($i = 0; $i < sizeof($issuers); $i++) {
            $issuers_fields[$i]['field'] = $issuers[$i]['name'];
            $issuers_fields[$i]['title'] = $issuers[$i]['id'];
        }
        $selection['fields'] = $issuers_fields;
        return $selection;
    }

    public function install()
    {
        global $db, $messageStack;
        if (defined('MODULE_PAYMENT_VENMO_STATUS')) {
            $messageStack->add_session('Venmo module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=venmo', 'NONSSL'));
            return 'failed';
        }
        $db->Execute("insert into ". TABLE_CONFIGURATION ." (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, date_added) values ('enable venmo payment module', 'MODULE_PAYMENT_VENMO_STATUS', 'True', 'Do you want to access venmo module payments?', '6', now());");
    }

    public function remove()
    {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode( ', ', $this->keys()) . "')");
    }

    function keys()
    {
        return array('MODULE_PAYMENT_VENMO_STATUS');
    }
}