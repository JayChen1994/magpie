<?php

namespace App\Http\Controllers;

use App\Models\PackageModel;
use Illuminate\Http\Request;

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

    private function getImgUrl($file)
    {
        return env('APP_URL') . '/imgs/' . $file;
    }
}
