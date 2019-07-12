<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/4/9
 * Time: 上午10:22
 */
require_once __DIR__ . '/MysqlService.php';
Class test{


}

var_dump(MysqlService::getInstance()->query("select * from user"));

