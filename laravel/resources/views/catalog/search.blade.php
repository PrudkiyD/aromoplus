@extends('base.base')

@section('canonical')
{{ url()->current() }}
@endsection

@section('content')
<section><h1>{{ $title }}</h1></section>
<hr>
@if($products->count())
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
@else
    <h4>Нічого не знайдено.</h4>
    <p>Скористайтеся формою зворотного зв'язку або звернітся до менеджера для уточнення інформації.</p>
    <div class="search-contact">
        {!! $kontakti->content !!}
    </div>
    <style>
        .search-contact ul{
            display:flex;
            gap:50px;
            flex-wrap: wrap;
        }


        .search-contact a {
            color:black;
            font-weight:600;
        }

        .search-contact .footerTitle{
            display:none;
        }
    </style>
    
@endif
@endsection