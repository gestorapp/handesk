<?php

namespace App\Http\Middleware;

use Artisan;
use Closure;
use DB;

class StartupCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->is('setup') && !$request->is('update*')) {

            if (!$this->envExists() || $this->needsDbSetup()) {

                return redirect('/setup');
            }

            if ($this->checkNeedsUpdate()) {

                return redirect('/update');
            }
        }

        return $next($request);
    }

    protected function envExists()
    {
        $envFile = base_path().'/.env';
        if (!file_exists($envFile)) {
            if (!copy($envFile.'.example', $envFile)) {
                dd("Error trying to write $envFile...");
            }
            Artisan::call('key:generate');
            return false;
        }
        return true;

    }

    protected function needsDbSetup()
    {
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return true;
        }
        return false;
    }

    protected function checkNeedsUpdate()
    {
        $file = storage_path().'/version.txt';
        $version = @file_get_contents($file);
        if ($version != APP_VERSION) {
            if (version_compare(phpversion(), '7.0.0', '<')) {
                dd('Please update PHP to >= 7.0.0');
            }
            $handle = fopen(storage_path().'/version.txt', 'w');
            fwrite($handle, APP_VERSION);
            fclose($handle);

            return true;
        }
    }

}
