<?php

namespace app\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use App\Models\Status;

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
        return $this->statuses()
            ->orderBy('created_at', 'desc');
    }
}
