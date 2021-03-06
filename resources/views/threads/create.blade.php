@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Create a New Thread
                </div>

                <div class="panel-body">
                    @include ('shared.errors')
                    <form method="post" action="/threads">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label for="channel_id">Channel</label>
                            <select name="channel_id" id="channel_id" class="form-control">
                                <option value="">Chose a channel...</option>
                                @foreach($channels as $channel)
                                    <option value="{{ $channel->id }}" {{ old('channel_id') == $channel->id ? 'selected' : '' }}>
                                        {{ $channel->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Title" value="{{ old('title') }}" required />
                        </div>
                        <div class="form-group">
                            <label for="body">Body</label>
                            <textarea name="body" id="body" cols="30" rows="10" class="form-control" required>{{ old('title') }}</textarea>
                        </div>
                        <button id="submit" type="submit" class="btn btn-primary">
                            Publish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
