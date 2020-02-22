@extends('layout')

@section('title', 'MY TOP')

@section('content')
    <h1>My Top Artist</h1>
    <h2>{{ $display_artist_date }}</h2>
    <table>
        @foreach ($artist as $key => $value)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $value->name }}</td>
        </tr>
        @endforeach
    </table>

    <h1>My Top Track</h1>
    <h2>{{ $display_track_date }}</h2>
    <table>
        @foreach ($track as $key => $value)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $value->track_name }}</td>
                <td>{{ $value->album_name }}</td>
                <td>{{ $value->artist_name }}</td>
            </tr>
        @endforeach
    </table>

@endsection