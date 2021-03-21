<?php
namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
class VerifyJWTToken
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
        try{
            $response = [];
            $token = $request->bearerToken();
            $user = JWTAuth::toUser($token);
        }catch (JWTException $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                $response['errors']['status'] = 500;
                $response['errors']['messages'] = 'Token expired';
                return response()->json($response);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                $response['errors']['status'] = 500;
                $response['errors']['messages'] = 'Invalid token';
                return response()->json($response);
            }else{
                $response['errors']['status'] = 500;
                $response['errors']['messages'] = 'Token is required';
                return response()->json($response);
            }
        }
       return $next($request);
    }
}