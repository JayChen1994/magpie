<?php

namespace App\Http\Middleware;

use App\ConstDir\BaseConst;
use App\ConstDir\ErrorConst;
use App\Exceptions\ApiException;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use stdClass;

class AfterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $data = [];
        if ($response->exception != null && ($response->exception instanceof ApiException)) {
            //抛异常
            $data = [
                'code' => $response->exception->getCode(),
                'msg' => $response->exception->getMessage(),
            ];
            $response->setStatusCode(Response::HTTP_OK);
        } elseif ($response->exception != null && $response->exception instanceof Exception) {
            //根据环境变量 判断
            if(env('APP_ENV') == 'production'){
                $data = [
                    'code' => ErrorConst::PHP_OVER,
                    'msg'  => ErrorConst::PHP_OVER_MSG,
                    //'data' => [],
                ];
                $response->setStatusCode(Response::HTTP_OK);
            }
        }else {
            $data = [
                'code' => BaseConst::SUCCESS_CODE,
                'msg'  => BaseConst::SUCCESS_CODE_MSG,
                'nowTime' => time()
            ];

            $origin = $response->getOriginalContent();
            if(!empty($origin) && (is_array($origin) || is_object($origin))) {
                $origin = $this->_filter($origin);
            }

            if (empty($origin)) {
                $origin = new stdClass();
            }

            $data['data'] = $origin;
        }

        //头信息为200的时候正常输出
        if($response->getStatusCode() == Response::HTTP_OK){
            $response->setContent($data);
            $response->setProtocolVersion('1.1');
            $response->setCharset('utf-8');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }


    /**
     * 因安卓和IOS数据为null,导致崩溃
     * 过滤数据为null
     * @param $data
     * @return array $data
     */
    private function _filter($data){

        foreach($data as $k=>$v){
            if(is_array($v)){
                $data[$k] = $this->_filter($v);
            }else{
                if(!isset($v)){
                    unset($data[$k]);
                }
            }
        }
        return $data;
    }
}