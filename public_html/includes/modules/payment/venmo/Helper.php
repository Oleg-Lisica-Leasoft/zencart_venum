<?php

class Helper
{
    private $ginger;
    private $client;

    public function __construct()
    {
        $this->ginger = new Ginger\Ginger();
        $this->client = $this->ginger::createClient(
            'https://example.com',
            'api.key'
        );
    }
}