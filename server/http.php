<?php

use think\Container;

class Http
{
    const HOST = '0.0.0.0';
    const PORT = '9501';

    public $http = null;

    public function __construct()
    {
        $this->http = new swoole_http_server(self::HOST, self::PORT);

        $this->http->set([
            'worker_num' => 4,
            'task_worker_num' => 4,
            'enable_static_handler' => true,
            'document_root' => '/Users/yyhaier/code/swoole/swoole_project_demo/think/public/static',
        ]);
        $this->http->on('workerstart', [$this, 'onWorkerStart']);
        $this->http->on('request', [$this, 'onRequest']);
        $this->http->on('task', [$this, 'onTask']);
        $this->http->on('finish', [$this, 'onFinish']);
        $this->http->on('close', [$this, 'onClose']);
        $this->http->start();
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

        //    $_SERVER = [];
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
        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }
        $_POST['http_server'] = $this->http;
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
        return $obj->$method($data['data']);
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
}

new Http();