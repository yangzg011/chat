<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/4/9
 * Time: 上午9:52
 */


Class CommonServer
{
    public function register($data){
        $pwd = md5($data['pwd'].'md5');
        $table = 'user';
        $result = MysqlService::getInstance()->insert($table,['']);
        return $result;
    }

    public function login($data)
    {

    }

    public function logout($data)
    {

    }
}