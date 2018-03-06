<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function create(){
        # 返回页面
        return view('users.create');
    }
    /*
     * Laravel 将会自动查找 ID 为 1 的用户并赋值到变量 $user 中，如果数据库中找不到对应的模型实例
     * 我们将用户对象 $user 通过 compact 方法转化为一个关联数组，并作为第二个参数传递给 view 方法，将数据与视图进行绑定。
     */
    public function show(User $user){
        return view('users.show',compact('user'));
    }

    /*
     * 处理表单数据提交后的 store 方法，用于处理用户创建的相关逻辑。
     * 在实际开发中，我们经常需要对用户输入的数据进行 验证，在验证成功后再将数据存入数据库。
     * 在 Laravel 开发中，提供了多种数据验证方式，在本教程中，我们使用其中一种对新手较为友好的验证方式 - validator 来进行讲解。
     * validator 由 App\Http\Controllers\Controller 类中的 ValidatesRequests 进行定义，因此我们可以在所有的控制器中使用 validate 方法来进行数据验证。
     * validate 方法接收两个参数，第一个参数为用户的输入数据，第二个参数为该输入数据的验证规则。
     *
     * store 方法接受一个 Illuminate\Http\Request 实例参数，我们可以使用该参数来获得用户的所有输入数据
     */
    public function store(Request $request){
        // 校验
        $this->validate($request,[
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::crete([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('user.show',[$user]);
    }
}
