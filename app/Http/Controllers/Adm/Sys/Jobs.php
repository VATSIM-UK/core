<?php

namespace App\Http\Controllers\Adm\Sys;

use DB;
use Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\Adm\AdmController;

class Jobs extends AdmController
{
    public function __construct()
    {
        parent::__construct();

        if (config('queue.default') !== 'database') {
            abort(500, 'Unable to manage jobs using '.config('queue.default').' queue driver.');
        }
    }

    public function getFailed(Request $request)
    {
        $jobs = DB::table('jobs_failed')->orderBy('failed_at', 'ASC');
        if ($request->has('filter_query')) {
            $jobs->where('payload', 'LIKE', '%'.$request->input('filter_query').'%');
        }
        $jobs = $jobs->paginate(20);

        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            $job->job = $payload['job'];
            $job->data = $payload['data'];
        }

        foreach ($jobs as $job) {
            foreach ($job->data as $key => &$data) {
                $data = str_replace(['{', '}', ';'], ['{<br>', '}<br>', ';<br>'], $data);
                $data = explode('<br>', $data);
                $count = 0;
                foreach ($data as &$line) {
                    $startObject = false;
                    if (starts_with($line, 'O:')) {
                        $startObject = true;
                    } elseif (starts_with($line, '}')) {
                        $count -= 4;
                        if ($count < 0) {
                            $count = 0;
                        }
                    }

                    $line = str_repeat('&nbsp;', $count).$line;

                    $count += $startObject ? 4 : 0;
                }

                $data = implode('<br>', $data);
            }
        }

        return $this->viewMake('adm.sys.jobs.failed')->with('jobs', $jobs);
    }

    public function postFailed(Request $request, $id)
    {
        if ($request->has('filter_query')) {
            $ids = DB::table('jobs_failed')->where('payload', 'LIKE', '%'.$request->input('filter_query').'%')->pluck('id');
            $exitCode = Artisan::call('queue:retry', ['id' => $ids]);
        } elseif ($id == 'all') {
            $exitCode = Artisan::call('queue:retry', ['id' => ['all']]);
        } else {
            $exitCode = Artisan::call('queue:retry', ['id' => [$id]]);
        }

        return back();
    }

    public function deleteFailed(Request $request, $id)
    {
        if ($request->has('filter_query')) {
            $ids = DB::table('jobs_failed')->where('payload', 'LIKE', '%'.$request->input('filter_query').'%')->pluck('id');
            foreach ($ids as $id) {
                $exitCode = Artisan::call('queue:forget', ['id' => $id]);
            }
        } elseif ($id == 'all') {
            $exitCode = Artisan::call('queue:flush');
        } else {
            $exitCode = Artisan::call('queue:forget', ['id' => $id]);
        }

        return back();
    }
}
