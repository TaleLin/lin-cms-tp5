<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 11:38 下午
 */

namespace app\lib\exception;


use LinCmsTp5\exception\BaseException;

class OperationException extends BaseException
{
    public $code = 400;
    public $msg = '操作失败';
    public $error_code = 10001;
}