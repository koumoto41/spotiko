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
                @foreach ($tag_table['td'] as $value)
                <tr>
                    @foreach ($value as $item)
                        <td>{{ e($item) }}</td>
                    @endforeach
                </tr>
                @endforeach
        </tbody>
    </table>
</div>
