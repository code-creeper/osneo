<?php

namespace App\Http\Controllers;

use App\Enums\LeaveAction;
use App\Enums\ModificationType;
use App\Http\Requests\LeaveFormRequest;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\LeaveActionTaken;
use DB;
use Exception;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Leave::class, 'leave');
        $this->middleware('log.activity')->only(['index', 'create', 'show', 'edit']);
    }

    //TODO::cleanup remove this, moved to form component. kept for reference
    public function store(LeaveFormRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = User::find($request->user_id);

            $leave = $user->leaves()->create($request->only([
                'created_by',
                'reason_id',
                'starts_on',
                'ends_on',
                'days',
            ]));

            if (user()->can('tag leaves')) {
                $leave->tags()->sync($request->tags);
            }

            // pre-approve a leave, if user have permission for it
            if (user()->can('preApprove', $leave)){
                $leave->approve();
            }
        } catch (Exception $exception) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        DB::commit();

        return redirect()->route('leaves.index')->with('success', 'Leave requested successfully');
    }

    public function update(LeaveFormRequest $request, Leave $leave)
    {
        if (user()->can('edit leaves without approval')) {

            $leave->update($request->only(
                'starts_on', 'ends_on', 'reason_id', 'days'
            ));

            if (user()->can('tag leaves')) {
                $leave->tags()->sync($request->tags);
            }

            // if leave is updated by someone else, send notification to user
            if (user()->id !== $leave->user_id) {
                $leave->user->notify(new LeaveActionTaken($leave, LeaveAction::Updated));
            }

            return redirect()->route('leaves.index')->with('success', 'Leave updated successfully');
        }

        if ($leave->createModification($request)) {
            return redirect()
                ->route('leaves.index')
                ->with('success', 'Request for modification has been sent to admin');
        }

        return redirect()->back()->with('info', 'No changes were made');
    }

    public function destroy(Request $request, Leave $leave)
    {
        if (user()->can('delete leaves without approval')) {
            $leave->delete();

            if (user()->id !== $leave->user_id) {
                $leave->user->notify(new LeaveActionTaken($leave, LeaveAction::Deleted));
            }

            return redirect()->back()->with('success', 'Leave deleted successfully');
        }

        $leave->createModification($request, ModificationType::Delete);

        return redirect()->back()->with('success', 'Request for deletion has been sent to admin');
    }
}
