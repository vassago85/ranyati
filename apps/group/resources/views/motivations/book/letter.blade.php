@extends('motivations.book._shell')

@section('content')
    @foreach ($items as $item)
        @switch($item['kind'])
            @case('heading')
                @php($level = max(1, min(3, (int) $item['level'])))
                @if ($level === 1)
                    <h1>{!! nl2br(e($item['text'])) !!}</h1>
                @elseif ($level === 2)
                    <h2>{!! nl2br(e($item['text'])) !!}</h2>
                @else
                    <h3>{!! nl2br(e($item['text'])) !!}</h3>
                @endif
                @break

            @case('paragraphs')
                @foreach (preg_split("/\r?\n\s*\r?\n/", trim((string) $item['text'])) as $paragraph)
                    @if (trim($paragraph) !== '')
                        <p>{!! nl2br(e($paragraph)) !!}</p>
                    @endif
                @endforeach
                @break

            @case('image')
                @if (! empty($item['data_uri']))
                    <div class="img-{{ $item['align'] ?? 'center' }}">
                        <img src="{{ $item['data_uri'] }}" style="width: {{ max(10, min(100, (int) ($item['width'] ?? 60))) }}%;">
                        @if (! empty($item['caption']))
                            <div class="img-caption">{{ $item['caption'] }}</div>
                        @endif
                    </div>
                @endif
                @break

            @case('firearms_table')
                @if (! empty($item['title']))
                    <h3>{{ $item['title'] }}</h3>
                @endif
                <table class="firearms">
                    <thead>
                        <tr>
                            @foreach (\App\Support\Motivations\QuestionSchema::FIREARM_COLUMNS as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['rows'] as $row)
                            <tr>
                                @foreach (array_keys(\App\Support\Motivations\QuestionSchema::FIREARM_COLUMNS) as $col)
                                    <td>{{ $row[$col] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @break

            @case('page_break')
                <div class="page-break"></div>
                @break
        @endswitch
    @endforeach
@endsection
