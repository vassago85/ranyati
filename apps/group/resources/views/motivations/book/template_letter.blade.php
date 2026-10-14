{{-- Shared PDF shell for a template-rendered letter segment. The header
     and footer bands are drawn by dompdf's canvas page_script callback
     (see LetterPdf::attachBands), so this view only owns the body. --}}
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
</style>
</head>
<body>
{!! $body !!}
</body>
</html>
