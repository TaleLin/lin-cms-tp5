<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:10 下午
 */

namespace app\api\model\admin;


use think\Model;
use think\model\concern\SoftDelete;

class LinFile extends Model
{
    use SoftDelete;

    public $autoWriteTimestamp = 'datetime';
}