<?php

namespace App\Policies;


use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\User;

/**
 * 在 Laravel 中可以使用 授权策略 (Policy) 来对用户的操作权限进行验证，在用户未经授权进行操作时将返回 403 禁止访问的异常
 * Class UserPolicy
 * @package App\Policies
 */
class UserPolicy
{
    use HandlesAuthorization;

    /**
     * update 方法接收两个参数，第一个参数默认为当前登录用户实例，第二个参数则为要进行授权的用户实例。
     * 当两个 id 相同时，则代表两个用户是相同用户，用户通过授权，可以接着进行下一个操作
     * @param User $currentUser
     * @param User $user
     * @return bool
     */
    public function update(User $currentUser,User $user){
        return $currentUser->id === $user->id;
    }

    /**
     *
     * 当前用户是管理员，且删除的不能是自己
     * 其实就是符合这个条件的能xxxxx
     * @param User $currentUser
     * @param User $user
     * @return bool
     */
    public function destroy(User $currentUser,User $user){
        return $currentUser->is_admin && $currentUser->id !== $user->id;
    }
}
