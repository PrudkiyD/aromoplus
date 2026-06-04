@extends('base.base')

@php
    $finalPrice = $product->price; // дефолтна ціна
@endphp

@foreach($product->discounts as $discount)
    @if($discount->quantity == 1)
        @php
            $finalPrice = ceil($product->price * $discount->discount * 10) / 10;
            if($discount->vat_included){
                $finalPrice = ceil($finalPrice * 1.2 * 10) / 10;
            }
                
        @endphp
    @endif

    

@endforeach

@section('canonical')https://aromoplus.com.ua/product/{{ $product->id }}@endsection
@section('title_page'){{ $product->name}}@endsection
@section('img_page'){{ $product->main_image }}@endsection
@section('headdescription')
{{ $product->name }} Ціна: {{ number_format($finalPrice, 2, '.', '') }} грн.@if($product->availability == 'in_stock') В наявності.@endifДоставка по Україні.
@endsection
@section('ogtype')
product
@endsection


@section('content')

<section>
    
    <h1>{{ $product->name }}</h1>
</section>
<hr>
<section class="product-section">
    
        
    <div class="img-product">
        <div class="zoom-img-bloc">
            
            <div class="zoom-img">
                <div class="zoom-img-close">Закрити</div>
                <img >
            </div>
            
        </div>

        @if($product->labels->isNotEmpty())
            <div class = "sale-label">
                @foreach($product->labels as $label)
                    <img src="/storage/{{$label->image}}" alt="{{ $product->name}}">
                @endforeach
            </div>
        @endif
        
        <img class="main-img" src="/storage/{{ $product->main_image }}" alt="{{ $product->name}}">
        <div style="display: flex; gap: 10px;">
            @if($product->images->isNotEmpty())
                <img class="img-slider-item" style="width: 80px;" src="/storage/{{ $product->main_image }}" alt="{{ $product->name}}">
            @endif
            @foreach($product->images as $image)
                <img class="img-slider-item" style="width: 80px;" src="/storage/{{ $image->image }}" alt="{{ $product->name}}">
            @endforeach
        </div>
    </div>
        
    
    <div class="info-product">
        <p class="cat">
            <a style="color: black;" href="{{ url()->previous() }}">Повернутися назад</a>
        </p>
        <h3>Ціна: {{ $finalPrice}} грн.</h2>
        <h2>Артикул: <strong>{{ $product->manufacturer_sku }}</strong></h2>
        <ul>
            @foreach($product->discounts as $discount)
                @if($discount->quantity != 1)
                    <li style="text-decoration: none; margin-bottom: 5px;">
                        <span 
                        style="color: rgb(58, 58, 58); font-size: 14px; font-weight: 200;">
                            @php
                                $finalPrice = ceil($product->price * $discount->discount * 10) / 10;
                                if($discount->vat_included){
                                    $finalPrice = ceil($finalPrice * 1.2 * 10) / 10;
                                }
                            @endphp
                            Ціна: {{ $finalPrice}} від {{ $discount->quantity }} шт.
                        </span>
                    </li>
                @endif
            @endforeach
        </ul>
        <div style="display: flex; gap: 10px; align-items: center;">
                        <label style="font-size: 13px; color: rgba(128, 128, 128, 0.567);" for="">Кількість:</label>
                        <input id="quantity" style="text-align: center; width: 35px;" type="text" value="1">
                        <span style="font-size: 13px; color: rgba(128, 128, 128, 0.567);">шт.</span>
                        <button 
                            data-id="{{ $product->id }}"
                            class="button-main add-to-basket add-to-card" 
                            id="add-to-card" 
                            type="button">
                            <img src="/storage/img/shopping-cart-svgrepo-com.svg" alt="">
                            <p id="add-to-card" data-id="{{ $product->id }}">Додати в кошик</p>

                        </button>
                </div>

        @if($product->availability == 'in_stock')
                <div style="margin-top: 10px; font-size:13px; color: rgba(19, 198, 6, 1);">
                    В наявності
                </div>
            @elseif($product->availability == 'on_order')
                <div style="margin-top: 10px; font-size:13px; color: rgba(122, 122, 122, 1);">
                    Під замовлення
                </div>
                <span style="margin-top: 10px; font-size:13px; color: rgba(122, 122, 122, 1);">
                    Ціна указана орієнтовно
                </span>
            @elseif($product->availability == 'out_of_stock')
                <div style="margin-top: 10px; font-size:13px; color: rgba(204, 0, 0, 1);">
                    Немає в наявності
                </div>
                <span style="margin-top: 10px; font-size:13px; color: rgba(122, 122, 122, 1);">
                    Ціна указана орієнтовно
                </span>
            @endif
        
        <hr>
        <div>
            <p class='info-del'>
                Безкоштовна доставка при замовленні від 1000 грн. <br><br>
                Графік роботи: <br>
                <br>
                Пн. - Нд. <br>
                Відділ продаж +380 (95) 840-59-04 <br>
                Відділ оренди +380 (66) 850-09-10 <br>
            </p>
            <p class='info-del'>
                Повернення товару протягом 14 днів <br> 
                за домовленістю.
            </p>
        </div>
        

    </div>
</section>

<section class="des">
    <h4>Опис</h4>
    {!! $product->description !!}
</section>
@if($recentProducts->isNotEmpty())
    <section>
    
        @foreach($recentProducts as $product)
            <p>{{ $product->name }}</p>
        @endforeach

    </section>
@endif
<script src="/storage/js/product.js"></script>
@endsection