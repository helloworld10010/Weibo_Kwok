@extends('layouts.default')

@section('title','主页')
@section('content')
    <div class="jumbotron">
        <h1>Hello Laravel</h1>
        <p class="lead">
            <a href="https://laravel-china.org/courses/laravel-essential-training-5.1">一女生问他男朋友：“你觉得范冰冰漂亮还是杨幂漂亮？” 男孩望着女孩问：“选项里不加上你吗？”女孩听后掩不住笑容， 羞娇发嗲问：“那…范冰冰，杨幂还有我，谁比较漂亮？” 男孩：“范冰冰。”</a>
        </p>
        <p>
            一切，将从这里开始。
        </p>
        <p>
            <a class="btn btn-lg btn-success" href="{{ route('signup') }}" role="button">现在注册</a>
        </p>
    </div>
@stop    
