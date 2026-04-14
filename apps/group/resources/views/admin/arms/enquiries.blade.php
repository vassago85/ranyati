@extends('admin.layout')
@section('title', 'Arms Enquiries')

@section('actions')
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.arms.enquiries.mark-all-read') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm">Mark All Read ({{ $unreadCount }})</button>
        </form>
    @endif
@endsection

@section('content')
    <div class="card">
        @if($enquiries->isEmpty())
            <div class="card-body" style="text-align: center; padding: 64px 20px;">
                <svg style="width:48px;height:48px;color:rgba(255,255,255,0.08);margin:0 auto 16px;" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                <p style="font-size: 14px; color: rgba(255,255,255,0.3);">No arms enquiries yet.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:8px;"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Listing</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enquiries as $enquiry)
                        <tr>
                            <td>
                                @if(!$enquiry->read_at)
                                    <span class="dot dot-orange" title="Unread"></span>
                                @else
                                    <span class="dot dot-green" title="Read"></span>
                                @endif
                            </td>
                            <td style="{{ !$enquiry->read_at ? 'color:#fff;font-weight:600;' : '' }}">{{ $enquiry->name }}</td>
                            <td><a href="mailto:{{ $enquiry->email }}" style="color: #C45A3C;">{{ $enquiry->email }}</a></td>
                            <td>{{ $enquiry->phone ?: '—' }}</td>
                            <td>
                                @if($enquiry->listing)
                                    <a href="{{ route('admin.arms.edit', $enquiry->listing) }}" style="color: rgba(255,255,255,0.7);">
                                        {{ $enquiry->listing->make }} {{ $enquiry->listing->model }}
                                    </a>
                                @else
                                    <span style="color:rgba(255,255,255,0.2);">Deleted</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">{{ $enquiry->created_at->format('d M Y H:i') }}</td>
                            <td style="text-align:right;">
                                @if(!$enquiry->read_at)
                                    <form method="POST" action="{{ route('admin.arms.enquiries.read', $enquiry) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">Mark Read</button>
                                    </form>
                                @endif
                                @if($enquiry->message)
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('msg-{{ $enquiry->id }}').toggleAttribute('hidden')">Message</button>
                                @endif
                            </td>
                        </tr>
                        @if($enquiry->message)
                            <tr id="msg-{{ $enquiry->id }}" hidden>
                                <td></td>
                                <td colspan="6" style="padding: 8px 12px; background: rgba(255,255,255,0.02); border-left: 3px solid rgba(196,90,60,0.3); font-size: 13px; color: rgba(255,255,255,0.5);">
                                    {{ $enquiry->message }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            @if($enquiries->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.04); display: flex; justify-content: center; gap: 8px;">
                    @if($enquiries->onFirstPage())
                        <span class="btn btn-secondary btn-sm" style="opacity:0.3;pointer-events:none;">&laquo; Previous</span>
                    @else
                        <a href="{{ $enquiries->previousPageUrl() }}" class="btn btn-secondary btn-sm">&laquo; Previous</a>
                    @endif
                    <span style="padding:6px 12px;font-size:12px;color:rgba(255,255,255,0.4);">
                        Page {{ $enquiries->currentPage() }} of {{ $enquiries->lastPage() }}
                    </span>
                    @if($enquiries->hasMorePages())
                        <a href="{{ $enquiries->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next &raquo;</a>
                    @else
                        <span class="btn btn-secondary btn-sm" style="opacity:0.3;pointer-events:none;">Next &raquo;</span>
                    @endif
                </div>
            @endif
        @endif
    </div>
@endsection
