<?php

use App\Features\Contributions\Models\Contribution;
use App\Features\Members\Models\Member;
use App\Support\Sqids\SqidsEncoder;

test('encodes and decodes a model primary key', function () {
    $encoder = app(SqidsEncoder::class);

    $sqid = $encoder->encodeForModel(Member::class, 42);
    $decoded = $encoder->decodeForModel(Member::class, $sqid);

    expect($sqid)->not->toBeEmpty()
        ->and($decoded)->toBe(42);
});

test('produces different sqids for different model classes with the same primary key', function () {
    $encoder = app(SqidsEncoder::class);

    $memberSqid = $encoder->encodeForModel(Member::class, 5);
    $contributionSqid = $encoder->encodeForModel(Contribution::class, 5);

    expect($memberSqid)->not->toBe($contributionSqid);
});

test('returns null when decoding a sqid for the wrong model class', function () {
    $encoder = app(SqidsEncoder::class);

    $sqid = $encoder->encodeForModel(Member::class, 10);

    expect($encoder->decodeForModel(Contribution::class, $sqid))->toBeNull();
});

test('returns null for invalid sqid strings', function () {
    $encoder = app(SqidsEncoder::class);

    expect($encoder->decodeForModel(Member::class, 'not-a-valid-sqid'))->toBeNull();
});
