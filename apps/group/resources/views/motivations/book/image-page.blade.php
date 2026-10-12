@extends('motivations.book._shell')

@section('content')
    <div class="image-page">
        <h2>{{ $title }}</h2>
        <img src="{{ $data_uri }}" alt="{{ $title }}">
    </div>
@endsection
