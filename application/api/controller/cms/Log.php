<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/4/26
 * Time: 22:20
 */

namespace app\api\controller\cms;

use app\api\service\admin\Log as LogService;
use LinCmsTp5\exception\ParameterException;
use think\Request;

class Log
{

    /**
     * @groupRequired
     * @permission('查询所有日志','日志')
     * @param Request $request
     * @param('page','分页数','integer')
     * @param('count','分页值','integer')
     * @param('start','开始日期','date')
     * @param('end','结束日期','date')
     * @return array
     * @throws ParameterException
     */
    public function getLogs(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $name = $request->get('name');
        $page = $request->get('page/d', 0);
        $count = $request->get('count/d', 10);

        return LogService::getLogs($page, $count, $start, $end, $name);
    }

    /**
     * @groupRequired
     * @permission('搜索日志','日志')
     * @param Request $request
     * @param('page','分页数','integer')
     * @param('count','分页值','integer')
     * @param('start','开始日期','date')
     * @param('end','结束日期','date')
     * @return array
     * @throws ParameterException
     */
    public function getUserLogs(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $name = $request->get('name');
        $keyword = $request->get('keyword');
        $page = $request->get('page/d', 0);
        $count = $request->get('count/d', 10);

        return LogService::searchLogs($page, $count, $start, $end, $name, $keyword);
    }

    /**
     * @groupRequired
     * @permission('查询日志记录的用户','日志')
     * @param Request $request
     * @param('page','分页数','integer')
     * @param('count','分页值','integer')
     * @return array
     * @throws ParameterException
     */
    public function getUsers(Request $request)
    {
        $page = $request->get('page/d', 0);
        $count = $request->get('count/d', 10);

        return LogService::getUserNames($page, $count);
    }
}