@extends('base.base')

@section('content')
<section>
    <h1>{{ $title }}</h1>
</section>
<hr>
<section>
    <img class="baner" loading="lazy" src="/storage/{{ $image }}" alt="{{ $title_page }} ">
</section>
<hr>
<section>
    {!! $content !!}
</section>


@endsection