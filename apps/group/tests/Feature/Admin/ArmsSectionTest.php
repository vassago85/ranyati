<?php

namespace Tests\Feature\Admin;

use App\Models\ArmsEnquiry;
use App\Models\ArmsListing;
use App\Models\User;
use App\Support\ArmsDescriptions;
use App\Support\ArmsPhone;
use App\Support\ArmsStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ArmsSectionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Arms Admin',
            'email' => 'arms-section@example.com',
            'password' => 'password123',
            'role' => User::ROLE_DEVELOPER,
        ]);
    }

    private function listing(User $admin, array $overrides = []): ArmsListing
    {
        return ArmsListing::create(array_merge([
            'title' => 'Listing',
            'make' => 'Make',
            'model' => 'Model',
            'calibre' => '9mm',
            'type' => 'handgun',
            'condition' => 'good',
            'price' => 7500,
            'status' => 'active',
            'is_featured' => false,
            'created_by' => $admin->id,
        ], $overrides));
    }

    private function armsUrl(string $path): string
    {
        return 'http://arms.ranyati.test/'.ltrim($path, '/');
    }

    public function test_reduce_price_and_remove_reduction(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin, ['price' => 8000]);

        $this->actingAs($admin)
            ->post(route('admin.arms.reduce', $listing), ['price' => 9000])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->post(route('admin.arms.reduce', $listing), ['price' => 7500])
            ->assertRedirect()
            ->assertSessionHas('success');

        $listing->refresh();
        $this->assertSame('8000.00', number_format((float) $listing->original_price, 2, '.', ''));
        $this->assertSame(7500.0, (float) $listing->price);

        $this->actingAs($admin)
            ->post(route('admin.arms.remove-reduction', $listing))
            ->assertSessionHas('success');

        $this->assertNull($listing->fresh()->original_price);
    }

    public function test_stock_numbers_are_generated_and_backfilled_in_created_order(): void
    {
        $admin = $this->admin();
        $first = $this->listing($admin, ['make' => 'First']);
        $second = $this->listing($admin, ['make' => 'Second']);

        $this->assertSame('A-0001', $first->stock_number);
        $this->assertSame('A-0002', $second->stock_number);

        $blank = ArmsListing::withoutEvents(function () use ($admin) {
            return ArmsListing::create([
                'title' => 'Blank',
                'slug' => 'blank-stock',
                'make' => 'Older',
                'calibre' => '9mm',
                'price' => 1000,
                'status' => 'active',
                'created_by' => $admin->id,
                'created_at' => now()->subYear(),
            ]);
        });

        $this->assertSame(1, ArmsStock::backfill());
        $this->assertSame('A-0003', $blank->fresh()->stock_number);
    }

    public function test_description_merge_uses_the_long_text_when_the_short_one_is_empty(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin, [
            'description' => null,
            'description_long' => 'The long write-up stays.',
        ]);

        $this->assertSame(1, ArmsDescriptions::fillEmptyFromLong());
        $this->assertSame('The long write-up stays.', $listing->fresh()->description);
        $this->assertSame('The long write-up stays.', $listing->fresh()->fullDescription());

        $kept = $this->listing($admin, [
            'description' => 'Short stays',
            'description_long' => 'Long stays too',
        ]);
        ArmsDescriptions::fillEmptyFromLong();
        $this->assertSame('Short stays', $kept->fresh()->description);
        $this->assertSame('Long stays too', $kept->fresh()->fullDescription());
    }

    public function test_phone_numbers_are_normalised_for_south_africa(): void
    {
        $this->assertSame('+27827454813', ArmsPhone::normalise('0827454813'));
        $this->assertSame('+27798920592', ArmsPhone::normalise('+27798920592'));
        $this->assertSame('+27766012082', ArmsPhone::normalise('(076) 601-2082'));
        $this->assertNull(ArmsPhone::normalise('12345'));
    }

    public function test_enquiry_status_changes_and_viewing_does_not(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin);
        $enquiry = ArmsEnquiry::create([
            'arms_listing_id' => $listing->id,
            'name' => 'Anna Buyer',
            'email' => 'anna@example.com',
            'phone' => '0827454813',
            'message' => 'Is it available?',
        ]);

        $this->assertSame('new', $enquiry->status);
        $this->assertSame('+27827454813', $enquiry->phone_e164);

        $this->actingAs($admin)
            ->get(route('admin.arms.enquiries', ['filter' => 'new']))
            ->assertOk()
            ->assertSee('Anna Buyer');

        $this->assertSame('new', $enquiry->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.arms.enquiries.replied', $enquiry))
            ->assertSessionHas('success', 'Marked as replied.');
        $this->assertSame('replied', $enquiry->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.arms.enquiries.close', $enquiry))
            ->assertSessionHas('success', 'Enquiry closed.');
        $this->assertSame('closed', $enquiry->fresh()->status);
    }

    public function test_whatsapp_reply_marks_the_enquiry_replied(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin, ['status' => 'sold', 'calibre' => '9mm', 'type' => 'handgun']);
        $this->listing($admin, ['make' => 'Similar', 'model' => 'One', 'calibre' => '9mm', 'type' => 'handgun']);
        $enquiry = ArmsEnquiry::create([
            'arms_listing_id' => $listing->id,
            'name' => 'Sam Buyer',
            'email' => 'sam@example.com',
            'phone' => '0827454813',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.arms.enquiries', ['filter' => 'all']))
            ->assertOk()
            ->assertSee('This firearm is sold. Suggest something similar.')
            ->assertSee('arms-badge-sold', false)
            ->assertSee('Sold, similar stock');

        $response = $this->actingAs($admin)
            ->post(route('admin.arms.enquiries.whatsapp', $enquiry), ['reply' => 'similar']);

        $response->assertRedirect();
        $this->assertStringContainsString('wa.me/27827454813', (string) $response->headers->get('Location'));
        $this->assertSame('replied', $enquiry->fresh()->status);
    }

    public function test_sold_and_archived_detail_pages_stay_up_without_an_enquiry_form(): void
    {
        $admin = $this->admin();
        $sold = $this->listing($admin, ['status' => 'sold', 'make' => 'Soldgun', 'model' => 'One', 'type' => 'rifle', 'calibre' => '.308']);
        $similar = $this->listing($admin, ['make' => 'Livegun', 'model' => 'Two', 'type' => 'rifle', 'calibre' => '.308']);
        $archived = $this->listing($admin, ['status' => 'archived', 'make' => 'Oldgun', 'model' => 'Three']);

        $soldPage = $this->get($this->armsUrl('/listings/'.$sold->slug));
        $soldPage->assertOk()
            ->assertSee('noindex', false)
            ->assertSee('Sold')
            ->assertSee('Livegun')
            ->assertDontSee('Enquire about this firearm')
            ->assertDontSee('Share on WhatsApp');

        $this->get($this->armsUrl('/listings/'.$archived->slug))
            ->assertOk()
            ->assertSee('No longer available')
            ->assertSee('noindex', false);

        $this->assertTrue($similar->isLive());
    }

    public function test_enquiries_are_blocked_for_sold_listings(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin, ['status' => 'sold']);
        Cache::put('arms-otp:buyer@example.com', '123456', now()->addMinutes(5));

        $this->postJson($this->armsUrl('/listing/'.$listing->id.'/enquire'), [
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'phone' => '0827454813',
            'message' => 'Please',
            'otp' => '123456',
        ])->assertStatus(422)
            ->assertJsonFragment(['error' => 'This firearm is sold. Look at the similar stock on the page, or send a general enquiry.']);

        $this->assertSame(0, ArmsEnquiry::count());
    }

    public function test_send_otp_requires_matching_email_confirmation(): void
    {
        // Missing confirmation → 422 with the "please confirm" copy from
        // routes/web.php. We assert on the message so a future regression
        // that drops the custom message is caught.
        $this->postJson($this->armsUrl('/arms/send-otp'), [
            'email' => 'buyer@gmail.com',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email_confirmation'])
            ->assertJsonFragment(['email_confirmation' => ['Please confirm your email.']]);

        // Mismatched confirmation → 422 with the "do not match" copy.
        $this->postJson($this->armsUrl('/arms/send-otp'), [
            'email' => 'buyer@gmail.com',
            'email_confirmation' => 'byuer@gmail.com',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email_confirmation'])
            ->assertJsonFragment(['email_confirmation' => ['Email addresses do not match.']]);

        // Nothing is cached because we never got past validation.
        $this->assertNull(Cache::get('arms-otp:buyer@gmail.com'));
        $this->assertNull(Cache::get('arms-otp:byuer@gmail.com'));

        // Matching confirmation → 200 and a 6-digit code lands in the cache
        // under the primary email, ready for the subsequent OTP check on
        // either /listing/{id}/enquire or /arms/general-enquiry.
        \Illuminate\Support\Facades\Mail::fake();
        $this->postJson($this->armsUrl('/arms/send-otp'), [
            'email' => 'buyer@gmail.com',
            'email_confirmation' => 'buyer@gmail.com',
        ])->assertOk()
            ->assertJsonFragment(['message' => 'Verification code sent.']);

        $code = Cache::get('arms-otp:buyer@gmail.com');
        $this->assertIsString($code);
        $this->assertSame(6, strlen($code));
    }

    public function test_general_enquiry_requires_verified_email(): void
    {
        // Posting without an OTP fails validation — the per-listing enquiry
        // already enforced this and the general-enquiry endpoint now matches.
        $this->postJson($this->armsUrl('/arms/general-enquiry'), [
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'phone' => '0827454813',
            'message' => 'A .308 rifle',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);

        // Posting with an OTP that doesn't match the cached code is rejected.
        Cache::put('arms-otp:buyer@example.com', '111111', 300);
        $this->postJson($this->armsUrl('/arms/general-enquiry'), [
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'phone' => '0827454813',
            'message' => 'A .308 rifle',
            'otp' => '000000',
        ])->assertStatus(422)
            ->assertJsonFragment(['error' => 'Invalid or expired verification code.']);

        $this->assertSame(0, ArmsEnquiry::count());
    }

    public function test_general_enquiry_honeypot_and_rate_limit(): void
    {
        // Honeypot still short-circuits BEFORE the OTP check, so a bot that
        // sends a dummy 6-digit code and fills the hidden `website` field
        // still gets the friendly response with nothing stored.
        $this->postJson($this->armsUrl('/arms/general-enquiry'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '0827454813',
            'message' => 'A rifle',
            'otp' => '000000',
            'website' => 'http://spam.test',
        ])->assertOk();

        $this->assertSame(0, ArmsEnquiry::count());

        Cache::put('arms-otp:buyer@example.com', '123456', 300);
        $this->postJson($this->armsUrl('/arms/general-enquiry'), [
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'phone' => '(076) 601-2082',
            'message' => 'A .308 rifle',
            'otp' => '123456',
            'type' => 'rifle',
            'budget' => 15000,
        ])->assertOk()
            ->assertJsonFragment(['message' => 'Thanks. We have your enquiry and will get back to you.']);

        // Successful submit should consume the cached code so it can't be
        // reused for a second enquiry on the same email.
        $this->assertNull(Cache::get('arms-otp:buyer@example.com'));

        $saved = ArmsEnquiry::first();
        $this->assertNull($saved->arms_listing_id);
        $this->assertSame('new', $saved->status);
        $this->assertSame('+27766012082', $saved->phone_e164);

        $this->actingAs($this->admin())
            ->get(route('admin.arms.enquiries', ['filter' => 'general']))
            ->assertOk()
            ->assertSee('Buyer');

        for ($i = 0; $i < 4; $i++) {
            Cache::put("arms-otp:buyer{$i}@example.com", '123456', 300);
            $this->postJson($this->armsUrl('/arms/general-enquiry'), [
                'name' => 'Buyer '.$i,
                'email' => "buyer{$i}@example.com",
                'phone' => '0827454813',
                'message' => 'Another',
                'otp' => '123456',
            ])->assertOk();
        }

        Cache::put('arms-otp:many@example.com', '123456', 300);
        $this->postJson($this->armsUrl('/arms/general-enquiry'), [
            'name' => 'Too Many',
            'email' => 'many@example.com',
            'phone' => '0827454813',
            'message' => 'Stop',
            'otp' => '123456',
        ])->assertStatus(429);
    }

    public function test_public_stock_order_filters_sort_and_new_badge(): void
    {
        config(['arms.max_featured' => 1, 'arms.new_badge_days' => 14]);
        $admin = $this->admin();
        $featured = $this->listing($admin, ['make' => 'Featured', 'model' => 'Rifle', 'type' => 'rifle', 'is_featured' => true, 'featured_at' => now(), 'price' => 5000]);
        $newer = $this->listing($admin, ['make' => 'Newer', 'model' => 'Pistol', 'type' => 'handgun', 'price' => 9000, 'created_at' => now()]);
        $older = $this->listing($admin, ['make' => 'Older', 'model' => 'Shotgun', 'type' => 'shotgun', 'price' => 2000]);
        $older->forceFill(['created_at' => now()->subDays(30)])->save();
        $extraFeatured = $this->listing($admin, ['make' => 'Second', 'model' => 'Feature', 'type' => 'rifle', 'is_featured' => true, 'featured_at' => now()->subDay(), 'price' => 1000]);

        $page = $this->get($this->armsUrl('/'));
        $page->assertOk()
            ->assertSee('Handguns')
            ->assertSee('Rifles')
            ->assertSee('Shotguns')
            ->assertSeeInOrder(['Featured Rifle', 'Newer Pistol'])
            ->assertSee('>New<', false)
            ->assertDontSee('info@firearmstorage.co.za');

        $this->assertTrue($featured->isNewArrival());
        $this->assertFalse($older->fresh()->isNewArrival());

        $this->get($this->armsUrl('/?type=shotgun'))
            ->assertOk()
            ->assertSee('Older Shotgun')
            ->assertDontSee('Newer Pistol');

        $this->get($this->armsUrl('/?sort=price_asc'))
            ->assertOk()
            ->assertSeeInOrder(['Second Feature', 'Older Shotgun', 'Featured Rifle', 'Newer Pistol']);

        $this->assertNotNull($extraFeatured->stock_number);
    }

    public function test_strip_description_numbers_is_a_dry_run_until_apply(): void
    {
        $admin = $this->admin();
        $listing = $this->listing($admin, [
            'description' => '34) The Beretta is clean.',
        ]);

        $this->artisan('arms:strip-description-numbers')
            ->expectsOutputToContain('['.$listing->id.'] description: "34) The Beretta is clean." → "The Beretta is clean."')
            ->expectsOutputToContain('Dry run only');

        $this->assertSame('34) The Beretta is clean.', $listing->fresh()->description);

        $this->artisan('arms:strip-description-numbers', ['--apply' => true])
            ->expectsOutputToContain('Updated 1 listing');

        $this->assertSame('The Beretta is clean.', $listing->fresh()->description);
    }

    public function test_status_badges_use_the_same_words(): void
    {
        $admin = $this->admin();
        $sold = $this->listing($admin, ['status' => 'sold', 'make' => 'Badge', 'model' => 'Gun']);
        ArmsEnquiry::create([
            'arms_listing_id' => $sold->id,
            'name' => 'Badge Buyer',
            'email' => 'badge@example.com',
            'phone' => '0827454813',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.arms', ['filter' => 'sold']))
            ->assertOk()
            ->assertSee('arms-badge-sold', false)
            ->assertSee('Sold');

        $this->actingAs($admin)
            ->get(route('admin.arms.enquiries', ['filter' => 'all']))
            ->assertOk()
            ->assertSee('arms-badge-sold', false)
            ->assertDontSee('>Archived<', false);
    }
}
