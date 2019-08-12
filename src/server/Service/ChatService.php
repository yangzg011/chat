<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 2019/4/8
 * Time: 下午7:18
 */
require_once __DIR__ . '/MysqlService.php.php';
require_once __DIR__ . '/RedisService.php';

Class ChatService
{
    private $table;
    private $redis;
    private $ser;

    public function __construct($ser)
    {
        $this->ser = $ser;

        $this->redis = RedisService::getInstance()->redis;

        $this->table = new Swoole\Table(1024);
        $this->table->column('fd', swoole_table::TYPE_INT, 16);
        $this->table->column('room_id', swoole_table::TYPE_INT, 16);
        $this->table->column('name', swoole_table::TYPE_STRING, 64);
        $this->table->column('token', swoole_table::TYPE_STRING, 64);
        $this->table->create();

    }

    public function sendMsg($fd, $data)
    {
        $user = $this->table->get($fd);
        $data = [
            'msg_info' => [
                'from_id' => $fd,
                'from_name' => $user['name'],
                'type' => 'user',
                'text' => $data['msg'],
            ],
            'type' => 'msg_info',
        ];
        $this->wsSend($data);
        return true;
    }

    public function changeRoom($fd, $data)
    {
        //更换房间
        return true;
    }

    public function login($fd, $data)
    {

        $this->joinRoom($fd, $data);

        $this->getUserList();

        $this->getRoomList();
        return true;
    }

    public function logout($fd)
    {
        //从房间踢出
        $this->leaveRoom($fd);

        $this->getUserList();

        return true;
    }

    public function wsSend($data)
    {
        $ret = [
            'code' => 0,
            'msg' => 'OK',
            'data' => $data,
        ];
        $ret = json_encode($ret);
        foreach ($this->ser->connections as $fd) {
            $this->ser->push($fd, $ret);
        }
        return true;
    }

    public function getUserList()
    {
        $count = $this->table->count();
        echo 'count:' . $count;
        $userList = [];
        foreach ($this->table as $key => $value) {
            $userList[] = $value['name'];
        }

        $data = [
            'user_info' => [
                'count' => $count,
                'user_list' => $userList,
            ],
            'type' => 'user_info',
        ];
        $this->wsSend($data);
        return true;
    }

    public function getRoomList()
    {
        $roomList = $this->redis->hgetall('chat:room:list');
        $data = [
            'room_info' => [
                'count' => count($roomList),
                'room_list' => $roomList,
            ],
            'type' => 'room_info',
        ];
        $this->wsSend($data);
        return true;
    }

    public function leaveRoom($fd)
    {
        $user = $this->table->get($fd);
        $this->table->del($fd);
        //构建返回数据
        $data = [
            'msg_info' => [
                'from_id' => $fd,
                'from_name' => '系统',
                'type' => 'system',
                'text' => $user['name'] . '离开房间',
            ],
            'type' => 'msg_info',
        ];
        $this->wsSend($data);
        return true;
    }

    public function joinRoom($fd, $data)
    {
        $user = [
            'fd' => $fd,
            'name' => $data['name'],
            'token' => $data['token'],
            'room_id' => $data['room_id'],
        ];
        $this->table->set($fd, $user);
        $data = [
            'change_room_info' => [
                'from_name' => '系统',
                'type' => 'system',
                'text' => $data['name'] . '进来了',
            ],
            'type' => 'msg_info',
        ];
        $this->wsSend($data);
        return true;
    }


}