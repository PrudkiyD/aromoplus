@extends('base.base')

@section('canonical')
https://aromoplus.com.ua/sale/
@endsection

@section('content')
<section><h1>{{ $title }}</h1></section>
<hr>
<div>
    <h4>Список товарів</h4>
</div>
 @include('catalog.paginator')
<div class="list-products">
    @foreach ($products as $product)
        @include('catalog.render_product')
    @endforeach
</div>
 @include('catalog.paginator')
@endsection