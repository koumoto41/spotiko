@extends('templates.layout')

@section('title', 'STOCK FORM')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form action="{{ url('cms/stock/form') }}" method="get" accept-charset="utf-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Spotify APIから検索</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="album">検索キーワード</label>
                                    <input name="query" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary text-white" type="submit">検索</button>
                            </div>
                        </div>
                    </form>

                    <div class="card">
                        <div class="card-header">
                            <b>{{ $query }}</b> に関連するアルバムを検索しています
                        </div>
                        {!! $table_view !!}
                        <div class="card-footer clearfix text-right">
                            <div class="btn-group">
                                @if ($page > 1)
                                    <a href="<?= url()->full() . '&page=' . ($page - 1) ?>" class="btn btn-default">前へ</a>
                                @endif
                                <a href="<?= url()->full() . '&page=' . ($page + 1) ?>" class="btn btn-default">次へ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection