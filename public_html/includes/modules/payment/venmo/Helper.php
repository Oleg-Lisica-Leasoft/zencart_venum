<?php
namespace public_html\includes\modules\payment\venmo;

require_once(DIR_FS_CATALOG . 'vendor/autoload.php');

use Ginger\Ginger;

class Helper
{
    public $client;

    function __construct()
    {
        $this->client = Ginger::createClient(
            'https://api.dev.gingerpayments.com',
            MODULE_PAYMENT_VENMO_APIKEY
        );
    }

    public function writeIssuers($venmo)
    {
        if (defined("MODULE_PAYMENT_VENMO_APIKEY") && MODULE_PAYMENT_VENMO_APIKEY == "Test") {
            $venmo->title .=  '<span class="alert"> (Not Configured)</span>';
        } else {
            try {
                $venmo->issuers = $this->client->getIdealIssuers();
                $venmo->issuers[] = array('id' => 'credit-card', 'list_type' => 'Nederland', 'name' => 'credit-card');
            } catch (\Exception $e) {
                $venmo->title .=  '<span class="alert"> (Invalid Api Key)</span>';
            }
        }
    }
}