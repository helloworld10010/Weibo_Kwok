<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    //我们看到其中的关键信息 MassAssignmentException - 批量赋值异常，
    //这是因为我们未在微博模型中定义 fillable 属性，来指定在微博模型中可以进行正常更新的字段，Laravel 在尝试保护。
    //解决的办法很简单，在微博模型的 fillable 属性中允许更新微博的 content 字段即可。
    protected $fillable = ['content'];

    public function user() {
        // 属于xx  1对多 括号里是1
        return $this->belongsTo(User::class);
    }
}
