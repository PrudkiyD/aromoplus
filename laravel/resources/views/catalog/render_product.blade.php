<div class="product-card">
            
    <div class="product-card-img"><img loading="lazy" src="/storage/{{ $product->main_image }}" alt="{{ $product->name }}"></div>
    <div>
        <a href="/catalog/product/{{ $product->id }}"><h2>{{ $product->name }}</h2></a>
        @if($product->labels->isNotEmpty())
            <div class = "sale-label">
                @foreach($product->labels as $label)
                    <img src="/storage/{{$label->image}}" alt="{{ $product->name}}">
                @endforeach
            </div>
        @endif
        @php
            $finalPrice = $product->price;
        @endphp

        @foreach($product->discounts as $discount)
            @if($discount->quantity == 1)
                @php
                    $finalPrice = ceil($product->price * $discount->discount * 10) / 10;
                    if($discount->vat_included){
                        $finalPrice = ceil($finalPrice * 1.2* 10) / 10;
                    }
                @endphp
            @endif

        @endforeach
        <p class="price">Ціна: ₴{{ $finalPrice }}</p>
        <div> 
            <div>
                <label style="font-size: 13px; color: rgba(128, 128, 128, 0.567);">Кількість:</label>
                <input id="quantity" style="margin-bottom: 10px; text-align: center; width: 35px;" type="text" value="1">
                <span style="font-size: 13px; color: rgba(128, 128, 128, 0.567);">шт.</span>
            </div>            
            <button data-id="{{ $product->id }}" id="add-to-card" class="button-main add-to-card">
                Додати в кошик
            </button>
            

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
        </div>
        
    </div>
</div>