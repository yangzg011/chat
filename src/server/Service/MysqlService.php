<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/4/9
 * Time: 上午10:01
 */

class MysqlService
{

    private static $instance;
    private $db;


    private function __construct()
    {
        $env = include(__DIR__ . '/../conf/db.php');
        $config = $env['r'];
        $this->db = self::conn($config);
    }

    /**
     * @return MysqlService
     */
    public static function getInstance()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        } else {
            self::$instance = new self;
            return self::$instance;
        }
    }


    //连接到数据库
    private static function conn($config)
    {
        try {
            $conn = new PDO($config['dsn'], $config['user'], $config['password']);   //数据库地址和密码等

        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $conn;

    }

    public function select($sql,$param = [])
    {
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($param)){
            $result = $stmt->fetchAll();
        }else{
            die(PHP_EOL.$this->db->errorInfo());
        }
        return $result;
    }

    public function update($sql,$param = [])
    {
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($param)){
            $result = $stmt->rowCount();
        }else{
            die(PHP_EOL.$this->db->errorInfo());
        }
        return $result;
    }


    public function insert($table,$param = [])
    {
        $sql = "insert into {$table} (%s) values(%s)";
        $column = array_keys($param);
        $columnStr = implode(',',$column);
        $values = '?';
        for ($i = 0;$i<count($param)-1;$i++){
            $values .= ',?';
        }
        $sql = sprintf($sql,$columnStr,$values);
        var_dump($sql);die;
        $stmt = $this->db->prepare($sql);
        try{
            if($stmt->execute($param)){
                $id = $this->db->lastInsertId();
            }else{
                die(PHP_EOL.$this->db->errorInfo());
            }
        }catch (Exception $e){
            die(PHP_EOL.$e->getMessage());
        }

        return $id;
    }

    public function delete($sql,$param = [])
    {
        $stmt = $this->db->prepare($sql);
        if($stmt->execute($param)){
            $result = $stmt->rowCount();
        }else{
            die(PHP_EOL.$this->db->errorInfo());
        }
        return $result;
    }

    public function transaction(callable $callback)
    {
        //开启一个事务，在事务中执行回调函数,返回回调函数的值
        try{
            $this->db->beginTransaction();
            $result = $callback();
            $this->db->commit();
            return $result;
        }catch (Exception $e){
            $this->db->rollBack();
            return false;
        }
    }
}


