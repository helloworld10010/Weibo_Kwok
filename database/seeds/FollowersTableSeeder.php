<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $users = User::all();
        $user = $users->first();
        $user_id = $user->id;

        // 不包含用户1的其他用户数组
        $followers = $users->slice(1);
        // 获取id数组
        $follower_ids = $followers->pluck('id')->toArray();

        // 关注除了1号以外的用户
        $user->follow($follower_ids);

        // 所有用户都关注1号用户
        foreach ($followers as $follower) {
            $follower->follow($user_id);
        }
    }
}
