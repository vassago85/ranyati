<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationRequest;
use App\Models\User;
use App\Support\Motivations\PreviousMotivationData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Walk-in / phone-in workflow: an admin builds a member account, completes
 * the profile, starts a motivation and fills in the questionnaire + uploads
 * on the customer's behalf. The customer never needs to log in.
 */
class MotivationMemberController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $query = User::query()
            ->where('role', User::ROLE_CLIENT)
            ->with('clientProfile')
            ->withCount('motivationRequests')
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('clientProfile', function ($p) use ($search) {
                        $p->where('surname', 'like', "%{$search}%")
                            ->orWhere('full_names', 'like', "%{$search}%")
                            ->orWhere('id_number', 'like', "%{$search}%")
                            ->orWhere('cell', 'like', "%{$search}%");
                    });
            });
        }

        return view('admin.motivations.members.index', [
            'members' => $query->paginate(25)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.motivations.members.create', [
            'profile' => new ClientProfile([
                'full_names' => $request->query('name'),
                'cell' => $request->query('phone'),
                'email' => $request->query('email'),
            ]),
            'prefillName' => $request->query('name'),
            'prefillEmail' => $request->query('email'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(array_merge(
            [
                'name' => 'required|string|max:255',
                'account_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'send_welcome' => 'nullable|boolean',
            ],
            $this->profileRules(),
        ));

        $password = Str::random(14);

        $user = DB::transaction(function () use ($data, $password) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['account_email'],
                'password' => $password,
                'role' => User::ROLE_CLIENT,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            ClientProfile::create($this->profileAttributes($data, $user));

            return $user;
        });

        return redirect()->route('admin.motivation-members.show', $user)
            ->with('success', 'Member created. Temporary password: '.$password.' — share this securely if the member will log in themselves.');
    }

    public function show(User $motivationMember)
    {
        abort_unless($motivationMember->role === User::ROLE_CLIENT, 404);

        $motivationMember->load(['clientProfile', 'motivationRequests.applicationType']);
        $motivationMember->motivationRequests = $motivationMember->motivationRequests->sortByDesc('created_at')->values();

        return view('admin.motivations.members.show', [
            'member' => $motivationMember,
            'profile' => $motivationMember->clientProfile,
            'activeTypes' => ApplicationType::where('is_active', true)->orderBy('sort')->orderBy('name')->get(),
        ]);
    }

    public function editProfile(User $motivationMember)
    {
        abort_unless($motivationMember->role === User::ROLE_CLIENT, 404);
        $profile = $motivationMember->clientProfile ?: new ClientProfile([
            'full_names' => $motivationMember->name,
            'email' => $motivationMember->email,
        ]);

        return view('admin.motivations.members.profile', [
            'member' => $motivationMember,
            'profile' => $profile,
        ]);
    }

    public function updateProfile(Request $request, User $motivationMember)
    {
        abort_unless($motivationMember->role === User::ROLE_CLIENT, 404);

        $data = $request->validate($this->profileRules());

        ClientProfile::updateOrCreate(
            ['user_id' => $motivationMember->id],
            $this->profileAttributes($data, $motivationMember),
        );

        return redirect()->route('admin.motivation-members.show', $motivationMember)
            ->with('success', 'Profile updated.');
    }

    public function startMotivation(Request $request, User $motivationMember)
    {
        abort_unless($motivationMember->role === User::ROLE_CLIENT, 404);

        $profile = $motivationMember->clientProfile;
        if (! $profile || $profile->missingRequired() !== []) {
            return redirect()->route('admin.motivation-members.profile.edit', $motivationMember)
                ->with('info', 'Complete the profile first — it is used on every motivation.');
        }

        $data = $request->validate([
            'application_type_id' => 'required|exists:application_types,id',
            'reuse_previous' => 'nullable|boolean',
        ]);

        $type = ApplicationType::where('is_active', true)->findOrFail($data['application_type_id']);

        $motivation = $motivationMember->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);

        $message = 'Motivation started for '.$motivationMember->name.' — fill in the questionnaire below.';

        if ($request->boolean('reuse_previous')) {
            $summary = PreviousMotivationData::applyTo($motivation, $request->user()->id);
            $suffix = [];
            if ($summary['answers_reused']) {
                $suffix[] = 'answers pre-filled';
            } elseif ($summary['firearms_reused']) {
                $suffix[] = 'firearm particulars pre-filled';
            }
            if ($summary['documents_reused'] !== []) {
                $suffix[] = 'carried over '.implode(', ', $summary['documents_reused']);
            }
            if ($summary['stale_kinds'] !== []) {
                $suffix[] = 'still needed: '.implode(', ', $summary['stale_kinds']);
            }
            if ($suffix !== []) {
                $message .= ' ('.implode('; ', $suffix).')';
            }
        }

        return redirect()->route('admin.motivation-requests.edit', $motivation)
            ->with('success', $message);
    }

    private function profileAttributes(array $data, User $user): array
    {
        return [
            'user_id' => $user->id,
            'full_names' => $data['full_names'],
            'surname' => $data['surname'],
            'id_number' => $data['id_number'],
            'physical_address' => $data['physical_address'],
            'postal_address' => $data['postal_address'] ?? null,
            'employer' => $data['employer'] ?? null,
            'self_employed' => (bool) ($data['self_employed'] ?? false),
            'business_address' => $data['business_address'] ?? null,
            'occupation' => $data['occupation'],
            'cell' => $data['cell'],
            'fax' => $data['fax'] ?? null,
            'tel_work' => $data['tel_work'] ?? null,
            'tel_home' => $data['tel_home'] ?? null,
            'email' => $data['email'],
            'spouse_details' => $data['spouse_details'] ?? null,
            'facebook_consent' => (bool) ($data['facebook_consent'] ?? false),
            'facebook_name' => $data['facebook_name'] ?? null,
            'association_name' => $data['association_name'] ?? null,
            'association_number' => $data['association_number'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profileRules(): array
    {
        return [
            'full_names' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'id_number' => 'required|string|max:20',
            'physical_address' => 'required|string|max:1000',
            'postal_address' => 'nullable|string|max:1000',
            'employer' => 'nullable|string|max:255',
            'self_employed' => 'nullable|boolean',
            'business_address' => 'nullable|string|max:1000',
            'occupation' => 'required|string|max:255',
            'cell' => 'required|string|max:30',
            'fax' => 'nullable|string|max:30',
            'tel_work' => 'nullable|string|max:30',
            'tel_home' => 'nullable|string|max:30',
            'email' => 'required|email|max:255',
            'spouse_details' => 'nullable|string|max:255',
            'facebook_consent' => 'nullable|boolean',
            'facebook_name' => 'nullable|string|max:255',
            'association_name' => 'nullable|string|max:255',
            'association_number' => 'nullable|string|max:255',
        ];
    }
}
