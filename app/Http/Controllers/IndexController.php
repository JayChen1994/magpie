<?php

namespace App\Http\Controllers;

use App\Logic\CookieLogic;
use App\Models\PackageModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $packages = PackageModel::query()
            ->where(['pid' => 0, 'type' => 0])
            ->get()
            ->map(function ($v) {
                return [
                    'title' => $v['title'],
                    'imgUrl' => $this->getImgUrl($v['imgUrl'])
                ];
            });

        $service = PackageModel::query()
            ->where(['pid' => 0, 'type' => 3])
            ->get()
            ->map(function ($v) {
                return [
                    'title' => $v['title'],
                    'imgUrl' => $this->getImgUrl($v['imgUrl'])
                ];
            });

        return [
            "banner" => [
                $this->getImgUrl("banner-1.jpg"),
                $this->getImgUrl("banner-2.jpg"),
            ],
            "packages" => $packages,
            "service" => $service,
            "intro" => [
                [
                    'title'=>'公司简介1',
                    'uri'=>'https://www.baidu.com'
                ],
                [
                    'title'=>'公司简介2',
                    'uri'=>'https://www.qq.com'
                ]
            ]
        ];
    }

    public function packages(Request $request)
    {
        return PackageModel::query()
            ->where(['pid' => 0, 'type' => 0])
            ->get()
            ->map(function ($v) {
                $v['imgUrl'] = $this->getImgUrl($v['imgUrl']);
                $v['sub'] = PackageModel::query()->where(['pid' => $v['id']])->get()->map(function ($v) {
                    $v['imgUrl'] = $this->getImgUrl($v['imgUrl']);
                    $v['price'] = $v['price'] / 100;
                    return $v;
                });
                return $v;
            });
    }

    public function addPackage(Request $request)
    {
        $user = Auth::user();
        if (empty($user) || $this->isAdmin($user->openid)) {
            return response('Unauthorized.', 401);
        }

        $price = $request->input('price');
        $cleanNum = $request->input('cleanNum');
        $personLimit = $request->input('personLimit');  //购买人数

        $uri = 'package_sp_' . uniqid();

        PackageModel::query()->insert([
            'title' => '服务精选',
            'uri' => $uri,
            'pid' => 0,
            'imgUrl' => 'cat0.jpg',
            'price' => $price * 100,
            'cleanNum' => $cleanNum,
            'type' => 2,
            'unit' => '次',
            'personLimit' => $personLimit
        ]);

        return ['uri' => $uri];
    }

    public function isLogin(Request $request)
    {
        $user = Auth::user();
        if (empty($user)) {
            return ['isLogin' => false];
        }
        return [
            'isLogin' => true,
            'isAdmin' => $this->isAdmin($user->openid)
        ];
    }

    public function getJsPackage(Request $request)
    {
        $appId = env('WX_APPID');
        $appSecret = env('WX_SECRET');
        $url = $request->input('url');

        $ret = $this->getWebPage("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret");

        Log::info('get js access_token...', $ret);

        $ret = $this->getWebPage("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$ret['access_token']}&type=jsapi");

        Log::info('get js package...', $ret);

        $jsapiTicket = $ret['ticket'];

        // 注意 URL 一定要动态获取，不能 hardcode.
        // $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        // $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => env('WX_APPID'),
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    public function authorizeUser(Request $request)
    {
        Log::info("retrieve code...", $request->all());

        $code = $request->input('code');
        $state = $request->input('state');
        $appId = env('WX_APPID');
        $appSecret = env('WX_SECRET');

        $ret = $this->getWebPage("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$appSecret&code=$code&grant_type=authorization_code");
        Log::info("retrieve access_token...", $ret);

        $accessToken = $ret['access_token'];
        $openId = $ret['openid'];

        $useInfo = $this->getWebPage("https://api.weixin.qq.com/sns/userinfo?access_token=$accessToken&openid=$openId&lang=zh_CN");
        Log::info("userInfo...", $useInfo);

        $user = UserModel::query()->where('openid', '=', $useInfo['openid'])->get()->first();
        if (!empty($user)) {
            $user->nickname = $useInfo['nickname'];
            $user->headimgurl = $useInfo['headimgurl'];
            $user->access_token = $accessToken;
            $user->save();
        } else {
            UserModel::query()->insert([
                'openid' => $useInfo['openid'],
                'nickname' => $useInfo['nickname'],
                'headimgurl' => $useInfo['headimgurl'],
                'access_token' => $accessToken,
            ]);
        }

        CookieLogic::set([
            'name' => 'magpieuc',
            'value' => $accessToken,
            'expire' => time() + 3600,
            'path' => '/',
            'domain' => env('COOKIE_DOMAIN'),
        ]);

        return redirect($state);
    }

    private function getImgUrl($file)
    {
        return env('APP_URL') . '/static/imgs/' . $file;
    }

    private function getWebPage($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => false,            // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => "",             // handle all encodings
            CURLOPT_USERAGENT => "magpie",      // who am i
            CURLOPT_AUTOREFERER => true,        // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,             // timeout on response
            CURLOPT_MAXREDIRS => 10,            // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        curl_close($ch);

        // $header['errno'] = $err;
        // $header['errmsg'] = $errmsg;
        // $header['content'] = $content;
        return json_decode($content, true);
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function isAdmin($openid)
    {
        return in_array($openid,
            ['o0d-m1TapYbRr8DIRGZhXMqDnLsI', 'o0d-m1cW-XhlpFheKNnMqomUI1c0', 'o0d-m1bcGPXLju7oLRjwnLyRi0FQ']);
    }
}
