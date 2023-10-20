<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 18/2/28
 * Time: 上午1:39
 */

use think\Container;

$http = new swoole_http_server("0.0.0.0", 8811);

$http->set(
    [
        'enable_static_handler' => true,
        'document_root' => "/home/mycode/tp5_swoole/thinkphp/public/static",
        'worker_num' => 5,
    ]
);


//引入框架内容
$http->on('WorkerStart', function ($server, $worker_id) {
    require __DIR__ . '/../thinkphp/base.php';
    // 执行应用并响应
    //Container::get('app')->run()->send();
});

$http->on('request', function($request, $response) use ($http) {
//    print_r($request->server);

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
//    $http->close($response->fd);//开销太大



});

$http->start();