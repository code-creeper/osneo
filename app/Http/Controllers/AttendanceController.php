<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceAction;
use App\Enums\ModificationType;
use App\Http\Requests\AttendanceFormRequest;
use App\Livewire\Datatables\AttendanceDatatable;
use App\Models\Attendance;
use App\Models\User;
use App\Notifications\AttendanceActionTaken;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


class AttendanceController extends Controller
{
    //todo::cleanup delete controller, moved to livewire

    public function __construct()
    {
        $this->authorizeResource(Attendance::class, 'attendance');
        $this->middleware('log.activity')->only(['index', 'create', 'show', 'edit']);
    }

    //TODO::cleanup remove this, moved to form component. kept for reference
    public function update(AttendanceFormRequest $request, Attendance $attendance)
    {
        if ( ! $attendance->isDirty()) {
            redirect()->back()->with('info', 'No changes were made');
        }

        if ($request->updated_by == null && $attendance->user->cannot('edit attendance without approval')) {
            $attendance->createModification($request);

            return redirect()->route('attendances.index')
                ->with('success', 'Request for modification has been sent to admin');
        }

        $attendance->update([
            'updated_by' => $request->updated_by,
            'checkin' => $request->checkin,
            'checkout' => $request->checkout
        ]);

        return redirect()->route('attendances.index')->with('success', 'Attendance updated');
    }

    public function destroy(Request $request, Attendance $attendance)
    {
        if (user()->can('delete attendance without approval')) {
            $attendance->delete();

            if ($attendance->user_id !== auth()->id()){
                $attendance->user->notify(new AttendanceActionTaken($attendance, AttendanceAction::Deleted));
            }

            return redirect()->back()->with('success', 'Attendance deleted successfully');
        }

        $attendance->createModification($request, ModificationType::Delete);

        return redirect()->back()->with('success', 'Request for deletion has been sent to admin');
    }

    public function storeManually(AttendanceFormRequest $request)
    {
        $user = User::find($request->user_id);
        $creatingForSelf = $request->created_by == null;

        if ( $creatingForSelf && $user->cannot('create manual attendance without approval')) {
            Attendance::requestCreation($request->only(['checkin', 'checkout', 'date']));

            return redirect()->route('attendances.index')
                ->with('success', 'Request for creating attendance has been sent to admin');
        }

        $user->attendances()->create($request->only([
            'created_by', 'checkin', 'checkout', 'date',
        ]));

        return redirect()->route('attendances.index')->with('success', 'Attendance created successfully');
    }
}
