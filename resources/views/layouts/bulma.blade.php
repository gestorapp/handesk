<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Handesk') }}</title>
        <link href="{{ asset('css/bulma.css') }}" rel="stylesheet">
        @stack('stylesheets')
    </head>
    <body>
        <div id="app">
            @yield('content')
        </div>

        <script src="{{ asset('js/webPacked.js') }}"></script>
        @stack('scripts')
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                @stack('onReady')
            });
        </script>
    </body>
</html>
