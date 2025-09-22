<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class CustomVerifyCsrfToken extends BaseVerifier
{
    protected function tokensMatch($request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $request->header('X-XSRF-TOKEN')) {
            $token = $request->header('X-XSRF-TOKEN');
        }

        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }
}
