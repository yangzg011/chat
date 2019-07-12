<?php

Class AsyMysqlService
{
    private static $db;
    private $config;
    static $reconnectTime = 5;

    public function __construct()
    {
        if (self::$db instanceof AsyMysqlService){
            return $this->db;
        }
        else{
            self::$db = new Swoole\Mysql;
            $env = include(__DIR__ . '/../conf/db.php');
            $this->config = $env['r'];
        }
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function execute($sql)
    {
        self::$db->connect($this->config, function ($db, $result) use ($sql) {
            if ($result == false) {
                echo 'mysql connect fail:' . $db->connect_error;
            }
            $db->query($sql, function ($db, $result) use ($sql) {
                if ($result === false) {
                    echo 'sql fail:' . $db->connect_error;
                    $db->close();
                    return false;
                } elseif ($result === true) {
                    $db->close();
                    echo 'sql execute success';
                    return true;
                } else {
                    $db->close();
                    return $result;
                }
            });
        });
        return true;
    }
}
