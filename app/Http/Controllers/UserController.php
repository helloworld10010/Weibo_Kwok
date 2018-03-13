<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct() {
        // 中间件用于控制某些http请求
        // 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问
        // Laravel 提供的 Auth 中间件在过滤指定动作时，如该用户未通过身份验证（未登录用户），默认将会被重定向到 /login 登录页面。
        $this->middleware('auth',[
            'except' => ['show','create','store','index','confirmEmail']
        ]);
        // 只让未登录的访问注册界面
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    public function create(){
        # 返回页面
        return view('users.create');
    }
    /*
     * Laravel 将会自动查找 ID 为 1 的用户并赋值到变量 $user 中，如果数据库中找不到对应的模型实例
     * 我们将用户对象 $user 通过 compact 方法转化为一个关联数组，并作为第二个参数传递给 view 方法，将数据与视图进行绑定。
     *
     * /users/1
     */
    public function show(User $user){
        $statuses = $user->statuses()
            ->orderBy('created_at', 'desc')
            ->paginate(30);
        //compact 方法可以同时接收多个参数，在上面代码我们将用户数据 $user 和微博动态数据 $statuses 同时传递给用户个人页面的视图上。
        return view('users.show',compact('user','statuses'));
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
        // 直接就创建到数据库里边去了
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        #Auth::login($user);
        $this->sendEmailConfirmationTo($user);

        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect()->route('home');
        #echo "what happened";
    }

    /**
     * 编辑页面
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\Vie
     */
    public function edit(User $user){
        //这里 update 是指授权类里的 update 授权方法，$user 对应传参 update 授权方法的第二个参数。正如上面定义 update 授权方法时候提起的，调
        //用时，默认情况下，我们 不需要 传递第一个参数，也就是当前登录用户至该方法内，因为框架会自动加载当前登录用户。
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }

    /**
     * 更新用户信息
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * 在每次更改个人资料的时候都输入完整的密码，才能更新其它信息，对于不想对密码进行更新的用户，这个过程会比较繁琐；
     * 更新成功之后在页面上没有进行任何提示，而是直接跳转到用户的个人页面，用户体验非常不好
     * 未登录用户可以访问 edit 和 update 动作；
     * 登录用户可以更新其它用户的个人信息；
     */
    public function update(User $user, Request $request) {

        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        // 也就是当前用户必须满足授权策略中的update方法
        $this->authorize('update',$user);

        $data=[];
        $data['name']=$request->name;
        if($request->password){
            $data['password']=bcrypt($request->password);
        }

        $user->update($data);
        session()->flash('success',"个人资料更新成功");

        return redirect()->route('users.show', $user->id);
    }

    public function index() {
        //Eloquent用户模型 跟orm似的
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function destroy(User $user){
        // 删除授权策略 destroy 我们已经在上面创建了，这里我们在用户控制器中使用 authorize 方法来对删除操作进行授权验证即可。
        // 在删除动作的授权中，我们规定只有当前用户为管理员，且被删除用户不是自己时，授权才能通过。
        // 也就是当前用户必须满足(授权策略中的destroy方法)
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success','删除用户成功！');
        //最后将用户重定向到上一次进行删除操作的页面，即用户列表页
        return back();
    }

    /**
     * Mail 的 send 方法接收三个参数。
        第一个参数是包含邮件消息的视图名称。
        第二个参数是要传递给该视图的数据数组。
        最后是一个用来接收邮件消息实例的闭包回调，我们可以在该回调中自定义邮件消息的发送者、接收者、邮件主题等信息。
     *
     * @param $user
     */
    protected function sendEmailConfirmationTo($user) {
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    /**
     * 据路由传送过来的 activation_token 参数从数据库中查找相对应的用户，Eloquent 的 where 方法接收两个参数，
     * 第一个参数为要进行查找的字段名称，第二个参数为对应的值，查询结果返回的是一个数组，因此我们需要使用 firstOrFail 方法来取出第一个用户，
     * 在查询不到指定用户时将返回一个 404 响应。在查询到用户信息后，我们会将该用户的激活状态改为 true，激活令牌设置为空。
     * 最后将激活成功的用户进行登录，并在页面上显示消息提示和重定向到个人页面。
     * @param $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmEmail($token) {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    /**
     * 个是用于显示用户关注人列表视图的 followings 方法，另一个则是用户显示粉丝列表的 followers 方法
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followings(User $user) {
        $users = $user->followings()->paginate(30);
        $title = '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user) {
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }



}
