<?php
//TODO::加上日志
set_error_handler(function ($level, $message, $file, $line) {
    $data = [
        'msg' => $message,
        'file' => $file,
        'line' => $line,
        'level'=>$level
    ];
    file_put_contents('/home/ubuntu/tmp/zl.log', json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL , FILE_APPEND);
    if ($level != E_DEPRECATED) {
        build_return($data, -100);
    }
});

set_exception_handler(function ($e) {
    $data = [
        'msg' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' =>  $e->getLine(),
    ];
    file_put_contents('/home/ubuntu/tmp/zl.log', json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL , FILE_APPEND);
    build_return($data, $e->getCode());
});

function build_return($data = [], $status = 0, $msg = '', $ext = NULL)
{
    $ret = array(
        'status' => $status,
        'msg' => $msg,
        'data' => $data
    );

    if ($ext) {
        $ret['ext'] = $ext;
    }
    header('Content-Type: application/json;charset=utf-8');
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    exit(0);
}