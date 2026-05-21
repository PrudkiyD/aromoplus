@extends('base.base')

@section('content')
<style>
    table {
        border-collapse: separate; /* щоб була можливість робити закруглені кути */
        border-spacing: 0;
        width: 100%;
        margin: 20px auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 3px;
        overflow: hidden;
        background-color: #ffffff;
    }

    th, td {
        padding: 12px 20px;
        text-align: left;
        cursor: pointer;
    }

    th {
        background-color: #bc854aff;
        color: white;
        font-weight: 600;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #e0f7fa;
        transition: background-color 0.3s;
    }

    td {
        color: #333;
    }
</style>

<section style="display: flex; justify-content: space-between;">
    <h1>{{ $title }}</h1>
    <button style="color: #333;
        display: block;
        background-color: rgba(27, 0, 0, 0);
        border: none;
        margin-block-start: 0.67em;
        margin-block-end: 0.67em;
        margin-inline-start: 0px;
        margin-inline-end: 0px;
        font-weight: bold;
        unicode-bidi: isolate;" >
        <a style="color: #333;" href='/account/logout/'>Вийти</a>
        
    </button>
</section>
<hr>
<section>
    <h3>Контактна інформація</h3>
    <ul>
        <li><strong>ПІБ: {{$user->name}}</strong><span></span></li>
        <li><strong>Номер телефону: {{$user->phone}}</strong><span></span></li>
        <li><strong>Елетронна пошта: {{$user->email}}</strong><span></span></li>
    </ul>
    <!---
    <h3>Інформація для доставки</h3>
    <ul>
        <li><strong>ПІБ: </strong><span></span></li>
        <li><strong>Номер телефону: </strong><span></span></li>
        <li><strong>Адреса доставки: </strong><span></span></li>
    </ul>
    --->
</section>
<hr>
<section>
    <h3>Замовлення</h3>
    <table>
        <tr>
            <th>Замовлення №</th>
            <th>ПІБ</th>
            <th>ТТН</th>
            <th>Загальна сума</th>
            <th>Дата оформлення</th>
        </tr>
        @foreach($orders as $order)
            
            <tr>
                <td> 
                    <a style="color:black;" href="/order/tracker/{{ $order->key}}">{{ $order->number}}</a>
                </td>
                <td>{{ $order->first_name}} {{ $order->last_name }}</td>
                <td>{{ $order->ttn}}</td>
                <td>{{ $order->total}}</td>
                <td>{{ $order->created_at}}</td>
            </tr>
        @endforeach
    </table>
</section>

@endsection