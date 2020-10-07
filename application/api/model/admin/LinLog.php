<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/3
 * Time: 6:13 下午
 */

namespace app\api\model\admin;


use think\Model;
use think\model\concern\SoftDelete;

class LinLog extends Model
{
    use SoftDelete;

    public $autoWriteTimestamp = 'datetime';
    protected $hidden = ['update_time', 'delete_time'];

    public static function getLogs(int $start, int $count, $params = []): array
    {
        $logList = self::withSearch(['name', 'start', 'end'], $params);

        $total = $logList->count();
        $logList = $logList->limit($start, $count)
            ->order('create_time desc')
            ->select();
        return [
            'logList' => $logList,
            'total' => $total
        ];
    }

    public static function searchLogs(int $start, int $count, $params = [])
    {
        $logList = self::withSearch(['name', 'start', 'end', 'keyword'], $params);

        $total = $logList->count();
        $logList = $logList->limit($start, $count)
            ->order('create_time desc')
            ->select();
        return [
            'logList' => $logList,
            'total' => $total
        ];
    }


    public static function getUserNames(int $start, int $count)
    {
        $users = self::field('username');

        $total = $users->count();
        $users = $users->limit($start, $count)
            ->group('username')
            ->select();

        return [
            'userList' => $users,
            'total' => $total
        ];
    }

    public function searchNameAttr($query, $value)
    {
        if ($value) {
            $query->where('username', $value);
        }
    }

    public
    function searchStartAttr($query, $value)
    {
        if ($value) {
            $query->where('create_time', '>= time', $value);
        }
    }

    public
    function searchEndAttr($query, $value)
    {
        if ($value) {
            $query->where('create_time', '<= time', $value);
        }
    }

    public
    function searchKeywordAttr($query, $value)
    {
        if ($value) {
            $query->whereLike('message', "%{$value}%");
        }
    }
}