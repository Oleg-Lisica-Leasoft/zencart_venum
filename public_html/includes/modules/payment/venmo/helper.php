<?php
namespace public_html\includes\modules\payment\venmo;

require_once(DIR_FS_CATALOG . 'vendor/autoload.php');

use Ginger\Ginger;

class helper
{
    public $client;

    function __construct()
    {
        $this->client = Ginger::createClient(
            'https://api.dev.gingerpayments.com',
            'c6a7151cde304a6da74ccff1d039319c'
        );
    }
}