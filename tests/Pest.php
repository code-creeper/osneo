<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/


use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

use function Spatie\PestPluginTestTime\testTime;

uses(
    TestCase::class,
    CreatesApplication::class,
)
    ->beforeEach(function (){
        Bus::fake();
        Http::fake();
        Notification::fake();
        Storage::fake('s3');
        Storage::fake('public');
        LexofficeApi::fake();
        CreditreformApi::fake();

        testTime()->freeze();
    })
    ->in('Feature', 'Unit');


/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/
expect()->extend('toPassWith', function(mixed $value) {
    $rule = $this->value;

    /*if (! $rule instanceof InvokableRule) {
        throw new Exception('Value is not an invokable rule');
    }*/

    $passed = true;

    $fail = function() use (&$passed) {
        $passed = false;
    };

    $rule->validate('attribute', $value, $fail);

    expect($passed)->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/
