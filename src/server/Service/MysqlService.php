<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/4/9
 * Time: 上午10:01
 */

class MysqlService
{
    //协程mysql
    public static $instance;

    private $pool;
    private $config;

    public function __construct()
    {
        $env = include_once("../conf/db.php");
        $this->config = $env['r'];
        $this->pool = new chan($this->config['size']);
        $this->init();
    }

    /**
     * 初始化连接池
     */
    private function init(){
        for ($i=0;$i<$this->config['size'];$i++){
            go(function () use ($i){
                try{
                    $mysql = new Co\Mysql;
                    $mysql->connect([
                        'host' => $this->config['host'],
                        'port' => $this->config['port'],
                        'user' => $this->config['user'],
                        'password' => $this->config['password'],
                        'database' => $this->config['dbname'],
                    ]);

                    if ($mysql){
                        $this->pool->push($mysql);
                    } else{
                        echo "第{$i}个redis链接失败";
                    }
                } catch (\Exception $e){
                    echo "第{$i}个redis链接失败,原因:".$e->getMessage();
                }
            });
        }
    }

    public static function getInstance(){
        if (self::$instance instanceof self){
            return self::$instance;
        } else{
            self::$instance = new self;
            return self::$instance;
        }
    }

    public function getMysql(){
        $mysql = $this->pool->pop($this->config['timeout']);
        return $mysql;
    }

    public function freeMysql($mysql){
        $this->pool->pop($mysql);
        return true;
    }
}
