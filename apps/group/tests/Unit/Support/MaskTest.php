<?php

namespace Tests\Unit\Support;

use App\Support\Mask;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for the POPIA masking helper. These rules show up in
 * every admin list view, so each regression here is a potential
 * compliance incident.
 */
class MaskTest extends TestCase
{
    // -------- idNumber ------------------------------------------------

    public function test_masks_a_sa_id_number_to_first_six_digits(): void
    {
        $this->assertSame('820101*******', Mask::idNumber('8201015123089'));
    }

    public function test_strips_whitespace_before_masking(): void
    {
        $this->assertSame('820101*******', Mask::idNumber(' 8201015123089 '));
    }

    public function test_treats_non_digit_separators_as_cosmetic(): void
    {
        // Some member profiles were imported with spaces or dashes.
        // The masker must still see 13 digits and reveal only the first 6.
        $this->assertSame('820101*******', Mask::idNumber('820101 5123 089'));
        $this->assertSame('820101*******', Mask::idNumber('820101-5123-089'));
    }

    public function test_empty_or_null_input_yields_empty_string(): void
    {
        $this->assertSame('', Mask::idNumber(null));
        $this->assertSame('', Mask::idNumber(''));
        $this->assertSame('', Mask::idNumber('   '));
    }

    public function test_short_or_junk_values_return_opaque_stars_not_the_original(): void
    {
        // We never want a half-ID or "unknown" string leaking through.
        $this->assertSame('***', Mask::idNumber('xx'));
        $this->assertSame('*****', Mask::idNumber('12345'));
    }

    // -------- phone ---------------------------------------------------

    public function test_masks_a_cell_number_as_prefix_stars_suffix(): void
    {
        $this->assertSame('082 *** 4567', Mask::phone('0821234567'));
    }

    public function test_strips_spaces_and_dashes_before_masking(): void
    {
        $this->assertSame('082 *** 4567', Mask::phone('082 123 4567'));
        $this->assertSame('082 *** 4567', Mask::phone('082-123-4567'));
    }

    public function test_handles_international_prefix(): void
    {
        // "+27 82 123 4567" strips to "27821234567". First 3 digits are
        // "278" and the last 4 are "4567", so we expect "278 *** 4567".
        $this->assertSame('278 *** 4567', Mask::phone('+27 82 123 4567'));
    }

    public function test_phone_empty_or_null_input_yields_empty_string(): void
    {
        $this->assertSame('', Mask::phone(null));
        $this->assertSame('', Mask::phone(''));
    }

    public function test_short_or_junk_phone_values_return_opaque_stars(): void
    {
        $this->assertSame('******', Mask::phone('123456'));
        $this->assertSame('***', Mask::phone('ab'));
    }
}
