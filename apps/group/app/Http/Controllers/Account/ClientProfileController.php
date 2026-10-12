<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use Illuminate\Http\Request;

class ClientProfileController extends Controller
{
    public function edit(Request $request)
    {
        $profile = $request->user()->clientProfile ?: new ClientProfile([
            'full_names' => $request->user()->name,
            'email' => $request->user()->email,
        ]);

        return view('account.motivations.profile', ['profile' => $profile]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
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
        ]);

        $data['self_employed'] = $request->boolean('self_employed');
        $data['facebook_consent'] = $request->boolean('facebook_consent');

        ClientProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data,
        );

        return redirect()->route('account.motivations.index')
            ->with('success', 'Profile saved. You can now start a motivation.');
    }
}
