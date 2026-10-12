@extends('motivations.book._shell')

@section('content')
    <div class="title-page">
        @if ($image)
            <img src="{{ $image }}" alt="">
        @endif
        @if ($title !== '')
            <h1>{{ $title }}</h1>
        @endif
        @if ($subtitle !== '')
            <h2>{{ $subtitle }}</h2>
        @endif
        @if ($body !== '')
            <div class="body">
                @foreach (preg_split("/\r?\n\s*\r?\n/", trim($body)) as $paragraph)
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @endforeach
            </div>
        @endif
    </div>
@endsection
