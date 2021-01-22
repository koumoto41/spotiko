<div class="card-body table-responsive">
    <table class="table table-hover text-nowrap">
        <thead>
            <tr>
                @foreach ($table['th'] as $value)
                    <th>{{ e($value) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
                @foreach ($table['td'] as $value)
                <tr>
                    @foreach ($value as $item)
                        <td>{!! $item !!}</td>
                    @endforeach
                </tr>
                @endforeach
        </tbody>
    </table>
</div>
