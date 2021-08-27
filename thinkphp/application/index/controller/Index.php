<?php
namespace app\index\controller;

use think\facade\Cache;

class Index
{
    public function index()
    {
//        print_r($_GET);
        return 'index'."\r\n";

    }

    
    public function singwa()
    {
        return "singwa\r".date("Y-m-d");
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
