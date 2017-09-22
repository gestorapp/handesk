<?php

namespace App\Http\Controllers;

use Artisan;
use Auth;
use Cache;
use Exception;
use Session;


class HomeController extends Controller
{
    public function index()
    {
        return redirect()->route('login');
    }

    private function showSystemSettings()
    {
        $data = [
            'account' =>'',
            'title' => trans('texts.system_settings'),
            'section' => '',
        ];

        return view('settings.system_settings', $data);
    }

    public function update()
    {

        try {
            set_time_limit(60 * 5);



            Artisan::call('clear-compiled');
            Artisan::call('cache:clear');

            if (!\App::environment('production')) {
                Artisan::call('debugbar:clear');
            }

            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');
            Artisan::call('optimize', ['--force' => true]);

            Auth::logout();
            Cache::flush();
            Session::flush();

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true, '--class' => 'UpdateSeeder']);

            // show message with link to Trello board
            $message = trans('texts.see_whats_new', ['version' => APP_VERSION]);
            $message = link_to(RELEASES_URL, $message, ['target' => '_blank']);
            $message = sprintf('%s - %s', trans('texts.processed_updates'), $message);
            // Flash::success($message);

        } catch (Exception $e) {

            // Utils::logError($e);

            return \Response::make($e->getMessage(), 500);
        }

        return redirect('/login')->with($message);
    }

    
}
