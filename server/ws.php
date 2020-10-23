<?php

use think\Container;
use app\common\lib\redis\Predis;

class Ws
{
    const HOST = '0.0.0.0';
    const PORT = '9501';
    const CHAT_PORT = '9502';

    public $ws = null;

    public function __construct()
    {
        //清空redis中的fd缓存
        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);
        $this->ws->listen(self::HOST, self::CHAT_PORT, SWOOLE_SOCK_TCP);
        $this->ws->set([
            'worker_num' => 4,
            'task_worker_num' => 4,
            'enable_static_handler' => true,
            'document_root' => '/Users/yyhaier/code/swoole/swoole_project_demo/think/public/static',
        ]);
        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('workerstart', [$this, 'onWorkerStart']);
        $this->ws->on('request', [$this, 'onRequest']);
        $this->ws->on('task', [$this, 'onTask']);
        $this->ws->on('finish', [$this, 'onFinish']);
        $this->ws->on('close', [$this, 'onClose']);
        $this->ws->start();
    }

    public function onStart($server)
    {
//        swoole_set_process_name("live_master");//MacOS不支持主进程修改名称
    }

    public function onOpen($ws, $request)
    {
        //fd放入redis
        Predis::getInstance()->sAdd(config('redis.live_redis_key'), $request->fd);
//        var_dump($request->fd, $request->server);
    }

    /**
     * @param $ws
     * @param $frame
     * @return null
     */
    public function onMessage($ws, $frame)
    {
//        echo "Message: {$frame->data}\n";
        $ws->push($frame->fd, "server: {$frame->data}" . date('Y-m-d H:i:s', time()));
    }

    public function onWorkerStart($server, $worker_id)
    {
        // thinkphp
        // 加载基础文件
        require __DIR__ . '/../thinkphp/base.php';
        Container::get('app')->run()->send();
    }

    public function onRequest($request, $response)
    {
        if ($request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return;
        }
        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if (isset($request->header)) {
            foreach ($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        $_GET = [];
        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }

        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }
        $this->writeLog();
        $_POST['ws_server'] = $this->ws;
        ob_start();
        $response->setHeader("Content-type", "text/html;charset=UTF-8");
        try {
            // 执行应用并响应
            Container::get('app')->run()->send();
        } catch (\Exception $e) {
            //TODO
        }
        $res = ob_get_contents();
        if (ob_get_contents()) ob_end_clean();
        $response->end($res);
    }

    public function onClose($ws, $fd)
    {
        //从redis中删除redis
        Predis::getInstance()->sRem(config('redis.live_redis_key'), $fd);
        echo "client-{$fd} is closed\n";
    }

    /**
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
        $obj = new app\common\lib\task\Task();
        $method = $data['method'];
        return $obj->$method($data['data'], $serv);
    }

    /**
     * @param $serv
     * @param $task_id
     * @param $data object onTask方法返回的内容
     */
    public function onFinish($serv, $task_id, $data)
    {
        echo "当前task事务id[$task_id] 完成！返回结果为: $data" . PHP_EOL;
    }

    /**
     * 记录日志
     */
    public function writeLog()
    {
        $datas = array_merge(['date' => date('Y-m-d H:i:s', time())], $_GET, $_POST, $_SERVER);
        $logs = '';
        foreach ($datas as $key => $value) {
            $logs = $logs . $key . ':' . json_encode($value) . "\n";
        }
        go(function () use ($logs) {
            Swoole\Coroutine\System::writeFile(
                '../runtime/log/' . date('Ym') . '/' . date('d') . '_access.log',
                $logs . "\n",
                FILE_APPEND);
        });
    }
}

new Ws();