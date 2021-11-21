<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Auth;
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
        $admins = [1,6];
        if (!$this->inExceptArray($request)) {
            if (!in_array(Auth::User()->id ?? 0, $admins)) {
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
