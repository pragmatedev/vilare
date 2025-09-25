<!DOCTYPE html>
<html
    {!! language_attributes() !!}
    x-data="vilare"
>
    <head>
        <meta charset="utf-8" />
        <meta
            http-equiv="x-ua-compatible"
            content="ie=edge"
        />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />
        {!! wp_head() !!}
    </head>

    <body {!! body_class() !!}>
        {!! do_action('wp_body_open') !!}
        {!! do_action('get_header') !!}

        @section('header')
        @show

        @yield('content')

        @section('footer')
        @show

        {!! do_action('get_footer') !!}
        {!! do_action('wp_footer') !!}
    </body>
</html>
