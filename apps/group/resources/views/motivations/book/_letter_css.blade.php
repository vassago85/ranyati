@php
    /**
     * Shared typography for a rendered motivation letter. Used by both the
     * dompdf template (motivations.book.template_letter) and the admin
     * builder canvas (admin.motivations.templates._canvas), so what you
     * edit in the builder looks like what prints.
     *
     * Pass $scope when embedding in the browser canvas — every selector is
     * prefixed with it so the letter styles can't leak into the admin UI.
     * Pass $fontSize + $fontFamily to override the defaults.
     */
    $scope = $scope ?? '';
    $prefix = $scope !== '' ? $scope.' ' : '';
    $fontFamily = $fontFamily ?? 'DejaVu Sans';
    $fontSize = (int) ($fontSize ?? 11);
@endphp
{{ $prefix }} {
    font-family: {{ $fontFamily }}, Verdana, Geneva, Tahoma, sans-serif;
    color: #111;
    font-size: {{ $fontSize }}pt;
    line-height: 1.55;
}
{!! $prefix !!}h1 { font-size: {{ $fontSize + 11 }}pt; margin: 0 0 6pt; }
{!! $prefix !!}h2 { font-size: {{ $fontSize + 5 }}pt; margin: 18pt 0 6pt; }
{!! $prefix !!}h3 { font-size: {{ $fontSize + 2 }}pt; margin: 14pt 0 6pt; }
{!! $prefix !!}p { margin: 0 0 8pt; text-align: justify; }
{!! $prefix !!}ul, {!! $prefix !!}ol { margin: 0 0 10pt 20pt; }
{!! $prefix !!}li { margin-bottom: 3pt; }
{!! $prefix !!}.title-page { text-align: center; padding-top: 60pt; }
{!! $prefix !!}.title-page h1 { font-size: 28pt; margin-bottom: 14pt; }
{!! $prefix !!}.title-page h2 { font-size: 15pt; color: #555; font-weight: normal; margin-top: 0; }
{!! $prefix !!}.title-page .body { margin: 36pt 10% 0; font-size: 12pt; line-height: 1.7; text-align: center; }
{!! $prefix !!}.title-page img { max-width: 50%; max-height: 180pt; margin: 0 auto 24pt; display: block; }
{!! $prefix !!}.divider { text-align: center; padding-top: 180pt; }
{!! $prefix !!}.divider h1 { font-size: 32pt; letter-spacing: 1pt; }
{!! $prefix !!}.annexure-tag { font-size: 11pt; text-transform: uppercase; letter-spacing: 2pt; color: #666; margin-bottom: 18pt; }
{!! $prefix !!}.img-left   { text-align: left; }
{!! $prefix !!}.img-center { text-align: center; }
{!! $prefix !!}.img-right  { text-align: right; }
{!! $prefix !!}.img-caption { font-size: 9pt; color: #555; margin-top: 4pt; }
{!! $prefix !!}.missing { background: #fee; color: #a00; padding: 0 2pt; border-radius: 2pt; }
