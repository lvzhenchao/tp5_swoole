<?php

use think\Container;
use think\facade\Cache;

/**
 * http 优化 基础类库
 * User: singwa
 * Date: 18/3/2
 * Time: 上午12:34
 */

//WebSocket\Server 继承自 Http\Server，所以 Http\Server 提供的所有 API 和配置项都可以使用
class Ws {

    CONST HOST = "0.0.0.0";
    CONST PORT = 8811;
    CONST CHART_PORT = 8812;//监听聊天页面的端口

    public $ws = null;
    public function __construct() {
        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);

        //监听聊天页面的端口
        $this->ws->listen(self::HOST, self::CHART_PORT, SWOOLE_SOCK_TCP);

        $this->ws->set(
            [
                'enable_static_handler' => true,
                'document_root' => "/home/mycode/tp5_swoole/thinkphp/public/static",
                'worker_num' => 5,
            ]
        );

        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);

        $this->ws->on("workerstart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request) {
        //1 将连接用户id放入到缓存中
//        \app\common\lib\redis\Predis::getInstance()->sAdd(config('redis.live_game_key'), $request->fd);
        var_dump($request->fd);
    }

    /**
     * 监听ws消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame) {
        echo "ser-push-message:{$frame->data}\n";
        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
    }

//    此事件在 Worker 进程 / Task 进程 启动时发生
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

        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k => $v) {
                $_FILES[strtolower($k)] = $v;
            }
        }

        $_POST['http_server'] = $this->ws;

        ob_start();
        try{
            // 执行应用并响应
            Container::get('app')->run()->send();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $res = ob_get_contents();
        if ($res) {
            ob_end_clean();
        }

        $response->end($res);
    }

    public function onTask($serv, $taskId, $workerId, $data) {
        print_r($data);
        // 耗时场景 10s
        sleep(10);
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
        // 将连接用户id从缓存中删除掉
//        \app\common\lib\redis\Predis::getInstance()->sRem(config('redis.live_game_key'), $fd);
        echo "clientid:{$fd}\n";
    }
}

new Ws();