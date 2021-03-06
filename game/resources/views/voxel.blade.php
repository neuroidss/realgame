<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="shortcut icon" href="favicon.png">
        <script src="https://code.jquery.com/pep/0.4.3/pep.js"></script>

        <title>Arcade City</title>

        <style>
            html, body {
                background-color: #000;
                color: #636b6f;
                font-family: monospace;
                font-weight: 200;
                /* height: 100vh; */
                margin: 0;
            }

            .lock-screen {
                height: 100%;
                overflow: hidden;
                width: 100%;
                position: fixed;
            }
        </style>
    </head>
    <body class="lock-screen">
        <div id="app">
            <!-- <inventory-bar></inventory-bar> -->
            <main-canvas></main-canvas>
            <!-- <token-widget></token-widget> -->
        </div>
        <script>window.auth_user = {!! json_encode($auth_user); !!};</script>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
