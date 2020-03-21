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
            ->where(['pid' => 0])
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
            "packages" => $packages
        ];
    }

    public function getAccessToken(Request $request)
    {
        $user = Auth::user();
        if (empty($user)) {
            return response('Unauthorized.', 401);
        }

        return ['access_token' => $user->accessToken];
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

        return redirect(env('APP_URL'));
    }

    private function getImgUrl($file)
    {
        return env('APP_URL') . '/imgs/' . $file;
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
}
