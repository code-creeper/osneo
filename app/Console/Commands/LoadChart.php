<?php

namespace App\Console\Commands;

use App\Models\Document;
use \DateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\File;
use Illuminate\Console\Command;

class LoadChart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(): void
    {
        Cache::put('loadChartMonth', $this->loadChartMonth(), 80000);
        Cache::put('loadChartWeek', $this->loadChartWeek(), 80000);
        Cache::put('loadChartDay', $this->loadChartDay(), 80000);
    }

    public function loadChartMonth(): bool|string
    {
        $start = Carbon::create(2020, 7);
        $result = array();

        while ($start->copy()->subMonth()->format('Y-m') != Carbon::now()->format('Y-m')) {
            $count = Document::where('source', 'DEB')
                ->where('document_type_id', 1)
                ->whereBetween('created_at', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
                ->count();

            $result[] = [$start->copy()->endOfMonth()->getTimestampMs(), $count];

            $start = $start->addMonth();
        }

        return json_encode($result);
    }

    public function loadChartWeek(): bool|string
    {
        $start = Carbon::create(2020, 7)->startOfWeek();
        $result = array();

        while ($start->copy()->format('Y-m-W') != Carbon::now()->startOfWeek()->format('Y-m-W')) {
            $count = Document::where('source', 'DEB')
                ->where('document_type_id', 1)
                ->whereBetween('created_at', [$start->toDateString(), $start->copy()->endOfWeek()->toDateString()])
                ->count();

            $result[] = [$start->copy()->endOfWeek()->getTimestampMs(), $count];

            $start = $start->addWeek();
        }

        return json_encode($result);
    }

    public function loadChartDay(): bool|string
    {
        $start = Carbon::create(2020, 7, 1);
        $result = array();

        while ($start->copy()->format('Y-m-d') != Carbon::now()->format('Y-m-d')) {
            $count = Document::where('source', 'DEB')
                ->where('document_type_id', 1)
                ->whereBetween('created_at', [$start->copy()->startOfDay()->toDateTimeString(), $start->copy()->endOfDay()->toDateTimeString()])
                ->count();

            $result[] = [$start->copy()->getTimestampMs(), $count];

            $start = $start->addDay();
        }

        return json_encode($result);
    }
}
