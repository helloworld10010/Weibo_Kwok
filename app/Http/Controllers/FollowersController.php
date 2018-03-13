<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowersController extends Controller {
    // 登录可用
    public function __construct() {
        $this->middleware('auth');
    }

    //登录用户和目标用户一致，返回首页
    public function store(User $user) {
        if (Auth::user()->id === $user->id) {
            return redirect('/');
        }
        // 不一致 关注ta
        if (!Auth::user()->isFollowing($user->id)) {
            Auth::user()->follow($user->id);
        }
        // 返回其个人页
        return redirect()->route('users.show', $user->id);
    }

    // 登录用户和目标用户一致，返回首页，不一致，判断已关注，取消关注
    public function destroy(User $user) {
        if (Auth::user()->id === $user->id) {
            return redirect('/');
        }

        if (Auth::user()->isFollowing($user->id)) {
            Auth::user()->unfollow($user->id);
        }

        return redirect()->route('users.show', $user->id);
    }
}
