<?php

class Helper
{
    private $ginger;

    public function __construct()
    {
        $this->ginger = new Ginger\Ginger();
    }

    private function createClient()
    {
        return $this->ginger::createClient(
            'https://example.com',
            'api.key'
        );
    }
}