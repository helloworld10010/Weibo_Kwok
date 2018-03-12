<li id="status-{{ $status->id }}">
    <a href="{{ route('users.show', $user->id )}}">
        <img src="{{ $user->gravatar() }}" alt="{{ $user->name }}" class="gravatar"/>
    </a>
    <span class="user">
    <a href="{{ route('users.show', $user->id )}}">{{ $user->name }}</a>
  </span>
    <span class="timestamp">
        {{--该方法的作用是将日期进行友好化处理，我们可以使用 tinker 来查看该方法的具体输出情况。--}}
    {{ $status->created_at->diffForHumans() }}
  </span>
    <span class="content">{{ $status->content }}</span>
    {{--并且删除按钮必须是微博的作者本人才能看到--}}
    @can('destroy', $status)
        <form action="{{ route('statuses.destroy', $status->id) }}" method="POST">
            {{ csrf_field() }}
            {{ method_field('DELETE') }}
            <button type="submit" class="btn btn-sm btn-danger status-delete-btn">删除</button>
        </form>
    @endcan
</li>