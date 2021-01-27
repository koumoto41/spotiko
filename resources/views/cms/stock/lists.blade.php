@extends('templates.layout')

@section('title', 'STOCK')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <a href="<?= url('cms/stock/form') ?>" class="btn btn-primary">アルバムを追加</a>
                    </div>
                    <form action="{{ url('cms/stock') }}" method="get" accept-charset="utf-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">持ってるアルバムから検索</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="album">検索キーワード</label>
                                    <input name="query" type="text" class="form-control" value="{{ $params['query'] }}">
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary text-white" type="submit">検索</button>
                            </div>
                        </div>
                    </form>
                    <div class="card">
                        <div class="card-header">
                            <b class="text-danger">{{ $count }} </b>枚のアルバムが見つかりました。
                        </div>
                        {!! $table_view !!}
                        <div class="card-footer clearfix">
                            {{ $paginame->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection