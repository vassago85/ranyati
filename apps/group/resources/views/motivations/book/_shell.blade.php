{{-- Shared PDF shell for every segment DomPDF renders. --}}
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 20mm 20mm 20mm 20mm; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #111; font-size: 11pt; line-height: 1.55; }
    h1 { font-size: 22pt; margin: 0 0 6pt; }
    h2 { font-size: 16pt; margin: 18pt 0 6pt; }
    h3 { font-size: 13pt; margin: 14pt 0 6pt; }
    p { margin: 0 0 8pt; text-align: justify; }
    .title-page { text-align: center; padding-top: 60pt; }
    .title-page h1 { font-size: 28pt; margin-bottom: 14pt; }
    .title-page h2 { font-size: 15pt; color: #555; font-weight: normal; margin-top: 0; }
    .title-page .body { margin: 36pt 10% 0; font-size: 12pt; line-height: 1.7; text-align: center; }
    .title-page img { max-width: 50%; max-height: 180pt; margin: 0 auto 24pt; display: block; }
    .divider { text-align: center; padding-top: 180pt; }
    .divider h1 { font-size: 32pt; letter-spacing: 1pt; }
    .annexure-tag { font-size: 11pt; text-transform: uppercase; letter-spacing: 2pt; color: #666; margin-bottom: 18pt; }
    .image-page { text-align: center; padding-top: 30pt; }
    .image-page h2 { margin: 0 0 18pt; }
    .image-page img { max-width: 100%; max-height: 220mm; }
    table.firearms { width: 100%; border-collapse: collapse; margin: 12pt 0 18pt; }
    table.firearms th, table.firearms td { border: 1px solid #999; padding: 4pt 6pt; font-size: 10pt; vertical-align: top; }
    table.firearms th { background: #f1f1f1; text-align: left; }
    .img-left   { text-align: left; }
    .img-center { text-align: center; }
    .img-right  { text-align: right; }
    .img-caption { font-size: 9pt; color: #555; margin-top: 4pt; }
    .page-break { page-break-after: always; }
    .missing { background: #fee; color: #a00; padding: 0 2pt; border-radius: 2pt; }
</style>
</head>
<body>
    @yield('content')
</body>
</html>
