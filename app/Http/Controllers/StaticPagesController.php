<?php
/**
 * 让我们来为控制器加上这三个动作来处理从路由发过来的请求：要在控制器中指定渲染某个视图，则需要使用到 view 方法
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Status;
use Auth;

class StaticPagesController extends Controller
{
    public function home(){
        $feed_items = [];
        if (Auth::check()) {
            // 用户的动态
            $feed_items = Auth::user()->feed()->paginate(30);
        }

        return view('static_pages/home', compact('feed_items'));
    }
    public function help(){
        return view('static_pages/help');
    }
    public function about(){
        return view('static_pages/about');
    }
}
