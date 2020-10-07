<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:14 下午
 */

namespace app\api\model\admin;


use think\Model;
use think\model\concern\SoftDelete;

class LinPermission extends Model
{
    use SoftDelete;

    public $autoWriteTimestamp = 'datetime';
    public $hidden = ['create_time', 'update_time', 'delete_time'];
}