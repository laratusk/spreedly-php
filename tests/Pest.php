<?php

declare(strict_types=1);

use Laratusk\Spreedly\Tests\Integration\IntegrationTestCase;
use Laratusk\Spreedly\Tests\LaravelTestCase;
use Laratusk\Spreedly\Tests\TestCase;

uses(TestCase::class)->in('Unit');
uses(LaravelTestCase::class)->in('Feature');
uses(IntegrationTestCase::class)->in('Integration');
