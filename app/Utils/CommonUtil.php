<?php

namespace App\Utils;

use App\Exceptions\ApiException;

class CommonUtil
{
    /**
     * @param $code
     * @param $msg
     * @throws \App\Exceptions\ApiException
     */
    public static function throwException($code, $msg)
    {
        throw new ApiException($code, $msg);
    }

    public static function createUri()
    {
        return date('ymdHis') . self::createRandStr(6);
    }

    public static function createRandStr($len)
    {
        $default = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= substr($default, rand(0, strlen($default) - 1), 1);
        }
        return $str;
    }
}