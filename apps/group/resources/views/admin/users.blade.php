@extends('admin.layout')
@section('title', 'Users')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        {{-- Users List --}}
        <div class="card">
            <div class="card-header">
                <h2>Admin Users</h2>
                <span class="badge badge-zinc">{{ $users->count() }} {{ Str::plural('user', $users->count()) }}</span>
            </div>
            @if($users->isEmpty())
                <div class="card-body" style="text-align:center;padding:48px 20px;">
                    <p style="font-size:13px;color:rgba(255,255,255,0.3);">No users yet.</p>
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
                        @foreach($users as $user)
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

        {{-- Add / Edit User --}}
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
                                @foreach(auth()->user()->assignableRoles() as $role)
                                    <option value="{{ $role }}">{{ \App\Models\User::ROLES[$role] }}</option>
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
    </script>
@endsection
