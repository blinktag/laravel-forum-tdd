<div class="panel panel-default">
    <div class="panel-heading">
        <div class="level">
            <span class="flex">
                <a href="{{ route('profile', $thread->creator->name) }}" class="flex">
                    {{ $thread->creator->name }}
                </a>
                posted:
                <a href="{{ $thread->path() }}">
                    {{ $thread->title }}
                </a>
            </span>

            {{ $thread->created_at->diffForHumans() }}
                &nbsp;
                @can('update', $thread)
                <form action="{{ $thread->path() }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                    <button class="btn btn-danger btn-link" type="submit">
                        Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>
    <div class="panel-body">
        {{ $thread->body }}
    </div>
</div>
