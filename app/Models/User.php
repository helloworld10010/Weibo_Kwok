<?php

namespace app\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use App\Models\Status;
use Auth;

class User extends Authenticatable

{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *  为了提高应用的安全性，Laravel 在用户模型中默认为我们添加了 fillable 在过滤用户提交的字段，只有包含在该属性中的字段才能够被正常更新：
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *  最后，当我们需要对用户密码或其它敏感信息在用户实例通过数组或 JSON 显示时进行隐藏，则可使用 hidden 属性：
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $table = 'users';

    public function gravatar($size = '100') {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    /**
     * 会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
     * 现在，我们需要更新模型工厂，将生成的假用户和第一位用户都设为已激活状态。
     */
    public static function boot() {
        parent::boot();
        // 用户创建前生成令牌，随着邮件发送给用户激活
        static::creating(function ($user) {
            // 监听用户创建，token随机
            $user->activation_token = str_random(30);
        });
    }

    /**
     * User 模型里调用：
     * @param string $token
     */
    public function sendPasswordResetNotification($token) {
        $this->notify(new ResetPassword($token));
    }

    //动态
    public function statuses() {
        return $this->hasMany(Status::class);
    }

    /**
     * 我们需要在用户模型中定义一个 feed 方法，该方法将当前用户发布过的所有微博从数据库中取出，并根据创建时间来倒序排序。
     * 在后面我们为用户增加关注人的功能之后，将使用该方法来获取当前用户关注的人发布过的所有微博动态。现在的 feed 方法定义如下：
     * @return $this
     */
    public function feed() {
        //Auth::user()->followings
        //我们在 User 模型里定义了关联方法 followings()，关联关系定义好后，我们就可以通过访问 followings 属性直接获取到关注用户的 集合
        //Laravel Eloquent 提供的「动态属性」属性功能，我们可以像在访问模型中定义的属性一样，来访问所有的关联方法。
        //可以简单理解为 followings 返回的是数据集合，而 followings() 返回的是数据库查询语句

        //关注者的id们
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        // 自己的id也加进去
        array_push($user_ids, Auth::user()->id);
        // 返回这些id的动态，倒叙
        //我们使用了 Eloquent 关联的 预加载 with 方法，预加载避免了 N+1 查找的问题，
        //大大提高了查询效率。N+1 问题 的例子可以阅读此文档 Eloquent 模型关系预加载 。
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    /**
     * 在用户关注功能中，一个用户（粉丝）能够关注多个人，而被关注者能够拥有多个粉丝，像这种关系我们称之为「多对多关系」。
     * 在 Laravel 中我们使用 belongsToMany 来关联模型之间的多对多关系。
     * 以粉丝为例，一个用户能够拥有多个粉丝，因此我们在用户模型中可以像这样定义：
     * 在 Laravel 中会默认将两个关联模型的名称进行合并并按照字母排序，因此我们生成的关联关系表名称会是 followers_user。
     * 我们也可以自定义生成的名称，把关联表名改为 followers。
     * 还可以自定义数据表里的名称
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers() {
        return $this->belongsToMany(User::Class,'followers', 'user_id', 'follower_id');
    }

    // 关注人列表
    public function followings() {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * @param $user_ids
     * is_array 用于判断参数是否为数组，如果已经是数组，则没有必要再使用 compact 方法。
     * 我们并没有给 sync 和 detach 指定传递参数为用户的 id，这两个方法会自动获取数组中的 id。
     */
    public function follow($user_ids) {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    public function unfollow($user_ids) {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    //于判断当前登录的用户 A 是否关注了用户 B
    public function isFollowing($user_id) {
        return $this->followings->contains($user_id);
    }
}
