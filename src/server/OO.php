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

        $this->chatService = new ChatService();

        $this->ser = new swoole_websocket_server('0.0.0.0',9502);
        $this->ser->set(
            [
                'task_worker_num' => 8,
                'worker_num' =>4,
                'log_file'  => '/usr/local/var/logs/swoole/swoole.log',
            ]
        );
        $this->ser->on('open',array($this,"onOpen"));
        $this->ser->on('message',array($this,"onMessage"));
        $this->ser->on('close',array($this,"onClose"));
        $this->ser->on('task',array($this,"onTask"));
        $this->ser->on('finish',array($this,"onFinish"));

        $this->ser->start();
    }

    public function onOpen($ser,$request){
        //创建连接,在这里check auth,实现登陆后才能聊天
        echo $request->fd;
    }

    public function onMessage($ser,$request){
        var_dump($request->data);
        $ser->task($request->data);
    }

    public function onClose($ser,$fd){
        //关闭连接
        echo $fd.'close';
    }

    public function onTask($ser,$taskId,$srcWorkId,$data){
        $data = json_decode($data,true);
        switch ($data['type']){
            case 'register':
                $result = $this->chatService->register($data);
                $ret = [
                    'code' => 0,
                    'msg'  => 'OK',
                    'data' => $result,
                ];

                $ret = json_encode($ret);
                foreach ($ser->connections as $fd){
                    $ser->push($fd,$ret);
                }

                break;
            case 'login':
                $result = $this->chatService->login($data);
                break;
            case 'logout':
                $result = $this->chatService->logout($data);
                break;
            case 'send_msg':
                $result = $this->chatService->sendMsg($data);
                break;
            case 'change_room':
                $result = $this->chatService->changeRoom($data);
                break;
            default:
                break;
        }

    }

    public function onFinish($ser,$taskId,$data){
        echo 'task_id:'.$taskId.',data:'.$data;
    }

}

new OO();
