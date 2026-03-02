<?php

use App\Livewire\Forms\LeaveTransactionForm;
use App\Models\LeaveTransaction;
use App\Notifications\LeaveBalanceAdjusted;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(LeaveTransactionForm::class, ['user' => auth()->id()]);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

describe('validation', function (){
    it('will validate form', function () {
        $this->component
            ->call('submit')
            ->assertHasErrors();
    });

    test('amount should be changed when editing', function (){
        $transaction = LeaveTransaction::factory()->create();

        $component = Livewire::test(LeaveTransactionForm::class, [
            'user' => $transaction->user_id,
            'leaveTransaction' => $transaction->id
        ]);

        $component->call('submit')->assertHasErrors('amount');
    });
});

it('can submit form', function () {
    $this->component
        ->set('comments', 'some comments')
        ->set('amount', 3)
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Leave balance adjusted');

    Notification::assertSentTo(auth()->user(), LeaveBalanceAdjusted::class);
});
