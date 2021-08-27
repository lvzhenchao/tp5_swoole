<?php
namespace app\admin\controller;


class Live
{
    public function push()
    {


        //1 赛况基本信息入库

        //2 数据组织好 push到直播页面

          //获取集合内的所有已连接的用户
        //2-1
        $clients = $_POST['http_server']->connections;
        print_r($clients);
        //2-2
//        $clients = Redis::sMembers($key)
        //

        foreach ($clients as $fd) {

            $_POST['http_server']->push($fd, "hello-$fd");
        }




    }



}