<?php

use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExportPayrollController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageLogController;
use App\Http\Controllers\Select2SourceController;
use App\Http\Controllers\Settings\GeneralSettingsController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WebhookController;
use App\Livewire\Calendars\AdminCalendar;
use App\Livewire\Calendars\EmployeeCalendar;
use App\Livewire\Datatables\ActivityDatatable;
use App\Livewire\Datatables\AnnouncementDatatable;
use App\Livewire\Datatables\AttendanceDatatable;
use App\Livewire\Datatables\AttendanceSummariesDatatable;
use App\Livewire\Datatables\ConstantDatatable;
use App\Livewire\Datatables\ContactDatatable;
use App\Livewire\Datatables\ContractDatatable;
use App\Livewire\Datatables\DocumentPropertyDatatable;
use App\Livewire\Datatables\DocumentTypeDatatable;
use App\Livewire\Datatables\InvoiceDatatable;
use App\Livewire\Datatables\LeaveBalanceDatatable;
use App\Livewire\Datatables\LeaveDatatable;
use App\Livewire\Datatables\LeaveReasonDatatable;
use App\Livewire\Datatables\LeaveTransactionDatatable;
use App\Livewire\Datatables\ManualEntryDatatable;
use App\Livewire\Datatables\ModificationDatatable;
use App\Livewire\Datatables\PayrollDatatable;
use App\Livewire\Datatables\RoleDatatable;
use App\Livewire\Datatables\ServiceCategoryDatatable;
use App\Livewire\Datatables\ServiceDatatable;
use App\Livewire\Datatables\TagDatatable;
use App\Livewire\Datatables\TicketDatatable;
use App\Livewire\Datatables\UserDatatable;
use App\Livewire\Datatables\VehicleDatatable;
use App\Livewire\EmploymentDetails;
use App\Livewire\Notifications;
use App\Livewire\PayrollManagement;
use App\Livewire\WorkingHoursOverview;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();

Route::get('auth/redirect/{provider?}', [SocialLoginController::class, 'redirect'])->name('auth.redirect');
Route::get('auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('auth.callback');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

Route::post('webhook/lexoffice', [WebhookController::class, 'lexoffice']);

Route::group(['middleware' => ['auth', 'vehicle.selected', 'log.activity']], function () {
    //Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('dashboard')->withoutMiddleware('vehicle.selected');
    Route::redirect('/dashboard', '/');

    //DMS
    Route::get('documents/{ticket:number}', [DocumentController::class, 'index'])->name('ticket.documents');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    Route::resource('documents', DocumentController::class)->only(['index', 'destroy']);

    // Datatables
    Route::get('tags', TagDatatable::class)->name('tags.index');
    Route::get('roles', RoleDatatable::class)->name('roles.index');
    Route::get('users', UserDatatable::class)->name('users.index');
    Route::get('payroll', PayrollDatatable::class)->name('payroll');
    Route::get('leaves', LeaveDatatable::class)->name('leaves.index');
    Route::get('tickets', TicketDatatable::class)->name('tickets.index');
    Route::get('contacts', ContactDatatable::class)->name('contacts.index');
    Route::get('services', ServiceDatatable::class)->name('services.index');
    Route::get('vehicles', VehicleDatatable::class)->name('vehicles.index');
    Route::get('invoices', InvoiceDatatable::class)->name('invoices.index');
    Route::get('contracts', ContractDatatable::class)->name('contracts.index');
    Route::get('constants', ConstantDatatable::class)->name('constants.index');
    Route::get('logs/activity', ActivityDatatable::class)->name('logs.activity');
    Route::get('attendances', AttendanceDatatable::class)->name('attendances.index');
    Route::get('leaves-balance', LeaveBalanceDatatable::class)->name('leaves.balance');
    Route::get('leave-reasons', LeaveReasonDatatable::class)->name('leave-reasons.index');
    Route::get('announcements', AnnouncementDatatable::class)->name('announcements.index');
    Route::get('modifications', ModificationDatatable::class)->name('modifications.index');
    Route::get('manual-entries', ManualEntryDatatable::class)->name('manual-entries.index');
    Route::get('document-types', DocumentTypeDatatable::class)->name('document-types.index');
    Route::get('serviceCategories', ServiceCategoryDatatable::class)->name('serviceCategories.index');
    Route::get('attendances/summary', AttendanceSummariesDatatable::class)->name('attendances.summary');
    Route::get('leave-transactions', LeaveTransactionDatatable::class)->name('leave-transactions.index');
    Route::get('document-properties', DocumentPropertyDatatable::class)->name('document-properties.index');

    // calendars
    Route::get('employee-calendar', EmployeeCalendar::class)->name('calendar.employee');
    Route::get('admin-calendar', AdminCalendar::class)->name('calendar.admin');

    Route::get('notifications', Notifications::class)->name('notifications');

    //Logs
    Route::get('logs/language', [LanguageLogController::class, 'index'])->name('logs.language');
    Route::get('logs/language/clear', [LanguageLogController::class, 'clear'])->name('logs.language.clear');


    //Users
    Route::get('users/employment/{employment}', EmploymentDetails::class)->name('user.show-employment');

    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    //TODO::remove - deprecated
    Route::get('working-hours', WorkingHoursOverview::class)->name('working-hours.index');

    // Settings
    Route::group(['prefix' => 'settings', 'namespace' => 'Settings'], function () {
        Route::get('general/edit', [GeneralSettingsController::class, 'edit'])->name('settings.general.edit');
        Route::post('general', [GeneralSettingsController::class, 'update'])->name('settings.general.update');
    });

    // System
    Route::get('system/', [SystemController::class, 'index'])->name('system.index');
    Route::post('system/', [SystemController::class, 'update'])->name('system.update');
    Route::get('system/update', [SystemController::class, 'systemIndex'])->name('system.systemindex');
    Route::get('system/update/up', [SystemController::class, 'systemUp'])->name('system.systemup');
    Route::get('system/update/down', [SystemController::class, 'systemDown'])->name('system.systemdown');
    Route::get('system/update/update', [SystemController::class, 'systemUpdate'])->name('system.systemupdate');
    Route::get('system/update/cache', [SystemController::class, 'systemCache'])->name('system.systemcache');

    Route::get('payroll/{payroll}', PayrollManagement::class)->name('payroll.manage');

    Route::view('pdf', 'pdf.payroll');
    Route::get('export', ExportPayrollController::class);


    Route::get('/data-sources/{source?}', Select2SourceController::class)
        ->name('select2-sources')
        ->withoutMiddleware('vehicle.selected');
});
