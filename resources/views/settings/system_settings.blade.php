@extends('layouts.bulma')

@section('content')

    <section class="hero is-fullheight is-dark is-bold">
      <div class="hero-body">
        <div class="container">
          <div class="columns is-vcentered">
            <div class="column is-6 is-offset-3">
              <h1 class="title">
                Handesk Setup
              </h1>
              @if ($errors->any())
                    <div class="notification is-warning">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

              @if (version_compare(phpversion(), '7.0.0', '<'))
                  <div class="notification is-warning">Warning: The application requires PHP >= 7.0.0</div>
              @endif
              @if (!function_exists('proc_open'))
                  <div class="notification is-warning">Warning: <a href="http://php.net/manual/en/function.proc-open.php" target="_blank">proc_open</a> must be enabled.</div>
              @endif
              @if (!@fopen(base_path()."/.env", 'a'))
                  <div class="notification is-warning">Warning: Permission denied to write .env config file
                      <pre>sudo chown www-data:www-data /path/to/handesk/.env</pre>
                  </div>
              @endif

              <div class="box">
                  {!! Former::open()
                      ->addClass('warn-on-exit')
                      ->autocomplete('off')
                      ->rules([
                          'app[url]' => 'required',
                          //'database[default]' => 'required',
                          'database[type][host]' => 'required',
                          'database[type][database]' => 'required',
                          'database[type][username]' => 'required',
                          'database[type][password]' => 'required',
                      ]) !!}



                  @include('settings.system_settings_fields')

                  <hr>
                  <div class="has-text-centered">
                      {!! Former::actions()->medium_primary_submit(trans('texts.save')) !!}
                  </div>
                  {!! Former::close() !!}
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>

@stop

@section('onReady')
    $('#app\\[url\\]').focus();
@stop
