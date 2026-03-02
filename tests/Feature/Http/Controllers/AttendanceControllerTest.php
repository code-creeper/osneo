<?php

use App\Models\Attendance;
use App\Models\Modification;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;
use function Spatie\PestPluginTestTime\testTime;

/*uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    testTime()->freeze();
});

describe('deprecated test', function (){
    it('can render attendance index page', function () {
        login();
        $response = get('/attendances');
        $response->assertStatus(403);

        loginWithPermissions(Permissions::attendanceBasic());
        $response = get('/attendances');
        $response->assertSuccessful();

    });

    it('can render attendance create page', function () {
        login();
        $response = get(route('attendances.create'));
        $response->assertStatus(403);

        loginWithPermissions("create manual attendance");

        $response = get(route('attendances.create'));
        $response->assertSuccessful();

    });

    describe('store manual attendance', function () {
        beforeEach(function (){
            $this->user = User::factory()->create();

            // move time ahead, it doesn't become future time for checkout
            testTime()->setHour('20');

            $this->checkin = '08:00';
            $this->checkout = '10:00';

            $this->requestData = [
                'date' => today()->toDateString(),
                'checkin' => $this->checkin,
                'checkout' => $this->checkout,
            ];
        });

        it('can request for manual attendance', function ($createdBy) {
            loginWithPermissions(Permissions::attendanceBasic());

            if ($createdBy == 'admin'){
                auth()->user()->givePermissionTo('create manual attendance for all');
                $this->requestData['user_id'] = $this->user->id;
            }

            post(route('attendances.store.manually'), $this->requestData)
                ->assertRedirect(route('attendances.index'))
                ->assertSessionHas('success', $createdBy == 'admin'
                    ? 'Attendance created successfully'
                    : 'Request for creating attendance has been sent to admin'
                );

            $changes = [
                'date' => today()->toDateString(),
                'checkin' => today()->setTimeFrom($this->checkin)->roundMinute()->toISOString(),
                'checkout' => today()->setTimeFrom($this->checkout)->roundMinute()->toISOString(),
            ];

            if ($createdBy == 'admin') {
                assertDatabaseHas('attendances', [
                    'user_id' => $this->user->id,
                    'created_by' => auth()->id(),
                ]);
            }

            if ($createdBy == 'self') {
                expect($changes)->toBe(Modification::first()->source->toArray());
            }
        })->with('roles');

        it('can store manual attendance', function ($createdBy){
            loginWithPermissions(Permissions::attendanceWithoutApproval());

            if ($createdBy == 'admin'){
                auth()->user()->givePermissionTo('create manual attendance for all');
                $this->requestData['user_id'] = $this->user->id;
            }

            post(route('attendances.store.manually'), $this->requestData)
                ->assertRedirect(route('attendances.index'))
                ->assertSessionHas('success', 'Attendance created successfully');

            $this->checkin = today()->setTimeFrom($this->checkin)->roundMinute()->toDateTimeString();
            $this->checkout = today()->setTimeFrom($this->checkout)->roundMinute()->toDateTimeString();

            expect(Attendance::first())
                ->created_by->toBe($createdBy == 'admin' ? auth()->id() : null)
                ->user_id->toBe($createdBy == 'admin' ? $this->user->id : auth()->id())
                ->checkin->toDateTimeString()->toBe($this->checkin)
                ->checkout->toDateTimeString()->toBe($this->checkout)
                ->date->toDateString()->toBe(today()->toDateString());
        })->with('roles');
    });

    it('can render attendance edit page', function () {
        loginWithPermissions(Permissions::attendanceBasic());

        $attendance = Attendance::factory()->for(auth()->user())->create();
        $response = get(route('attendances.edit', $attendance->id));

        $response->assertSuccessful();
    });

    describe('update attendance', function () {
        it('can request for update attendance', function ($updatedBy) {
            $user = User::factory()->create();
            loginWithPermissions(Permissions::attendanceBasic());
            if ($updatedBy == 'admin') {
                auth()->user()->givePermissionTo('edit any attendance');
            }

            $attendance = Attendance::factory()->for($updatedBy == 'admin' ? $user : auth()->user())->create();

            // move time to 1 day ahead, so when we update checkout time,
            // it doesn't't become future time
            testTime()->addDay();
            put(route('attendances.update', $attendance), [
                'checkin' => $attendance->checkin->copy()->addHour()->format('H:i'),
                'checkout' => $attendance->checkout->copy()->addHour()->format('H:i'),
            ])
                ->assertRedirect(route('attendances.index'))
                ->assertSessionHas('success', $updatedBy == 'admin'
                    ? 'Attendance updated'
                    : 'Request for modification has been sent to admin'
                );

            $changes = [
                'checkin' => $attendance->checkin->addHour()->toISOString(),
                'checkout' => $attendance->checkout->addHour()->toISOString(),
            ];

            if ($updatedBy == 'self') {
                expect($changes)->toBe(Modification::first()->data->toArray());
            }

            if ($updatedBy == 'admin') {
                assertDatabaseHas('attendances', [
                    'user_id' => $user->id,
                    'updated_by' => auth()->id(),
                ]);
            }
        })->with('roles');

        it('can update attendance', function ($updatedBy) {
            $user = User::factory()->create();
            loginWithPermissions(Permissions::attendanceWithoutApproval());
            $attendance = Attendance::factory()->for($updatedBy == 'admin' ? $user : auth()->user())->create();

            if ($updatedBy == 'admin') {
                auth()->user()->givePermissionTo('edit any attendance');
            }

            // move time to 1 day ahead, so when we update checkout time,
            // it doesn't't become future time
            testTime()->addDay();

            $checkin = $attendance->checkin->copy()->addHour();
            $checkout = $attendance->checkout->copy()->addHour();

            put(route('attendances.update', $attendance), [
                'checkin' => $checkin->format('H:i'),
                'checkout' => $checkout->format('H:i'),
            ])
                ->assertRedirect(route('attendances.index'))
                ->assertSessionHas('success', 'Attendance updated');

            assertDatabaseHas('attendances', [
                'id' => $attendance->id,
                'updated_by' => $updatedBy == 'admin' ? auth()->id() : null,
                'user_id' => $updatedBy == 'admin' ? $user->id : auth()->id(),
                'checkin' => $checkin->toDateTimeString(),
                'checkout' => $checkout->toDateTimeString(),
            ]);
        })->with('roles');

        it('will not update attendance if nothing is changed', function ($updatedBy) {
            $user = User::factory()->create();
            loginWithPermissions(Permissions::attendanceWithoutApproval());

            if ($updatedBy == 'admin') {
                auth()->user()->givePermissionTo('edit any attendance');
            }

            $attendance = Attendance::factory()->for($updatedBy == 'admin' ? $user : auth()->user())->create();

            // move time to 1 day ahead, so when we update checkout time,
            // it doesn't't become future time
            testTime()->addDay();

            put(route('attendances.update', $attendance), [
                'checkin' => $attendance->checkin->format('H:i'),
                'checkout' => $attendance->checkout->format('H:i'),
            ])
                ->assertRedirect()
                ->assertSessionHas('info', 'No changes were made');
        })->with('roles');
    });
})->skip('will be removed');

dataset('roles', [
    'self',
    'admin',
]);*/
