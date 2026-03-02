<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Helpers\System;

class LanguageLogController extends Controller
{

    //todo::cleanup: what are we using this for ? might need to delete it
    private $activity = '';

    public function __construct()
    {
        $this->activity = Activity::where('log_name', 'language')
            ->selectRaw('count(id) as occurred, description, properties')
            ->groupBy('description')
            ->orderBy('occurred', 'desc')
            ->get();
    }

    public function index()
    {
        $this->activity = $this->activity->transform(function ($activity) {
            $activity->language = json_decode($activity->properties, true)['attributes']['language'];

            return $activity;
        });

        $this->activity = $this->activity->reject(function ($activity) {
            return app('translator')->has($activity->description);
        });

        $this->activity = $this->activity->paginate(25);

        $data['logs'] = $this->activity;

        return view('log.language', $data);
    }

    public function clear()
    {
        System::clearLanguageLog();

        return redirect()->route('logs.language');
    }
}
