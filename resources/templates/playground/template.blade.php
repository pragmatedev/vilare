@extends('base')

@section('header')
    
@endsection

@section('content')
    <main
        class="template-playground -wrapper"
        x-data="template_playground"
    >
        {{ the_content() }}
    </main>
@endsection

@section('footer')
    
@endsection
