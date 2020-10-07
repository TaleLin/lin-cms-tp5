<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:15 下午
 */

namespace app\api\model\admin;


use think\facade\Config;
use think\Model;
use think\model\concern\SoftDelete;

class LinUser extends Model
{
    use SoftDelete;

    public $autoWriteTimestamp = 'datetime';
    public $hidden = ['create_time', 'update_time', 'delete_time'];

    public static function getUsers(int $start, int $count, array $params = [])
    {
        $userList = self::withSearch(['group_id'], $params)
            ->where('username', '<>', 'root');
        $total = $userList->count();

        $userList = $userList
            ->limit($start, $count)
            ->with('groups')
            ->select();

        return [
            'userList' => $userList,
            'total' => $total
        ];
    }

    public function groups()
    {
        return $this->belongsToMany('LinGroup', 'lin_user_group', 'group_id', 'user_id');
    }

    public function identity()
    {
        return $this->hasMany('LinUserIdentity', 'user_id');
    }

    public function searchGroupIdAttr($query, $value)
    {
        if ($value) {
            $query->join('lin_group g', 'g.id=' . $value)->where('g.id', '<>', 1);
        }
    }

    public function getAvatarAttr($value)
    {
        if ($value) {
            $host = Config::get('file.host') ?? "http://127.0.0.1:5000/";
            $dir = Config::get('file.store_dir');
            return $host . $dir . '/' . $value;
        }
        return $value;
    }
}