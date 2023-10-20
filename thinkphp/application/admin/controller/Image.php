<?php
namespace app\admin\controller;


class Image
{
    public function index()
    {
        $file = request()->file('file');
        $info = $file->move('../public/static/upload');
        if ($info) {
            $data = [
                'image' => config('live.host').'/upload/'.$info->getSaveName(),

            ];
            return json_encode(['status'=>1, 'message' => 'ok', 'data'=>$data]);
        } else {
            return json_encode(['status'=>0, 'message' => 'error']);
        }

    }



}