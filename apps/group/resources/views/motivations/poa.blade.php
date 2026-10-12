@extends('motivations.book._shell')

@section('content')
    <h1 style="text-align:center;">Power of Attorney</h1>
    <p style="text-align:center; color:#555; margin-bottom:24pt;">
        Reference: <strong>{{ $request->reference() }}</strong> &middot; Application: <strong>{{ $request->applicationType->name }}</strong>
    </p>

    <p style="font-size:11pt;">
        I, the undersigned,
        <strong>{{ $profile['full_name'] ?: '____________________________________________' }}</strong>
        @if (! empty($profile['id_number'])) (ID number {{ $profile['id_number'] }}) @endif,
        of
        <strong>{{ $profile['physical_address'] ?: '______________________________________________' }}</strong>,
        hereby give Power of Attorney to <strong>Riaan Kunneke of Ranyati Firearm Motivations (Pty) Ltd</strong> and/or his representative to legally prepare my motivation on my behalf.
    </p>

    <p>
        I declare that the information given to the aforementioned is true and correct and I indemnify him and/or his representative from any action set against them should the information supplied by me not be true and correct.
    </p>

    <table style="width:100%; margin-top:48pt; font-size:11pt;">
        <tr>
            <td style="width:55%; border-top:1px solid #333; padding-top:4pt;">Signature</td>
            <td style="width:5%;"></td>
            <td style="width:40%; border-top:1px solid #333; padding-top:4pt;">Date</td>
        </tr>
    </table>

    <p style="margin-top:36pt; font-size:11pt;">
        Signed at ______________________________________ on this _____ day of ____________________ 20_____.
    </p>

    <p style="margin-top:48pt; font-size:9pt; color:#666; text-align:center;">
        Print, sign in black pen, and upload the signed copy to your Ranyati Motivations account to continue your application.
    </p>
@endsection
