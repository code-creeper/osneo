<?php

use App\Models\DocumentProperty;
use App\Models\DocumentType;
use App\Models\Employment;
use App\Models\User;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Database\Seeders\DocumentPropertySeeder;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\PermissionSeeder;
use Livewire\Features\SupportTesting\Testable;

use Spatie\Permission\Models\Permission;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;
use function Pest\testDirectory;

function login(User $user = null, $permissions = [])
{
    $user = $user ?? User::factory()->create();

    if ($permissions) {
        seed(PermissionSeeder::class);
        $user->givePermissionTo($permissions);
    }

    actingAs($user);

    return $user;
}

function loginWithPermissions($permissions = [], User $user = null)
{
    return login($user, $permissions);
}

function createHolidays(mixed $dates): void
{
    $holidays = [];

    if ( ! is_array($dates)) {
        $dates = array($dates);
    }

    foreach ($dates as $date){
        $holidays[] = Carbon::parse($date)->format('d-m-Y');
    }

    $settings = app(GeneralSettings::class);
    $settings->holidays = $holidays;
    $settings->save();
}

function setModelValues(Testable $component, $model, $values): void
{
    foreach ($values as $key => $value){
        $key = "$model.{$key}";
        $component->set($key, $value);
    }
}

function seedDocumentTypes($seedWithProperties = false): void
{
    if ( ! DocumentType::count()) {
        seed(DocumentTypeSeeder::class);
    }

    if ($seedWithProperties){
        seedDocumentProperties();
    }
}

function seedDocumentProperties(): void
{
    if ( ! DocumentProperty::count()) {
        seed(DocumentPropertySeeder::class);
    }
}

function seedPermissions(): void
{
    if ( ! Permission::count()) {
        seed(PermissionSeeder::class);
    }
}

function fixture($path): string
{
    return File::get(testDirectory("Fixtures/$path"));
}
