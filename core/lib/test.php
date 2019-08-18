<?php

require_once __ROOT__ . '/core/common/cache.php';
class test
{
    public function test1()
    {
        $client = new Cache();
        $client->main()->sadd('123', 123);
        $client->main()->sadd('123', 456);
        $client->main()->sadd('123', 789);


        print_r($client->main()->sCard('123'));
    }
}