<?php
/**
 * Created by PhpStorm.
 */

namespace app\lib\exception\token;


use LinCmsTp5\exception\BaseException;

class DeployException extends BaseException
{
    public $code = 500;
    public $msg  = '请修改php.ini配置：opcache.save_comments=1或直接注释掉此配置(无效请在 etc/php.d/ext-opcache.ini 文件中修改)';
    public $error_code = 50000;
}
