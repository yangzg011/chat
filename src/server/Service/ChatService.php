<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/4/8
 * Time: 下午7:18
 */
require_once __DIR__ . '/AsyMysqlService.php';
Class ChatService{
    private $db;

    public function __construct()
    {
        $this->db = new AsyMysqlService();
    }


    public function sendMsg($data){

    }

    public function changeRoom($data){

    }

}