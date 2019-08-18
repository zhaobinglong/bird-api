<?php

class Cache
{
    private static $conn;

    public function getInstance()
    {
        if (empty(self::$conn)) {
            $redis = new \Redis();
            $conn = $redis->pconnect(REDIS_HOST, REDIS_PORT, 1);

            if (!$conn) {
                throw new \Exception('redis connect error', -10);
            }

            if (REDIS_PSW) {
                $auth = $redis->auth(REDIS_PSW);
                if (!$auth) {
                    throw new \Exception('redis psw error', -10);
                }
            }

            self::$conn = $redis;
        }

        return self::$conn;
    }

    public function main()
    {
        return $this->getInstance();
    }
}