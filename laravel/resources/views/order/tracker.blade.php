@extends('base.base')
<?php

$PAY_CHOICES = array(
    "nal" => "Післяплата",
    "fop" => "Реквізити ФОП",
    "tov" => "Реквізити ТОВ",
    "kasa" => "Каса"
);

$DELIVERY_CHOICES = array(
    "nova" => "Нова Пошта",
    "ukr" => "Укрпошта",
    "cat" => "Адресна доставка до дверей по Україні",
    "sum" => "Самовивіз"
);

$STATUS_CHOICES = array(
    "new" => "Нове",
    "processing" => "Прийнято",
    "ready" => "Готово до відправки",
    "shipped" => "Відправлено",
    "successful" => "Виконано",
    "canceled" => "Скасовано"
);

?>
@section('content')

<section>
    <h1>{{ $title }}</h1>    
</section>

<section>
    <ul>
        <li>Номер замовлення: <strong>{{ $order->number }}</strong></li>
        <li>Статус: <strong>{{ $STATUS_CHOICES[$order->status] }}</strong></li>
        <li>Прізвище: <strong>{{ $order->last_name }}</strong></li>
        <li>Ім'я: <strong>{{ $order->first_name }}</strong></li>
        <li>По-батькові: <strong>{{ $order->middle_name }}</strong></li>
        <li>Організація: <strong>{{ $order->organization }}</strong></li>
        <li>Номер телефону: <strong>{{ $order->phone_number}}</strong></li>
        <li>Тип оплати: <strong>{{ $PAY_CHOICES[$order->payment_type] }}</strong></li>
        <li>Доставка: <strong>{{ $DELIVERY_CHOICES[$order->delivery] }}</strong></li>
        <li>Адреса: <strong>{{ $order->city}}</strong></li>
        <hr>
        <li class="prodItems" style="list-style: none;">
                @foreach ($order->productItems as $orderProduct)
                    <div class="prodItem">
                        <div><img src="/storage/{{ $orderProduct->product->main_image}}" alt=""></div>
                        <div class="prod-info">
                            <strong><a href="/catalog/product/{{ $orderProduct->product->id}}">{{ $orderProduct->Product->name}}</a></strong>
                            <div>
                                <div>Кількість: {{ $orderProduct->quantity}} шт.</div>
                                <div>Ціна: {{ $orderProduct->price}} грн.</div>
                            </div>
                            
                        </div>
                    </div>
                    <hr>
                @endforeach
            </li>
            <li style="text-align: end; list-style: none;">Загальна сума: <strong>{{ $order->total }} грн.</strong></li>
    </ul>
</section>

@endsection