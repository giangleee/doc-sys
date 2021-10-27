<table>
    <thead>
    <tr style="border: 1px solid #000000;">
        <th style="text-align: center;border: 1px solid #000000;background: #99cc00;">
            <b>{{ __('message.export.line') }}</b>
        </th>
        <th style="text-align: center;border: 1px solid #000000;background: #99cc00;">
            <b>{{ __('message.export.content') }}</b>
        </th>
    </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $value)
            <tr style="border: 1px solid #000000;">
                <td style="vertical-align: middle; text-align: center;border: 1px solid #000000;">{{ $key }}</td>
                <td style="border: 1px solid #000000;width: 400px;">{!! $value !!}</td>
            </tr>
        @endforeach
    </tbody>
</table>