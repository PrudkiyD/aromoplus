@extends('base.base')
@section('content')


<section><h1>{{ $title }}</h1></section>
<hr>
<section>
    {!! $content !!}
</section>
@endsection