<?php

namespace app\http\middleware;

use app\lib\authenticator\Authenticator;
use app\lib\exception\token\ForbiddenException;

class Authentication
{
    /**
     * 权限验证
     * @param $request
     * @param \Closure $next
     * @return mixed
     * @throws \ReflectionException
     * @throws \app\lib\exception\token\TokenException
     * @throws \think\Exception
     */
    public function handle($request, \Closure $next)
    {

        $auth = (new Authenticator($request))->check();

        if (!$auth) {
            throw new ForbiddenException();
        }

        return $next($request);
    }
}
