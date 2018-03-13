{{--局部视图，用于展示用户的一些社交的统计信息，如关注人数、粉丝数、微博发布数等。--}}
<div class="stats">
    <a href="{{ route('users.followings', $user->id) }}">
        <strong id="following" class="stat">
            {{ count($user->followings) }}
        </strong>
        关注
    </a>
    <a href="{{ route('users.followers', $user->id) }}">
        <strong id="followers" class="stat">
            {{ count($user->followers) }}
        </strong>
        粉丝
    </a>
    <a href="{{ route('users.show', $user->id) }}">
        <strong id="statuses" class="stat">
            {{ $user->statuses()->count() }}
        </strong>
        微博
    </a>
</div>