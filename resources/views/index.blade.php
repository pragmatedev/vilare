@extends('base')

@section('content')
    <main class="-wrapper">
        @if (have_posts())
            @while (have_posts())
                {{ the_post() }}
                <div>
                    <a href="{{ the_permalink() }}">
                        {{ the_title() }}
                    </a>
                </div>
            @endwhile
        @endif
    </main>
@endsection
