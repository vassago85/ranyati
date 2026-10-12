<?php

namespace Tests\Feature\Motivations;

use App\Mail\MotivationUpdate;
use App\Models\ApplicationType;
use App\Models\ClientProfile;
use App\Models\MotivationDocument;
use App\Models\MotivationRequest;
use App\Models\User;
use App\Support\Motivations\DocumentKinds;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MotivationFulfilmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['motivations.storage' => 'local']);
        config(['motivations.inbox' => 'inbox@ranyati.test']);
        Mail::fake();
    }

    private function member(string $email = 'member@example.com'): User
    {
        $user = User::create([
            'name' => 'Member',
            'email' => $email,
            'password' => 'secret12',
            'role' => User::ROLE_CLIENT,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        ClientProfile::create([
            'user_id' => $user->id,
            'full_names' => 'Member',
            'surname' => 'Smith',
            'id_number' => '9001011234080',
            'physical_address' => '1 Test Rd',
            'occupation' => 'Clerk',
            'cell' => '0821234567',
            'email' => $email,
        ]);

        return $user;
    }

    private function admin(): User
    {
        $u = User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@ranyati.test',
            'password' => 'secret12',
            'role' => User::ROLE_DEVELOPER,
        ]);
        $u->forceFill(['email_verified_at' => now()])->save();

        return $u;
    }

    private function simpleType(): ApplicationType
    {
        return ApplicationType::firstOrCreate(
            ['slug' => 'simple-fulfilment'],
            [
                'name' => 'Simple',
                'category' => 'sport',
                'is_active' => true,
                'has_motivation' => true,
                'questions' => [],
                'document_requirements' => [
                    ['kind' => 'id_document', 'label' => 'ID', 'required' => true, 'min_count' => 1],
                ],
            ],
        );
    }

    private function printedRequest(User $user, string $delivery = MotivationRequest::DELIVERY_COURIER): MotivationRequest
    {
        $request = $user->motivationRequests()->create([
            'application_type_id' => $this->simpleType()->id,
            'status' => MotivationRequest::STATUS_PRINTED,
            'answers' => [],
            'delivery_method' => $delivery,
        ]);

        // Simulate the printed book being on disk.
        Storage::disk('local')->put('motivations/'.$request->id.'/book.pdf', '%PDF-1.4 book');
        $request->update([
            'pdf_disk' => 'local',
            'pdf_path' => 'motivations/'.$request->id.'/book.pdf',
            'pdf_generated_at' => now(),
            'printed_at' => now(),
        ]);

        return $request;
    }

    private function withMotivationsHost(): self
    {
        return $this->withServerVariables(['HTTP_HOST' => 'motivations.ranyati.test']);
    }

    public function test_submit_requires_delivery_method_and_sends_the_two_submission_emails(): void
    {
        $user = $this->member();
        $type = $this->simpleType();
        $request = $user->motivationRequests()->create([
            'application_type_id' => $type->id,
            'status' => MotivationRequest::STATUS_DRAFT,
            'answers' => [],
        ]);
        MotivationDocument::create([
            'motivation_request_id' => $request->id,
            'kind' => 'id_document',
            'disk' => 'local',
            'path' => 'motivations/'.$request->id.'/id.pdf',
            'original_name' => 'id.pdf',
            'mime' => 'application/pdf',
            'size' => 10,
        ]);

        $this->withMotivationsHost()
            ->actingAs($user)
            ->post('/account/motivations/'.$request->id.'/submit')
            ->assertSessionHasErrors('delivery_method');

        $this->withMotivationsHost()
            ->actingAs($user)
            ->post('/account/motivations/'.$request->id.'/submit', [
                'delivery_method' => MotivationRequest::DELIVERY_COLLECT,
            ])
            ->assertRedirect();

        $fresh = $request->fresh();
        $this->assertSame(MotivationRequest::STATUS_SUBMITTED, $fresh->status);
        $this->assertSame(MotivationRequest::DELIVERY_COLLECT, $fresh->delivery_method);

        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_SUBMITTED);
        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_NEW_SUBMISSION_INTERNAL);
    }

    public function test_admin_upload_invoice_moves_to_invoiced_and_notifies_member(): void
    {
        $user = $this->member();
        $request = $this->printedRequest($user, MotivationRequest::DELIVERY_COLLECT);

        $this->actingAs($this->admin())
            ->post('/admin/motivation-requests/'.$request->id.'/invoice', [
                'file' => UploadedFile::fake()->create('invoice.pdf', 30, 'application/pdf'),
                'invoice_total' => 1500.50,
            ])
            ->assertRedirect();

        $fresh = $request->fresh();
        $this->assertSame(MotivationRequest::STATUS_INVOICED, $fresh->status);
        $this->assertEquals('1500.50', (string) $fresh->invoice_total);
        $this->assertDatabaseHas('motivation_documents', [
            'motivation_request_id' => $request->id,
            'kind' => DocumentKinds::INVOICE,
        ]);
        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_INVOICED);
    }

    public function test_member_can_upload_proof_of_payment_only_while_invoiced(): void
    {
        $user = $this->member();
        $request = $this->printedRequest($user, MotivationRequest::DELIVERY_COLLECT);

        // Not invoiced yet: upload is denied.
        $this->withMotivationsHost()
            ->actingAs($user)
            ->post('/account/motivations/'.$request->id.'/documents', [
                'kind' => DocumentKinds::PROOF_OF_PAYMENT,
                'file' => UploadedFile::fake()->create('pop.pdf', 10, 'application/pdf'),
            ])
            ->assertForbidden();

        $request->update(['status' => MotivationRequest::STATUS_INVOICED]);

        $this->withMotivationsHost()
            ->actingAs($user)
            ->post('/account/motivations/'.$request->id.'/documents', [
                'kind' => DocumentKinds::PROOF_OF_PAYMENT,
                'file' => UploadedFile::fake()->create('pop.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('motivation_documents', [
            'motivation_request_id' => $request->id,
            'kind' => DocumentKinds::PROOF_OF_PAYMENT,
        ]);
        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_PROOF_OF_PAYMENT_INTERNAL);
    }

    public function test_mark_paid_requires_invoice_and_notifies_member(): void
    {
        $user = $this->member();
        $request = $this->printedRequest($user);

        // Not invoiced: PRINTED is still allowed because collection payments can happen without an invoice step.
        $this->actingAs($this->admin())
            ->post('/admin/motivation-requests/'.$request->id.'/paid', [
                'payment_method' => 'cash',
                'payment_reference' => 'walk-in',
            ])
            ->assertRedirect();

        $this->assertSame(MotivationRequest::STATUS_PAID, $request->fresh()->status);
        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_PAID);
    }

    public function test_mark_shipped_requires_waybill_and_sets_courier(): void
    {
        $user = $this->member();
        $request = $this->printedRequest($user, MotivationRequest::DELIVERY_COURIER);
        $request->update(['status' => MotivationRequest::STATUS_PAID]);

        $this->actingAs($this->admin())
            ->post('/admin/motivation-requests/'.$request->id.'/shipped', [
                'courier' => '',
                'waybill_number' => '',
            ])
            ->assertSessionHasErrors(['courier', 'waybill_number']);

        $this->actingAs($this->admin())
            ->post('/admin/motivation-requests/'.$request->id.'/shipped', [
                'courier' => 'Courier Guy',
                'waybill_number' => 'CG-123456',
            ])
            ->assertRedirect();

        $fresh = $request->fresh();
        $this->assertSame(MotivationRequest::STATUS_SHIPPED, $fresh->status);
        $this->assertSame('Courier Guy', $fresh->courier);
        $this->assertSame('CG-123456', $fresh->waybill_number);
        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_SHIPPED);
    }

    public function test_mark_ready_for_collection_requires_paid_and_collect_delivery(): void
    {
        $user = $this->member();
        $request = $this->printedRequest($user, MotivationRequest::DELIVERY_COURIER);
        $request->update(['status' => MotivationRequest::STATUS_PAID]);

        // Delivery is courier — endpoint rejects.
        $this->actingAs($this->admin())
            ->post('/admin/motivation-requests/'.$request->id.'/ready-for-collection')
            ->assertStatus(422);

        $request->update(['delivery_method' => MotivationRequest::DELIVERY_COLLECT]);

        $this->actingAs($this->admin())
            ->post('/admin/motivation-requests/'.$request->id.'/ready-for-collection', ['note' => 'Office closes at 4pm'])
            ->assertRedirect();

        $this->assertSame(MotivationRequest::STATUS_READY_FOR_COLLECTION, $request->fresh()->status);
        Mail::assertSent(MotivationUpdate::class, fn ($m) => $m->stage === MotivationUpdate::STAGE_READY_FOR_COLLECTION);
    }

    public function test_member_sees_a_collapsed_status_label_while_admin_sees_the_detailed_one(): void
    {
        $r = new MotivationRequest(['status' => MotivationRequest::STATUS_AWAITING_ENDORSEMENT]);
        $this->assertSame('Under review', $r->memberStatusLabel());
        $this->assertSame('Awaiting endorsement', $r->statusLabel());

        $r->status = MotivationRequest::STATUS_READY_TO_PRINT;
        $this->assertSame('Under review', $r->memberStatusLabel());

        $r->status = MotivationRequest::STATUS_INVOICED;
        $this->assertSame('Awaiting payment', $r->memberStatusLabel());

        $r->status = MotivationRequest::STATUS_SHIPPED;
        $this->assertSame('Shipped', $r->memberStatusLabel());
    }

    public function test_request_changes_dispatches_a_mail_with_the_admin_note(): void
    {
        $user = $this->member();
        $request = $user->motivationRequests()->create([
            'application_type_id' => $this->simpleType()->id,
            'status' => MotivationRequest::STATUS_SUBMITTED,
            'answers' => [],
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/motivation-requests/'.$request->id.'/request-changes', [
                'reviewer_notes' => 'Please redo your address.',
            ])
            ->assertRedirect();

        Mail::assertSent(MotivationUpdate::class, function ($m) {
            return $m->stage === MotivationUpdate::STAGE_CHANGES_REQUESTED
                && $m->note === 'Please redo your address.';
        });
    }
}
