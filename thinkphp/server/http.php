<?php

use think\Container;

/**
 * http 优化 基础类库
 * User: singwa
 * Date: 18/3/2
 * Time: 上午12:34
 */

class Http {

    CONST HOST = "0.0.0.0";
    CONST PORT = 8811;

    public $http = null;
    public function __construct() {
        $this->http = new swoole_http_server(self::HOST, self::PORT);

        $this->http->set(
            [
                'enable_static_handler' => true,
                'document_root' => "/home/mycode/tp5_swoole/thinkphp/public/static",
                'worker_num' => 5,
            ]
        );
        $this->http->on("workerstart", [$this, 'onWorkerStart']);
        $this->http->on("request", [$this, 'onRequest']);
        $this->http->on("task", [$this, 'onTask']);
        $this->http->on("finish", [$this, 'onFinish']);
        $this->http->on("close", [$this, 'onClose']);

        $this->http->start();
    }


    public function onWorkerStart($server, $worker_id) {
        require __DIR__ . '/../thinkphp/base.php';
    }



    public function onRequest($request, $response) {
        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtolower($k)] = $v;
            }
        }

        if (isset($request->header)) {
            foreach ($request->header as $k => $v) {
                $_SERVER[strtolower($k)] = $v;
            }
        }

        $_GET = [];
        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[strtolower($k)] = $v;
            }
        }

        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[strtolower($k)] = $v;
            }
        }

        $_POST['http_server'] = $this->http;

        ob_start();
        try{
            // 执行应用并响应
            Container::get('app')->run()->send();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $res = ob_get_contents();
//    if ($res) {
        ob_end_clean();
//    }

        $response->end($res);
    }

    public function onTask($serv, $taskId, $workerId, $data) {
        // 分发 task 任务机制，让不同的任务 走不同的逻辑
        $obj = new app\common\lib\task\Task;

        $method = $data['method'];
        $flag = $obj->$method($data['data'], $serv);
        return "on task finish"; // 告诉worker
    }


    /**
     * @param $serv
     * @param $taskId
     * @param $data
     */
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    /**
     * close
     * @param $http
     * @param $fd
     */
    public function onClose($http, $fd) {
        echo "clientid:{$fd}\n";
    }
}

new http();