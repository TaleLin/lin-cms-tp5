<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 9:59 下午
 */

namespace app\lib\exception;


use LinCmsTp5\exception\BaseException;

class NotFoundException extends BaseException
{
    public $code = 404;
    public $msg = '资源不存在';
    public $error_code = 10021;
}