<?php


namespace app\admin\controller;


use app\common\lib\redis\Predis;
use app\common\lib\Util;

class Live
{
    public function push()
    {
        if (empty($_GET)) {
            Util::show(config('code.error'), '内容不能为空');
        }
        $teams = [
            1 => [
                'name' => '马刺',
                'logo' => '/live/imgs/team1.png'
            ],
            2 => [
                'name' => '火箭',
                'logo' => '/live/imgs/team2.png'
            ]
        ];

        $data = [
            'type' => intval($_GET['type']),
            'title' => !empty($teams[$_GET['team_id']]['name']) ? $teams[$_GET['team_id']]['name'] : '直播员',
            'logo' => !empty($teams[$_GET['team_id']]['logo']) ? $teams[$_GET['team_id']]['logo'] : '',
            'content' => !empty($_GET['content']) ? $_GET['content'] : '',
            'image' => !empty($_GET['image']) ? $_GET['image'] : '',
        ];
        $taskData = [
            'method' => 'pushLive',
            'data' => $data
        ];
        $server = $_POST['ws_server'];
        $server->task($taskData);//将发送短信任务给task事务
        return Util::show(config('code.success'), '成功');
        //放入task任务中处理


    }
}