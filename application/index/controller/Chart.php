<?php


namespace app\index\controller;


use app\common\lib\Util;

class Chart
{
    public function index()
    {
        //登录校验
        if (empty($_POST['game_id'])) {
            return Util::show(config('code.error'), 'error');
        }
        if (empty($_POST['content'])) {
            return Util::show(config('code.error'), 'error');
        }
        $data = [
            'user' => '用户' . rand(0, 100),
            'content' => $_POST['content'],
        ];
        $server = $_POST['ws_server'];
        foreach ($server->ports[1]->connections as $fd) {
            $server->push($fd, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        return Util::show(config('code.success'), 'ok', $data);
    }
}