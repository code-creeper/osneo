<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\System;

class SystemController extends Controller
{
	
	public function __construct(){
		$this->middleware('log.activity');
	}
	
    public function index(){
		$this->authorize('manage updates');
		
		$data['env'] = System::retrievingENV();
        return view('system.general', $data);
    }
	
    public function update(Request $request)
    {
		$this->authorize('manage updates');
		
		foreach($request->all() as $key => $value){
			System::updateEnv($key, $value);
		}
        return redirect()->route('system.index')->with('success', __('Settings Updated Successfully'));
    }

    public function systemIndex(){
		$this->authorize('manage updates');
		
        return view('system.update');
    }

    public function systemUp(){
		$this->authorize('manage updates');
		
		System::up();
        return redirect()->back()->with('success', __('System is live.'));
    }
	
    public function systemDown(){
		$this->authorize('manage updates');
		
		$hash = System::downWithSecret();
        return redirect('/' . $hash);
    }

    public function systemUpdate(){
		$this->authorize('manage updates');
		
		System::update();
        return redirect()->back()->with('success', __('Update was successful.'));
    }

    public function systemCache(){
		$this->authorize('manage updates');
		
		System::clearCache();
        return redirect()->back()->with('success', __('Cache clear.'));
    }
}