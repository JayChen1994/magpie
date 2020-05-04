<?php

namespace App\Providers;

use App\Logic\CookieLogic;
use App\Models\UserModel;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $accessToken = CookieLogic::get('magpieuc');
            if (empty($accessToken)) {
                return null;
            }

            $user = UserModel::query()->where('access_token', '=', $accessToken)->get()->first();
            if (empty($user)) {
                return null;
            }
            return new GenericUser(
                [
                    'id' => $user['id'], 'openid' => $user['openid'], 'accessToken' => $accessToken, 'headImg' => $user['headimgurl'],
                    'nickName' => $user['nickname'], 'isAdmin' => $user['isAdmin']
                ]
            );
        });
    }
}
