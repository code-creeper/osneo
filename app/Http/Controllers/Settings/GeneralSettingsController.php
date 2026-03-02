<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\LeaveReason;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class GeneralSettingsController extends Controller
{
	public function __construct(){
		//
	}

    public function index()
    {
        //
    }

    public function edit(GeneralSettings $settings)
    {
		$this->authorize('manage settings');
        $reasons = LeaveReason::all();

        return view('settings.general', compact('settings', 'reasons'));
    }

    public function update(Request $request, GeneralSettings $settings)
    {
		$this->authorize('manage settings');

        $settings->holidays = explode(',', $request->holidays);
        $settings->save();

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
