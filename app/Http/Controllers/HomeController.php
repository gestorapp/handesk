<?php

namespace App\Http\Controllers;

use Artisan;
use Auth;
use Cache;
use Config;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Session;

class HomeController extends Controller
{
    public function index()
    {
        return redirect()->route('login');
    }

    public function install()
    {
        app('debugbar')->disable();

        $data = [
            'account' => '',
            'title'   => trans('texts.system_settings'),
            'section' => '',
        ];

        // \Former::framework('TwitterBootstrap');

        return view('settings.system_settings', $data);
    }

    public function uninstall()
    {
        if (file_exists(storage_path().'/version.txt')) {
            unlink(storage_path().'/version.txt');
        }
        if (file_exists(base_path().'/.env')) {
            unlink(base_path().'/.env');
        }

        return ['status' => 'Success'];
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

            // show message with link
            $message = trans('texts.see_whats_new', ['version' => APP_VERSION]);
            $message = link_to(RELEASES_URL, $message, ['target' => '_blank']);
            $message = sprintf('%s - %s', trans('texts.processed_updates'), $message);
            // Flash::success($message);

        } catch (Exception $e) {

            // Utils::logError($e);
            return response($e->getMessage(), 500);
        }

        return redirect('/login')->with($message);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function doSetup(Request $request)
    {

        $valid = false;
        $test = $request->get('test');

        $app = $request->get('app');
        $app['key'] = env('APP_KEY') ?: strtolower(str_random(RANDOM_KEY_LENGTH));
        $app['debug'] = $request->get('debug') ? 'true' : 'false';
        $app['https'] = $request->get('https') ? 'true' : 'false';

        $database = $request->get('database');
        $dbType = 'mysql'; // $database['default'];
        $database['connections'] = [$dbType => $database['type']];
        $mail = $request->get('mail');

        if ($test == 'mail') {
            return self::testMail($mail);
        }

        $valid = self::testDatabase($database);

        if ($test == 'db') {
            return $valid === true ? 'Success' : $valid;
        } elseif (!(true === $valid)) {
            return redirect('/setup')
                ->withErrors(['database' => 'Cant connect to database.'])
                ->withInput();
        }

        $_ENV['APP_ENV'] = 'production';
        $_ENV['APP_DEBUG'] = $app['debug'];
        $_ENV['APP_LOCALE'] = 'en';
        $_ENV['APP_FALLBACK_LOCALE'] = 'en';
        $_ENV['APP_URL'] = $app['url'];
        $_ENV['APP_KEY'] = $app['key'];
        $_ENV['APP_CIPHER'] = env('APP_CIPHER', 'AES-256-CBC');

        $_ENV['REQUIRE_HTTPS'] = $app['https'];

        $_ENV['DB_TYPE'] = $dbType;
        $_ENV['DB_HOST'] = $database['type']['host'];
        $_ENV['DB_DATABASE'] = $database['type']['database'];
        $_ENV['DB_USERNAME'] = $database['type']['username'];
        $_ENV['DB_PASSWORD'] = $database['type']['password'];

        $_ENV['MAIL_DRIVER'] = $mail['driver'];
        $_ENV['MAIL_PORT'] = $mail['port'];
        $_ENV['MAIL_ENCRYPTION'] = $mail['encryption'];
        $_ENV['MAIL_HOST'] = $mail['host'];
        $_ENV['MAIL_USERNAME'] = $mail['username'];
        $_ENV['MAIL_FROM_NAME'] = $mail['from']['name'];
        $_ENV['MAIL_FROM_ADDRESS'] = $mail['from']['address'];
        $_ENV['MAIL_PASSWORD'] = $mail['password'];

        $_ENV['PHANTOMJS_CLOUD_KEY'] = 'a-demo-key-with-low-quota-per-ip-address';
        $_ENV['PHANTOMJS_SECRET'] = strtolower(str_random(32));

        $_ENV['MAILGUN_DOMAIN'] = $mail['mailgun_domain'];
        $_ENV['MAILGUN_SECRET'] = $mail['mailgun_secret'];

        $_ENV['MAIL_FETCH_HOST'] = 'smtp.yourmail.com';
        $_ENV['MAIL_FETCH_PORT'] = '110';
        $_ENV['MAIL_FETCH_USERNAME'] = 'hello@handesk.com';
        $_ENV['MAIL_FETCH_PASSWORD'] = 'secret-password';
        $_ENV['MAIL_FETCH_OPTIONS'] = '/pop3';

        $_ENV['MAIL_SSLOPTIONS_ALLOW_SELF_SIGNED'] = 'false';
        $_ENV['MAIL_SSLOPTIONS_VERIFY_PEER'] = 'true';
        $_ENV['MAIL_SSLOPTIONS_VERIFY_PEER_NAME'] = 'true';

        $config = '';
        foreach ($_ENV as $key => $val) {
            if (is_array($val)) {
                continue;
            }
            if (preg_match('/\s/', $val)) {
                $val = "'{$val}'";
            }
            $config .= "{$key}={$val}\n";
        }

        // Write Config Settings
        $fp = fopen(base_path().'/.env', 'w');
        fwrite($fp, $config);
        fclose($fp);

        // // == DB Migrate & Seed == //
        // $sqlFile = base_path() . '/database/setup.sql';
        // DB::unprepared(file_get_contents($sqlFile));
        // Cache::flush();
        // Artisan::call('optimize', ['--force' => true]);
        //
        // $firstName = trim($request->get('first_name'));
        // $lastName = trim($request->get('last_name'));
        // $email = trim(strtolower($request->get('email')));
        // $password = trim($request->get('password'));
        // $account = $this->accountRepo->create($firstName, $lastName, $email, $password);
        // $user = $account->users()->first();

        return redirect('/login');
    }

    /**
     * @param $database
     */
    private function testDatabase($database)
    {
        $dbType = 'mysql'; // $database['default'];
        Config::set('database.default', $dbType);
        foreach ($database['connections'][$dbType] as $key => $val) {
            Config::set("database.connections.{$dbType}.{$key}", $val);
        }

        try {
            DB::reconnect();
            $valid = DB::connection()->getDatabaseName() ? true : false;
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $valid;
    }

    /**
     * @param $mail
     */
    private function testMail($mail)
    {
        $email = $mail['from']['address'];
        $fromName = $mail['from']['name'];

        foreach ($mail as $key => $val) {
            Config::set("mail.{$key}", $val);
        }

        Config::set('mail.from.address', $email);
        Config::set('mail.from.name', $fromName);

        $data = [
            'text'      => 'Test email',
            'fromEmail' => $email,
        ];

        try {

            $result = Mail::send('emails.blank', ['title' => 'Gratz!', 'body' => 'You configured successfully your mail settings'], function ($message) use ($email) {
                $message->to($email);
                $message->subject('E-Mail configuration test');
            });

            return 'Sent';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
