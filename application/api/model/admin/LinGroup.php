<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:12 下午
 */

namespace app\api\model\admin;


use think\Model;
use think\model\concern\SoftDelete;

class LinGroup extends Model
{
    use SoftDelete;

    public $autoWriteTimestamp = 'datetime';
    public $hidden = ['level', 'create_time', 'update_time', 'delete_time'];


    public function users()
    {
        return $this->belongsToMany('LinUser', 'Lin_user_group', 'user_id', 'group_id');
    }

    public function permissions()
    {
        return $this->belongsToMany('LinPermission', 'lin_group_permission', 'permission_id', 'group_id');
    }
}