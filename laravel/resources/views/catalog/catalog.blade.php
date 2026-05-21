@extends('base.base')

@section('canonical')
{{ url()->current() }}
@endsection

@section('content')
<section><h1>{{ $title }}</h1></section>
<hr>
<section>
    @if($subcategorys->isNotEmpty())
        <h3>Виробники</h3>
        <div class="categories-grid">
        @foreach($subcategorys as $subcategory)
            <div class="category">
                <a href="/catalog/{{ $subcategory->slug }}">
                    <img src="/storage/{{ $subcategory->image }}" alt="{{ $subcategory->name }}">
                </a>
                <a href="/catalog/{{ $subcategory->slug }}">{{ $subcategory->name }}</a>
            </div>
            
        @endforeach
        </div>
    @endif
</section>
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