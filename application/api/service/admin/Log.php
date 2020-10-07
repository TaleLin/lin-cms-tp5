<?php
/**
 * Created by PhpStorm
 * Author: 沁塵
 * Date: 2020/10/6
 * Time: 11:53 下午
 */

namespace app\api\service\admin;

use app\api\model\admin\LinLog as LinLogModel;
use LinCmsTp5\exception\ParameterException;

class Log
{
    /**
     * @param int $page
     * @param int $count
     * @param string|null $start
     * @param string|null $end
     * @param string|null $name
     * @return array
     * @throws ParameterException
     */
    public static function getLogs(int $page, int $count, string $start = null, string $end = null, string $name = null)
    {
        list($offset, $count) = paginate($count, $page);
        $params = ['start' => $start, 'end' => $end, 'name' => $name];
        $logsRes = LinLogModel::getLogs($offset, $count, $params);

        return [
            'items' => $logsRes['logList'],
            'count' => $count,
            'page' => $page,
            'total' => $logsRes['total']
        ];
    }

    /**
     * @param int $page
     * @param int $count
     * @param string|null $start
     * @param string|null $end
     * @param string|null $name
     * @param string|null $keyword
     * @return array
     * @throws ParameterException
     */
    public static function searchLogs(int $page, int $count, string $start = null,
                                      string $end = null, string $name = null, string $keyword = null)
    {
        list($offset, $count) = paginate($count, $page);
        $params = ['start' => $start, 'end' => $end, 'name' => $name, 'keyword' => $keyword];

        $logsRes = LinLogModel::searchLogs($offset, $count, $params);

        return [
            'items' => $logsRes['logList'],
            'count' => $count,
            'page' => $page,
            'total' => $logsRes['total']
        ];
    }

    /**
     * @param int $page
     * @param int $count
     * @return array
     * @throws ParameterException
     */
    public static function getUserNames(int $page, int $count)
    {
        list($start, $count) = paginate($count, $page);
        $usersRes = LinLogModel::getUserNames($start, $count);
        $items = array_map(function ($item) {
            return $item['username'];
        }, $usersRes['userList']->toArray());

        return [
            'items' => $items,
            'count' => $count,
            'page' => $page,
            'total' => $usersRes['total']
        ];
    }
}