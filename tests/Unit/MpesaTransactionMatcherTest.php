<?php

use App\Features\Mpesa\Services\MpesaTransactionMatcher;

it('normalizes Kenyan phone numbers consistently', function (): void {
    $matcher = new MpesaTransactionMatcher;

    expect($matcher->normalizePhone('+254712345678'))->toBe('254712345678')
        ->and($matcher->normalizePhone('0712345678'))->toBe('254712345678')
        ->and($matcher->normalizePhone('712345678'))->toBe('254712345678');
});
