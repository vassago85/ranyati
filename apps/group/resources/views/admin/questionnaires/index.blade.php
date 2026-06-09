@extends('admin.layout')
@section('title', 'Digital Questionnaires')

@section('content')
    <div class="alert alert-info">
        <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
        Scaffold / preview — these digital forms are only reachable here in the admin panel and are not publicly accessible yet.
    </div>

    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header"><h2>Available Questionnaires</h2></div>
        <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
            @foreach($questionnaires as $key => $q)
                <div style="border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 18px; background: rgba(255,255,255,0.02);">
                    <div style="font-size: 14px; font-weight: 700; color: #fff;">{{ $q['title'] }}</div>
                    <div style="font-size: 12px; color: rgba(255,255,255,0.35); margin-top: 6px; line-height: 1.5;">{{ $q['blurb'] ?? '' }}</div>
                    <div style="font-size: 11px; color: rgba(255,255,255,0.25); margin-top: 10px;">{{ count(\App\Support\QuestionnaireRegistry::fields($q)) }} fields &middot; {{ count($q['sections']) }} sections</div>
                    <a href="{{ route('admin.questionnaires.fill', $key) }}" class="btn btn-primary btn-sm" style="margin-top: 14px;">Fill in (test)</a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2>Submissions</h2></div>
        @if($responses->isEmpty())
            <div class="card-body" style="text-align: center; padding: 48px 20px;">
                <p style="font-size: 13px; color: rgba(255,255,255,0.3);">No submissions yet. Fill in a questionnaire above to test it.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Questionnaire</th>
                        <th>Submitted</th>
                        <th>By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($responses as $response)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #fff;">{{ $response->applicant_name ?: '—' }}</div>
                                <div style="font-size: 11px; color: rgba(255,255,255,0.25);">{{ $response->applicant_email }}</div>
                            </td>
                            <td>{{ $response->questionnaire_title }}</td>
                            <td>{{ $response->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $response->submitter?->name ?? '—' }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <a href="{{ route('admin.questionnaires.response', $response) }}" class="btn btn-secondary btn-sm">View</a>
                                    <form method="POST" action="{{ route('admin.questionnaires.response.delete', $response) }}" style="display:inline;" onsubmit="return confirm('Delete this submission?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($responses->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.04);">{{ $responses->links() }}</div>
            @endif
        @endif
    </div>
@endsection
