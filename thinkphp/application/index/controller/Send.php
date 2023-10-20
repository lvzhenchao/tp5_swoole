<?php
namespace app\index\controller;

class Send
{
    public function index()
    {
        $phoneNum = request()->get("phone_num", 0, "intval");
        if (!$phoneNum) {
            return json_encode(['status'=>0, 'message'=>'error']);
        }
    }


}
