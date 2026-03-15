@extends('admin.layout')
@section('title', 'Enquiry from ' . $enquiry->name)

@section('actions')
    <a href="{{ route('admin.enquiries') }}" class="btn btn-secondary btn-sm">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Back
    </a>
    <form method="POST" action="{{ route('admin.enquiries.delete', $enquiry) }}" style="display:inline;" onsubmit="return confirm('Delete this enquiry?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
@endsection

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        {{-- Main details --}}
        <div class="card">
            <div class="card-header">
                <h2>Enquiry Details</h2>
                <div style="display:flex;align-items:center;gap:8px;">
                    @if(!$enquiry->read_at)
                        <span class="badge badge-orange">Unread</span>
                    @else
                        <span class="badge badge-green">Read {{ $enquiry->read_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <div class="form-label">Full Name</div>
                        <div style="font-size:14px;color:#fff;font-weight:500;">{{ $enquiry->name }}</div>
                    </div>
                    <div>
                        <div class="form-label">Email</div>
                        <div style="font-size:14px;">
                            <a href="mailto:{{ $enquiry->email }}" style="color:#38bdf8;text-decoration:underline;text-underline-offset:2px;">{{ $enquiry->email }}</a>
                        </div>
                    </div>
                    @if($enquiry->phone)
                    <div>
                        <div class="form-label">Phone</div>
                        <div style="font-size:14px;">
                            <a href="tel:{{ $enquiry->phone }}" style="color:#38bdf8;text-decoration:underline;text-underline-offset:2px;">{{ $enquiry->phone }}</a>
                        </div>
                    </div>
                    @endif
                    @if($enquiry->membership_number)
                    <div>
                        <div class="form-label">NRAPA Membership</div>
                        <div style="font-size:14px;color:#fff;font-weight:500;font-family:monospace;">{{ $enquiry->membership_number }}</div>
                    </div>
                    @endif
                    @if($enquiry->endorsement_type)
                    <div>
                        <div class="form-label">Endorsement Type</div>
                        <div style="font-size:14px;color:rgba(255,255,255,0.7);">{{ $enquiry->endorsement_type }}</div>
                    </div>
                    @endif
                    @if($enquiry->purpose)
                    <div>
                        <div class="form-label">Purpose</div>
                        <div style="font-size:14px;color:rgba(255,255,255,0.7);">{{ $enquiry->purpose }}</div>
                    </div>
                    @endif
                </div>

                @if($enquiry->message)
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.04);">
                    <div class="form-label">Message</div>
                    <div style="margin-top:8px;padding:16px;border-radius:8px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.04);font-size:13px;line-height:1.7;color:rgba(255,255,255,0.6);white-space:pre-wrap;">{{ $enquiry->message }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Sidebar meta --}}
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card">
                <div class="card-header"><h2>Meta</h2></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <div class="form-label">Source</div>
                        <div style="font-size:13px;">
                            @if($enquiry->source === 'nrapa_endorsement')
                                <span class="badge badge-blue">NRAPA Endorsement Referral</span>
                            @else
                                <span class="badge badge-zinc">Motivations Website</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="form-label">Submitted</div>
                        <div style="font-size:13px;color:rgba(255,255,255,0.6);">{{ $enquiry->created_at->format('d M Y \a\t H:i') }}</div>
                    </div>
                    <div>
                        <div class="form-label">ID</div>
                        <div style="font-size:13px;color:rgba(255,255,255,0.3);font-family:monospace;">#{{ $enquiry->id }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Actions</h2></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
                    <a href="mailto:{{ $enquiry->email }}?subject=Re: Motivation Enquiry" class="btn btn-primary" style="width:100%;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        Reply via Email
                    </a>
                    @if($enquiry->phone)
                    <a href="tel:{{ $enquiry->phone }}" class="btn btn-secondary" style="width:100%;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        Call
                    </a>
                    @endif
                    @if(!$enquiry->read_at)
                    <form method="POST" action="{{ route('admin.enquiries.toggle-read', $enquiry) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="width:100%;">Mark as Read</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.enquiries.toggle-read', $enquiry) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="width:100%;">Mark as Unread</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
