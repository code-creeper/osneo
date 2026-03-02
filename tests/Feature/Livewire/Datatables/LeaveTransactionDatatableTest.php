<?php

use App\Livewire\Datatables\LeaveTransactionDatatable;
use App\Models\LeaveTransaction;
use App\Notifications\LeaveBalanceAdjusted;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(LeaveTransactionDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show leave transactions', function () {
    LeaveTransaction::factory(3)->create();

    $this->component
        ->set('filterComponents.month', '')
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows->count())->toBeGreaterThan(0);

            return true;
        });
});

it('can delete leave transaction', function () {
    $leaveTransaction = LeaveTransaction::factory()->create();
    $comments = 'some comments';

    $this->component
        ->call('delete', $leaveTransaction->id)
        ->assertDispatched('modal.open', 'modals.confirmation-modal')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed', comments: $comments)
        ->assertDispatched('flashNotification', message: 'Leave balance adjusted successfully');

    Notification::assertSentTo($leaveTransaction->user, LeaveBalanceAdjusted::class);
    assertDatabaseHas(LeaveTransaction::class, [
        'amount' => 0 - $leaveTransaction->amount,
        'transacted_by' => auth()->id(),
        'comments' => $comments,
    ]);
});
