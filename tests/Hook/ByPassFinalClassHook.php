<?php

declare(strict_types=1);

namespace App\Tests\Hook;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

/**
 * Class ByPassFinalClassHook
 * @package App\Tests\Hook
 */
class ByPassFinalClassHook implements BeforeTestHook
{
    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
    }
}
