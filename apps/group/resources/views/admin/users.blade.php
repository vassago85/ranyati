@extends('admin.layout')
@section('title', 'Users')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        {{-- Admin Users List --}}
        <div class="card">
            <div class="card-header">
                <h2>Admin Users</h2>
                <span class="badge badge-zinc">{{ $admins->count() }} {{ Str::plural('user', $admins->count()) }}</span>
            </div>
            @if($admins->isEmpty())
                <div class="card-body" style="text-align:center;padding:48px 20px;">
                    <p style="font-size:13px;color:rgba(255,255,255,0.3);">No admin users yet.</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $user)
                            <tr>
                                <td style="color:#fff;font-weight:500;">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span style="font-size:10px;color:rgba(255,255,255,0.25);margin-left:4px;">(you)</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->role === 'developer')
                                        <span class="badge badge-orange">Developer</span>
                                    @elseif($user->role === 'owner')
                                        <span class="badge badge-blue">Owner</span>
                                    @else
                                        <span class="badge badge-zinc">Admin</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">{{ $user->created_at->format('d M Y') }}</td>
                                <td style="text-align:right;">
                                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                                        @if(auth()->user()->canManage($user) || $user->id === auth()->id())
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="editUser({{ $user->id }}, '{{ e($user->name) }}', '{{ e($user->email) }}', '{{ $user->role }}')">Edit</button>
                                        @endif
                                        @if(auth()->user()->canManage($user) && $user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" style="display:inline;" onsubmit="return confirm('Delete {{ e($user->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Add / Edit Admin User --}}
        <div>
            <div class="card" id="user-form-card">
                <div class="card-header">
                    <h2 id="form-title">Add User</h2>
                </div>
                <div class="card-body">
                    <form id="user-form" method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <input type="hidden" id="form-method" name="_method" value="POST" disabled>

                        <div class="form-group">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" name="name" class="form-input" placeholder="Full name" required value="{{ old('name') }}">
                            @error('name') <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="form-email" name="email" class="form-input" placeholder="user@ranyati.co.za" required value="{{ old('email') }}">
                            @error('email') <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-group">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-input">
                                @foreach(array_reverse(auth()->user()->assignableRoles()) as $role)
                                    <option value="{{ $role }}" @selected($loop->first)>{{ \App\Models\User::ROLES[$role] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="form-password" class="form-label">Password</label>
                            <input type="password" id="form-password" name="password" class="form-input" placeholder="Min 8 characters" required>
                            <div class="form-hint" id="password-hint">Required for new users.</div>
                            @error('password') <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                        </div>

                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary" style="flex:1;" id="form-submit-btn">
                                Create User
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancel-edit-btn" style="display:none;" onclick="resetForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Members (tracker) --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 24px;">
        {{-- Members List --}}
        <div class="card">
            <div class="card-header">
                <h2>Members</h2>
                <span class="badge badge-zinc">{{ $members->count() }} {{ Str::plural('member', $members->count()) }}</span>
            </div>
            <div class="card-body" style="padding-top:0;">
                <p style="font-size:12px;color:rgba(255,255,255,0.4);margin:0 0 4px;">Tracker accounts for Ranyati Motivations clients. Members can only access the SAPS application tracker.</p>
            </div>
            @if($members->isEmpty())
                <div class="card-body" style="text-align:center;padding:32px 20px;">
                    <p style="font-size:13px;color:rgba(255,255,255,0.3);">No members yet.</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                            <tr>
                                <td style="color:#fff;font-weight:500;">{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    @if($member->hasVerifiedEmail())
                                        <span class="badge badge-green">Verified</span>
                                    @else
                                        <span class="badge badge-zinc">Unverified</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">{{ $member->created_at->format('d M Y') }}</td>
                                <td style="text-align:right;">
                                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="editMember({{ $member->id }}, '{{ e($member->name) }}', '{{ e($member->email) }}')">Edit</button>
                                        <form method="POST" action="{{ route('admin.users.members.delete', $member) }}" style="display:inline;" onsubmit="return confirm('Delete {{ e($member->name) }}? This also permanently removes all of their tracked applications.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Add / Edit Member --}}
        <div>
            <div class="card" id="member-form-card">
                <div class="card-header">
                    <h2 id="member-form-title">Add Member</h2>
                </div>
                <div class="card-body">
                    <form id="member-form" method="POST" action="{{ route('admin.users.members.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="m-name" class="form-label">Name</label>
                            <input type="text" id="m-name" name="name" class="form-input" placeholder="Full name" required>
                            @error('name') <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-group">
                            <label for="m-email" class="form-label">Email</label>
                            <input type="email" id="m-email" name="email" class="form-input" placeholder="member@example.com" required>
                            @error('email') <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                        </div>

                        <div class="form-group">
                            <label for="m-password" class="form-label">Password</label>
                            <input type="password" id="m-password" name="password" class="form-input" placeholder="Min 8 characters" required>
                            <div class="form-hint" id="m-password-hint">Required for new members. They can sign in immediately.</div>
                            @error('password') <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p> @enderror
                        </div>

                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary" style="flex:1;" id="member-submit-btn">
                                Create Member
                            </button>
                            <button type="button" class="btn btn-secondary" id="member-cancel-btn" style="display:none;" onclick="resetMemberForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editUser(id, name, email, role) {
            document.getElementById('form-title').textContent = 'Edit User';
            document.getElementById('form-submit-btn').textContent = 'Update User';
            document.getElementById('cancel-edit-btn').style.display = 'inline-flex';
            document.getElementById('user-form').action = '/admin/users/' + id + '/update';

            document.getElementById('name').value = name;
            document.getElementById('form-email').value = email;
            document.getElementById('role').value = role;
            document.getElementById('form-password').required = false;
            document.getElementById('form-password').placeholder = 'Leave blank to keep current';
            document.getElementById('password-hint').textContent = 'Leave blank to keep current password.';

            document.getElementById('user-form-card').scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('form-title').textContent = 'Add User';
            document.getElementById('form-submit-btn').textContent = 'Create User';
            document.getElementById('cancel-edit-btn').style.display = 'none';
            document.getElementById('user-form').action = '{{ route("admin.users.store") }}';

            document.getElementById('name').value = '';
            document.getElementById('form-email').value = '';
            document.getElementById('role').value = 'admin';
            document.getElementById('form-password').value = '';
            document.getElementById('form-password').required = true;
            document.getElementById('form-password').placeholder = 'Min 8 characters';
            document.getElementById('password-hint').textContent = 'Required for new users.';
        }

        function editMember(id, name, email) {
            document.getElementById('member-form-title').textContent = 'Edit Member';
            document.getElementById('member-submit-btn').textContent = 'Update Member';
            document.getElementById('member-cancel-btn').style.display = 'inline-flex';
            document.getElementById('member-form').action = '/admin/users/members/' + id + '/update';

            document.getElementById('m-name').value = name;
            document.getElementById('m-email').value = email;
            document.getElementById('m-password').value = '';
            document.getElementById('m-password').required = false;
            document.getElementById('m-password').placeholder = 'Leave blank to keep current';
            document.getElementById('m-password-hint').textContent = 'Leave blank to keep current password.';

            document.getElementById('member-form-card').scrollIntoView({ behavior: 'smooth' });
        }

        function resetMemberForm() {
            document.getElementById('member-form-title').textContent = 'Add Member';
            document.getElementById('member-submit-btn').textContent = 'Create Member';
            document.getElementById('member-cancel-btn').style.display = 'none';
            document.getElementById('member-form').action = '{{ route("admin.users.members.store") }}';

            document.getElementById('m-name').value = '';
            document.getElementById('m-email').value = '';
            document.getElementById('m-password').value = '';
            document.getElementById('m-password').required = true;
            document.getElementById('m-password').placeholder = 'Min 8 characters';
            document.getElementById('m-password-hint').textContent = 'Required for new members. They can sign in immediately.';
        }
    </script>
@endsection
