<?php
declare(strict_types=1);

use App\Utils;

test('addition fonctionne', function () {
    expect(Utils::add(2, 3))->toBe(5);
});