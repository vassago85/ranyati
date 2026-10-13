{{-- Shared PDF shell for a template-rendered letter segment. --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        margin: {{ (int) $margins['top'] }}mm {{ (int) $margins['right'] }}mm {{ (int) $margins['bottom'] }}mm {{ (int) $margins['left'] }}mm;
    }
    body {
        font-family: {{ $fontFamily }}, Arial, sans-serif;
        color: #111;
        font-size: {{ (int) $fontSize }}pt;
        line-height: 1.55;
    }
    h1 { font-size: {{ (int) $fontSize + 11 }}pt; margin: 0 0 6pt; }
    h2 { font-size: {{ (int) $fontSize + 5 }}pt; margin: 18pt 0 6pt; }
    h3 { font-size: {{ (int) $fontSize + 2 }}pt; margin: 14pt 0 6pt; }
    p { margin: 0 0 8pt; text-align: justify; }
    ul, ol { margin: 0 0 10pt 20pt; }
    li { margin-bottom: 3pt; }
    .title-page { text-align: center; padding-top: 60pt; }
    .title-page h1 { font-size: 28pt; margin-bottom: 14pt; }
    .title-page h2 { font-size: 15pt; color: #555; font-weight: normal; margin-top: 0; }
    .title-page .body { margin: 36pt 10% 0; font-size: 12pt; line-height: 1.7; text-align: center; }
    .title-page img { max-width: 50%; max-height: 180pt; margin: 0 auto 24pt; display: block; }
    .divider { text-align: center; padding-top: 180pt; }
    .divider h1 { font-size: 32pt; letter-spacing: 1pt; }
    .annexure-tag { font-size: 11pt; text-transform: uppercase; letter-spacing: 2pt; color: #666; margin-bottom: 18pt; }
    .img-left   { text-align: left; }
    .img-center { text-align: center; }
    .img-right  { text-align: right; }
    .img-caption { font-size: 9pt; color: #555; margin-top: 4pt; }
    .page-break { page-break-after: always; }
    .missing { background: #fee; color: #a00; padding: 0 2pt; border-radius: 2pt; }
    header.pdf-header, footer.pdf-footer {
        position: fixed;
        left: 0; right: 0;
        color: #555;
        font-size: 9pt;
    }
    header.pdf-header { top: -{{ max(5, (int) $margins['top'] - 10) }}mm; }
    footer.pdf-footer { bottom: -{{ max(5, (int) $margins['bottom'] - 10) }}mm; }
    header.pdf-header .inner, footer.pdf-footer .inner { padding: 4pt 0; }
    header.pdf-header img { max-height: {{ (int) $margins['top'] - 6 }}mm; }
    .page-number { text-align: right; }
    .header-first-only { display: block; }
    .header-rest-only { display: none; }
    .page_1_of_many .header-first-only { display: block; }
</style>
</head>
<body>
@if ($header['enabled'])
    <header class="pdf-header">
        <div class="inner">
            @if (! empty($header['data_uri']))
                <img src="{{ $header['data_uri'] }}" alt="">
            @endif
            @if ($header['text'] !== '')
                <div>{!! $header['text'] !!}</div>
            @endif
        </div>
    </header>
@endif

@if ($footer['enabled'])
    <footer class="pdf-footer">
        <div class="inner">
            @if ($footer['text'] !== '')
                <div>{!! $footer['text'] !!}</div>
            @endif
            @if ($footer['show_page_numbers'])
                <div class="page-number">
                    Page <span class="pagenum"></span>
                </div>
            @endif
        </div>
        @if ($footer['show_page_numbers'])
            {{-- DomPDF replaces this script with page numbers after layout. --}}
            <script type="text/php">
                if (isset($pdf)) {
                    $font = $fontMetrics->get_font("{!! addslashes($fontFamily) !!}", "normal");
                    $size = 9;
                    $pdf->page_text(
                        $pdf->get_width() - {{ (int) $margins['right'] }} * 2.83 - 60,
                        $pdf->get_height() - {{ (int) $margins['bottom'] }} * 2.83 + 6,
                        "Page {PAGE_NUM} of {PAGE_COUNT}",
                        $font, $size, [0.3, 0.3, 0.3]
                    );
                }
            </script>
        @endif
    </footer>
@endif

{!! $body !!}
</body>
</html>
