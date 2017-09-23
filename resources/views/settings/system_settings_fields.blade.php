  <p class="title is-5">{{ trans('texts.application_settings') }}</p>

  {!! Former::text('app[url]')->label(trans('texts.url'))->value(isset($_ENV['APP_URL']) ? $_ENV['APP_URL'] : Request::root()) !!}
  <div class="columns">
    <div class="column is-6">
      {!! Former::checkbox('https')->label('')->text( ' ' . trans('texts.require_https'))->check(env('REQUIRE_HTTPS'))->value(1) !!}
    </div>
    <div class="column is-6">
      {!! Former::checkbox('debug')->label('')->text( ' ' . trans('texts.enable_debug'))->check(config('app.debug'))->value(1) !!}
    </div>
  </div>

  <hr>

  <p class="title is-5">{{ trans('texts.database_connection') }}</p>


  {{--- Former::select('database[default]')->label('driver')->options(['mysql' => 'MySQL', 'pgsql' => 'PostgreSQL', 'sqlite' => 'SQLite'])
          ->value(isset($_ENV['DB_TYPE']) ? $_ENV['DB_TYPE'] : 'mysql') ---}}
  {!! Former::plaintext('Driver')->value('MySQL') !!}
  <div class="columns">
    <div class="column is-6">
      {!! Former::text('database[type][host]')->label('host')->value(isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : '127.0.0.1') !!}
      {!! Former::text('database[type][username]')->label('username')->value(isset($_ENV['DB_USERNAME']) ? $_ENV['DB_USERNAME'] : 'homestead') !!}
    </div>
    <div class="column is-6">
      {!! Former::text('database[type][database]')->label('database')->value(isset($_ENV['DB_DATABASE']) ? $_ENV['DB_DATABASE'] : 'handesk') !!}
      {!! Former::password('database[type][password]')->label('password')->value(isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : 'secret') !!}
    </div>
  </div>




  <div class="has-text-right">
    {!! Former::actions(
      '<a class="button is-primary text-right" onclick="testDatabase()">'.trans('texts.test_connection').'</a>',
      '&nbsp;&nbsp;<p class="help" id="dbTestResult"/>' ) !!}
  </div>

  @if (!isset($_ENV['POSTMARK_API_TOKEN']))

  <hr>

  <p class="title is-4">{{ trans('texts.email_settings') }}</p>

  <div class="columns">
    <div class="column is-6">
      {!! Former::select('mail[driver]')->options(['smtp' => 'SMTP', 'mail' => 'Mail', 'sendmail' => 'Sendmail', 'mailgun' => 'Mailgun'])
             ->value(isset($_ENV['MAIL_DRIVER']) ? $_ENV['MAIL_DRIVER'] : 'smtp')
             ->setAttributes(['onchange' => 'mailDriverChange()'])!!}
      {!! Former::text('mail[from][name]')->label('from_name')
            ->value(isset($_ENV['MAIL_FROM_NAME']) ? $_ENV['MAIL_FROM_NAME'] : '')  !!}



    </div>
    <div class="column is-6">
      {!! Former::select('mail[encryption]')->label('encryption')
              ->options(['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'])
              ->value(isset($_ENV['MAIL_ENCRYPTION']) ? $_ENV['MAIL_ENCRYPTION'] : 'tls')
      !!}
      {!! Former::text('mail[from][address]')->label('from_address')
              ->value(isset($_ENV['MAIL_FROM_ADDRESS']) ? $_ENV['MAIL_FROM_ADDRESS'] : '')  !!}





    </div>
  </div>

  {!! Former::text('mail[username]')->label('username')
         ->value(isset($_ENV['MAIL_USERNAME']) ? $_ENV['MAIL_USERNAME'] : '')  !!}

  <div id="standardMailSetup">
    <div class="columns">
      <div class="column is-6">
        {!! Former::text('mail[host]')->label('host')
                ->value(isset($_ENV['MAIL_HOST']) ? $_ENV['MAIL_HOST'] : '') !!}
      </div>
      <div class="column is-6">
        {!! Former::text('mail[port]')->label('port')
                ->value(isset($_ENV['MAIL_PORT']) ? $_ENV['MAIL_PORT'] : '587')  !!}
      </div>
    </div>

  {!! Former::password('mail[password]')->label('password')
          ->value(isset($_ENV['MAIL_PASSWORD']) ? $_ENV['MAIL_PASSWORD'] : '')  !!}
  </div>

  <div id="mailgunMailSetup">
    <div class="columns">
      <div class="column is-6">
        {!! Former::text('mail[mailgun_domain]')->label('mailgun_domain')
                ->value(isset($_ENV['MAILGUN_DOMAIN']) ? $_ENV['MAILGUN_DOMAIN'] : '') !!}
      </div>
      <div class="column is-6">
        {!! Former::text('mail[mailgun_secret]')->label('mailgun_private_key')
                ->value(isset($_ENV['MAILGUN_SECRET']) ? $_ENV['MAILGUN_SECRET'] : '')  !!}
      </div>
    </div>
  </div>

  <div class="has-text-right">
    {!! Former::actions(
      '<a class="button is-primary" onclick="testMail()">'.trans('texts.send_test_email').'</a>',
      '&nbsp;&nbsp;<span id="mailTestResult"/>' ) !!}
  </div>

@endif





@push('scripts')
  <script type="text/javascript">

    var db_valid = false
    var mail_valid = false
    mailDriverChange();

    function testDatabase()
    {
      var data = $("form").serialize() + "&test=db";

      // Show Progress Text
      $('#dbTestResult').html('Working...').css('color', 'black');

      // Send / Test Information
      $.post( "{{ URL::to('/setup') }}", data, function( data ) {
        var color = 'red';
        if(data == 'Success'){
          color = 'green';
          db_valid = true;
        }
        $('#dbTestResult').html(data).css('color', color);
      });

      return db_valid;
    }

    function mailDriverChange() {
      if ($('select[name="mail[driver]"]').val() == 'mailgun') {
        $("#standardMailSetup").hide();
        $("#standardMailSetup").children('select,input').prop('disabled',true);
        $("#mailgunMailSetup").show();
        $("#mailgunMailSetup").children('select,input').prop('disabled',false);

      } else {
        $("#standardMailSetup").show();
        $("#standardMailSetup").children('select,input').prop('disabled',false);

        $("#mailgunMailSetup").hide();
        $("#mailgunMailSetup").children('select,input').prop('disabled',true);

      }
    }

    function testMail()
    {
      var data = $("form").serialize() + "&test=mail";

      // Show Progress Text
      $('#mailTestResult').html('Working...').css('color', 'black');

      // Send / Test Information
      $.post( "{{ URL::to('/setup') }}", data, function( data ) {
        var color = 'red';
        if(data == 'Sent'){
          color = 'green';
          mail_valid = true;
        }
        $('#mailTestResult').html(data).css('color', color);
      });

      return mail_valid;
    }

    // Prevent the Enter Button from working
    $("form").bind("keypress", function (e) {
      if (e.keyCode == 13) {
        return false;
      }
    });

  </script>
@endpush
