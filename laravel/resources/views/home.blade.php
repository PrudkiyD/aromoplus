@extends('base/base')

@section('canonical')
https://aromoplus.com.ua/
@endsection

@section('title_page')
Запчастини до кавомашин | Aromoplus
@endsection

@section('content')
<section><h1>{{ $title }}</h1></section>
<hr>
<script src="/storage/js/jquery.js"></script>
<script src="/storage/js/slick.min.js"></script>
<script src="/storage/js/script.js"></script>
<section class="slider">
    @foreach($slider as $img)
        <div class="slide">
            <a href="{{ $img->url }}">
                <img class="baner" loading="lazy" src="/storage/{{ $img->image }}" alt="{{ $img->name }}">
            </a>
        </div>
    @endforeach
    
</section>
<hr>
<section>
    <h2>Товари та послуги</h2>
    <div class="categories-grid">
        @foreach ($categoryTree ?? [] as $category)
            <div class="category">
                @if($category->label)
                <img src="/storage/{{ $category->label }}" alt="{{ $category->name }}">
                @endif
                <a href="/catalog/{{ $category->slug }}">{{ $category->name }}</a>
            </div>
        @endforeach

        @foreach ($services ?? [] as $service)
            <div class="category">
                @if($service->label)
                    <img src="/storage/{{ $service->label }}" alt="{{ $service->name }}">
                @endif
                <a href="/services/{{ $service->slug }}">{{ $service->name }}</a>
            </div>
        @endforeach
    </div>
</section>
<hr>
<section>
    <div class="saleMain">   
        <h2>Товари зі знижкою</h2>
        <a href="/catalog/sale/">Переглянути всі товари</a>
    </div>
   
    <div class="list-products">
        @foreach ($sale_products as $product)
            @include('catalog.render_product')
        @endforeach
    </div>
</section>
<section>
    <div class="saleMain">   
        <h2>Рекомендовані товари</h2>
        <a href="/catalog/rekomendovani/">Переглянути всі товари</a>
    </div>
    
    <div class="list-products">
        @foreach ($recomend_products as $product)
            @include('catalog.render_product')
        @endforeach
    </div>
</section>
<section>
    <div class="saleMain">   
        <h2>Популярні товари</h2>
        <a href="/catalog/populyarni/">Переглянути всі товари</a>
    </div>

    <div class="list-products">
        @foreach ($populyarni_products as $product)
            @include('catalog.render_product')
        @endforeach
    </div>
</section>
<hr>
<section >
    {!! $about_us->content !!}
</section>

@endsection