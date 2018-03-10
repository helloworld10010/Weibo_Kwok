<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class SessionsController extends Controller
{
    // 只让未登录用户访问登录界面
    public function __construct() {
        // 这里的不能去掉
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    // 登录界面
    public function create(){
        return view('sessions.create');
    }

    // 用户身份认证
    public function store(Request $request) {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);
        //watch-poll被关掉了
        //前面我们介绍过的 Auth::attempt() 方法可接收两个参数，第一个参数为需要进行用户身份认证的数组，
        //第二个参数为是否为用户开启『记住我』功能的布尔值。
        //检查路由，检查视图，在路由跳转前 dd输出下
        if (Auth::attempt($credentials,$request->has('remember'))) {
            // 已激活
            if(Auth::user()->activated) {
                session()->flash('success', '欢迎回来！');
                return redirect()->intended(route('users.show', [Auth::user()]));
            } else {
                // 这就已经登录了？所以需要登出？
                Auth::logout();
                session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
        } else {
            // 登录失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back();
        }
    }

    // 退出登录
    public function destroy(){
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}
