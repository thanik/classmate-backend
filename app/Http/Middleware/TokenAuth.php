<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\Cache;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!is_null($request->header('Authorization')))
        {
            $token = str_replace('Bearer ','',$request->header('Authorization'));
            $signer = new Sha256();
            $token = (new Parser())->parse($token);
            $data = new ValidationData();
            $data->setIssuer('https://classmateapp.xyz');
            $data->setAudience('https://classmateapp.xyz');

            if($token->validate($data))
            {
                if($token->verify($signer,env('TOKEN_HMAC_KEY')))
                {
                    $request->attributes->add(['tokendata' => $token->getClaims()]);

                    /* renew new token if passed 1 hour
                    $key = $request->fingerprint();

                    $response = $next($request);

                    $response->headers->add([
                        'X-New-Token' => $this->generateNewToken($request,$token),
                        'X-Token-Renew-Time' => Cache::get($key.':token-renew') - time(),
                    ]);
                    return $response;*/
                    return $next($request);
                }
                else
                {
                    return response()->json(['status' => 'unauthorized','message' => 'invalid token signature'],401);
                }
            }
            else
            {
                return response()->json(['status' => 'unauthorized','message' => 'invalid token or expired token'],401);
            }
        }
        else
        {
            return response()->json(['status' => 'unauthorized','message' => 'no token'],401);
        }

    }
}
