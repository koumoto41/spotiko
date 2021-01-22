@extends('templates.layout')

@section('title', 'STOCK FORM')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <form action="{{ url('cms/stock/form') }}" method="post" accept-charset="utf-8">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="album">登録済アルバム</label>
                                    <select name="album" class="form-control">
                                        <option value="" selected="selected">選択して下さい</option>
                                        @foreach($albums as $album)
                                            <option value="{{ $album->album_id }}">{{ $album->album_name }}【{{ $album->artist_name }}】</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">登録</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection