@extends('layouts.default')
@section('title', $user->name)
@section('content')
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="col-md-12">
                <div class="col-md-offset-2 col-md-8">
                    <section class="user_info">
                        @include('shared._user_info', ['user' => $user])
                    </section>
                    {{--社交信息统计视图--}}
                    <section class="stats">
                        @include('shared._stats', ['user' => $user])
                    </section>
                </div>
            </div>
            <div class="col-md-12">
                {{--关注表单--}}
                {{--未登录用户不需要渲染关注表单--}}
                @if (Auth::check())
                    @include('users._follow_form')
                @endif
                @if (count($statuses) > 0)
                    <ol class="statuses">
                        @foreach ($statuses as $status)
                            @include('statuses._status')
                        @endforeach
                    </ol>
                    {!! $statuses->render() !!}
                @endif
            </div>
        </div>
    </div>
@stop