<?php
namespace Providers;

class Response
{
    public function custom(Int $status, $message = '', $data = [])
    {
        $res = [];
        $res['status'] = $status;

        if(!empty($message)) $res['message'] = $message;
        if(!empty($data)) $res['data'] = $data;

        return json_encode($res);
    }
}