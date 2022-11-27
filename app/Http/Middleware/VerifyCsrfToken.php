<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'test/image',
        'test/check',
    ];

    public function handle($request, Closure $next)
    {
        $admins = [1,7,9,10];
        if (!$this->inExceptArray($request)) {
            $userId = Auth::User()->id ?? 0;
            $attribute = Redis::GET('user_attribute:' . $userId);
            $attribute =  json_decode($attribute,true);
            if (!in_array($userId, $admins) || $attribute['type'] != 2) {
                $result = [
                    'data' => (object)[],
                    'code' => 300,
                    'msg'  => '当前账户无权操作',
                    'uuid' => Uuid::uuid6(),
                ];
                return response()->json($result);
            }
        }
        return $next($request);
    }
}
