<?php
declare(strict_types=1);

use App\Utils;

test('addition fonctionne', function () {
    expect(Utils::add(2, 3))->toBe(5);
});

test('multiplication avec nombres positifs', function () {
    expect(Utils::multiply(2, 3))->toBe(6);
    expect(Utils::multiply(5, 7))->toBe(35);
});

test('multiplication avec nombres négatifs', function () {
    expect(Utils::multiply(-2, 3))->toBe(-6);
    expect(Utils::multiply(2, -3))->toBe(-6);
    expect(Utils::multiply(-2, -3))->toBe(6);
});

test('multiplication avec zéro', function () {
    expect(Utils::multiply(0, 5))->toBe(0);
    expect(Utils::multiply(5, 0))->toBe(0);
    expect(Utils::multiply(0, 0))->toBe(0);
});

test('multiplication avec grands nombres', function () {
    expect(Utils::multiply(1000000, 1000000))->toBe(1000000000000);
    expect(Utils::multiply(2147483647, 1))->toBe(2147483647); // MAX_INT
});