<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;
use DB;
use Artisan;

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
        $envFile = base_path().'/.env';
        // fresh install: .env does not exists. We create at runtime.
        if (! file_exists($envFile)) {
            if (! copy($envFile . '.example', $envFile)) {
                dd( "Error trying to write $envFile..." );
            }
            Artisan::call('key:generate');
            Artisan::call('config:clear');
            return Redirect::to('/');
        }

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration.");
        }

        $file = storage_path() . '/version.txt';
        $version = @file_get_contents($file);
        if ($version != APP_VERSION) {
            if (version_compare(phpversion(), '7.0.0', '<')) {
                dd('Please update PHP to >= 7.0.0');
            }
            $handle = fopen($file, 'w');
            fwrite($handle, APP_VERSION);
            fclose($handle);
            return Redirect::to('/update');
        }
        //dd($version);
        return $next($request);
    }
}
