<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/3/28
 * Time: 下午10:06
 */


require_once __DIR__ . '/Service/ChatService.php';

Class OO{
    private $ser;
    private $chatService;
    public function __construct(){



        $this->ser = new swoole_websocket_server('0.0.0.0',9502);
        $this->ser->set(
            [
                'task_worker_num' => 8, //task进程数量
                'worker_num' =>4,       //worker进程数量，cpu核数的1-4倍
                'log_file'  => '/usr/local/var/logs/swoole/swoole.log', //文件地址
            ]
        );
        $this->ser->on('open',array($this,"onOpen"));
        $this->ser->on('message',array($this,"onMessage"));
        $this->ser->on('close',array($this,"onClose"));
        $this->ser->on('task',array($this,"onTask"));
        $this->ser->on('finish',array($this,"onFinish"));

        $this->chatService = new ChatService($this->ser);

        $this->ser->start();
    }

    public function onOpen($ser,$request){
        //创建连接,在这里check auth,实现登陆后才能聊天
        echo 'fd:'.$request->fd.PHP_EOL;
    }

    public function onMessage($ser,$request){
        var_dump('onMessage:'.$request->data.PHP_EOL);
        $ser->task(['param'=>$request->data,'fd'=>$request->fd]);
    }

    public function onClose($ser,$fd){
        //关闭连接
        $ser->task(['param' => '{"type":"logout"}','fd'=> $fd]);
    }

    /**
     * @param $ser
     * @param $taskId
     * @param $srcWorkId
     * @param $data
     * @return array|false|string
     */
    public function onTask($ser,$taskId,$srcWorkId,$data){
        $param = json_decode($data['param'],true);
        switch ($param['type']){
            case 'login':
                $result = $this->chatService->login($data['fd'],$param);
                break;
            case 'logout':
                $result = $this->chatService->logout($data['fd']);
                break;
            case 'send_msg':
                $result = $this->chatService->sendMsg($data['fd'],$param);
                break;
            case 'change_room':
                $result = $this->chatService->changeRoom($data['fd'],$param);
                break;
            default:
                $result = [
                    'msg_info' => [
                        'from_id' => $data['fd'],
                        'from_name' => '系统',
                        'type' => 'system',
                        'text' => '位置操作',
                    ],
                    'type' => 'msg_info',
                ];
                break;
        }

        return $result;
    }

    public function onFinish($ser,$taskId,$data){

        echo 'task_id:'.$taskId.',data:'.json_encode($data).PHP_EOL;
    }

}

new OO();
