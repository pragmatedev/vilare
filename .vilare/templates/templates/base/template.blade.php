@extends('index')

@section('content')
    <main
        class="template-base -wrapper"
        x-data="template_base"
    >
        {!! the_content() !!}
    </main>
@endsection
