@extends('templates.layout')

@section('title', 'STOCK')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <a href="<?= url('cms/stock/form') ?>" class="btn btn-primary">追加</a>
                    </div>
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