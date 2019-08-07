<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/4/8
 * Time: 下午7:18
 */
require_once __DIR__ . '/AsyMysqlService.php';
Class ChatService{
    private $table;
    private $ser;

    public function __construct($ser)
    {
        $this->ser = $ser;

        $this->table = new Swoole\Table(1024);
        $this->table->column('name', swoole_table::TYPE_STRING, 64);
        $this->table->column('token', swoole_table::TYPE_STRING,64);

        $this->table->create();

    }


    public function sendMsg($srcWorkId,$data){
        $user = $this->table->get($srcWorkId);
        $data = [
            'msg_info' => [
                'from_id' => $srcWorkId,
                'from_name' => $user['name'],
                'type' => 'user',
                'text' => $data['msg'],
            ],
            'type' => 'msg_info',
        ];
        $this->wsSend($data);
    }

    public function changeRoom($srcWorkId,$data){
        return __FUNCTION__;
    }

    public function login($srcWorkId,$data){
        $user = [
            'name' => $data['name'],
            'token' => $data['token'],
        ];
        $this->table->set($srcWorkId,$user);
        $data = [
            'msg_info' => [
                'from_id' => $srcWorkId,
                'from_name' => '系统',
                'type' => 'system',
                'text' => $data['name'].'加入房间',
            ],
            'type' => 'msg_info',
        ];
        $this->wsSend($data);
        list($count,$userList) = $this->getUserList();
        $data = [
            'user_info' => [
                'count' => $count,
                'user_list' => $userList,
            ],
            'type' => 'user_info',
        ];
        $this->wsSend($data);
    }

    public function logout($srcWorkId){
        //从房间踢出
        $user = $this->table->get($srcWorkId);
        $this->table->del($srcWorkId);
        //构建返回数据
        $data = [
            'msg_info' => [
                'from_id' => $srcWorkId,
                'from_name' => '系统',
                'type' => 'system',
                'text' => $user['name'].'离开房间',
            ],
            'type' => 'msg_info',
        ];
        $this->wsSend($data);
        list($count,$userList) = $this->getUserList();
        $data = [
            'user_info' => [
                'count' => $count,
                'user_list' => $userList,
            ],
            'type' => 'user_info',
        ];
        $this->wsSend($data);
    }

    public function wsSend($data){
        $ret = [
            'code' => 0,
            'msg'  => 'OK',
            'data' => $data,
        ];
        $ret = json_encode($ret);
        foreach ($this->ser->connections as $fd){
            $this->ser->push($fd,$ret);
        }
    }

    public function getUserList(){
        $count = $this->table->count();
        echo 'count:'.$count;
        $userList = [];
        foreach ($this->table as $key => $value){
            $userList[] = $value['name'];
        }
        return $data = [$count,$userList];
    }
}