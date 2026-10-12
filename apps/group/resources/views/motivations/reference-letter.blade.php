@extends('motivations.book._shell')

@section('content')
    <h1 style="text-align:center;">Reference letter / testimonial / confirmation of character</h1>
    <p style="color:#555; text-align:center; margin-bottom:20pt;">
        Reference: <strong>{{ $request->reference() }}</strong>
    </p>

    <p style="font-size:10pt;">
        <strong>First reference:</strong> spouse or family member (if not married).<br>
        <strong>Second and third reference:</strong> friend / colleague / neighbour.
    </p>

    <p>Full names &amp; surname: ______________________________________________________________</p>
    <p>ID number: __________________________________________________________________________</p>
    <p>Home address: _______________________________________________________________________</p>
    <p>_____________________________________________________________________________________</p>
    <p>Tel (work): ____________________________ Cell number: ________________________________</p>

    <h3>Questions</h3>
    <ol style="padding-left:18pt;">
        <li>What is the relationship between you and the applicant? <br>__________________________________________________________________</li>
        <li>How many years is the applicant known to you? <br>__________________________________________________________________</li>
        <li>Is he / she emotionally stable and does he / she have problems controlling his / her temper? <br>__________________________________________________________________</li>
        <li>Is the applicant inclined to act violently or is he / she aggressive in his / her behaviour? <br>__________________________________________________________________</li>
        <li>Does the applicant use drugs or alcohol? <br>__________________________________________________________________</li>
        <li>In your opinion, is the applicant responsible enough to use and handle a firearm? If not, state reasons. <br>__________________________________________________________________</li>
        <li>State any comments that you feel we should take into consideration when considering the application. <br>__________________________________________________________________</li>
    </ol>

    <table style="width:100%; margin-top:36pt;">
        <tr>
            <td style="width:55%; border-top:1px solid #333; padding-top:4pt;">Signature</td>
            <td style="width:5%;"></td>
            <td style="width:40%; border-top:1px solid #333; padding-top:4pt;">Date</td>
        </tr>
    </table>

    <p style="margin-top:36pt; font-size:9pt; color:#666; text-align:center;">
        Please complete in black pen and upload the signed reference to your Ranyati Motivations account.
    </p>
@endsection
