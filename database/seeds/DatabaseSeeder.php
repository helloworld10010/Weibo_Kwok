<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
/**
 * 需要在 DatabaseSeeder 中调用 call 方法来指定我们要运行假数据填充的文件。
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        Model::unguard();
        $this->call(UsersTableSeeder::class);
        Model::reguard();
    }
}
