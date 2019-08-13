<?php


class RedisService
{
    //在长链接模式下，redis服务不再是单单需要同步的，现在应该自己建立一个连接池
    //利用协程管道和协程redis来实现，每次使用完之后释放。
    private static $instance;//单例
    private $pool;//缓冲池
    private $config;

    private function __construct()
    {
        $env = include(__DIR__ . '/../conf/db.php');
        $this->config = $env['redis'];
        $this->pool = new chan($this->config['size']); // 短名称
        $this->init();
    }

    private function init(){
        for ($i =0;$i<$this->config['size'];$i++){
            go(function () use($i){
                try{
                    $redis = new Co\Redis;
                    $redis->connect($this->config['host'], $this->config['port']);
                    $ret = $redis->auth($this->config['password']);
                    $redis->setOptions(['compatibility_mode' => 'true']);
                    if ($ret){
                        $this->pool->push($redis);
                    } else{
                        var_dump("第{$i}个redis链接失败");
                    }
                } catch (\Exception $e){
                    var_dump("第{$i}个redis链接失败,原因:{$e->getMessage()}");
                }

            });
        }
        var_dump('连接池建立完毕');
    }
    /**
     * @return RedisService
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

    public function freeRedis($redis){
        $this->pool->push($redis);
    }

    public function getRedis(){
        $redis = $this->pool->pop($this->config['timeout']);
        if (!$redis){
            echo 'redis等待超时';
        }
        return $redis;
    }
}
