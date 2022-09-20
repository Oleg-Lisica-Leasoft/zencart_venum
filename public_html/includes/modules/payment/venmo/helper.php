<?php

require_once(DIR_FS_CATALOG . 'vendor/autoload.php');

use Ginger\Ginger;

class helper
{
    public $client;

    function __construct()
    {
        $this->client = Ginger::createClient(
            '',
            ''
        );
    }
}