<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/7
 * Time: 10:37 上午
 */

namespace app\lib\exception;


use LinCmsTp5\exception\BaseException;

class RepeatException extends BaseException
{
    public $code = 400;
    public $msg = '资源已存在';
    public $error_code = 10071;
}