@extends('base.base')

@section('content')
<link rel="stylesheet" href="/storage/css/startorder.css">

<section>
    <h1>{{ $title }}</h1>
</section>
<hr>
<section style="display: flex; flex-wrap: wrap; gap: 10px;">
    <form class="chekoutform" method="post" action="/order/save">
        <h3>Контактна інформація</h3>
        @csrf
        <input class="input-main" type="text" name="last_name" id="last_name" placeholder="Прізвище" required value="">
        <input class="input-main" type="text" name="first_name" id="first_name" placeholder="Ім'я" required value="">
        <input class="input-main" type="text" name="middle_name" id="middle_name" placeholder="По батькові" required value="">
        <input class="input-main" type="text" name="organization" id="organization" placeholder="Організація" value="">
        <input class="input-main phone" type="text" name="phone_number" id="phone_number" placeholder="Номер телефону" required value="">

        <hr>
        <h3>Доставка</h3>
        <select class="select-main delivery" name="delivery" id="delivery">
            <option value="nova">Нова пошта</option>
            <option value="cat">Адресна доставка до дверей по Україні</option>
        </select>

        <input class="input-main city" type="text" name="city" id="street" placeholder="Місто" value="">

        <input class="input-main department" type="text" name="department" id="department" placeholder="Відділення" value="">
        
        <input class="input-main street" type="text" name="street" id="street" placeholder="Вулиця" value="">

        <input class="input-main addresses" type="text" name="addresses" id="addresses" placeholder="Номер будинку" value="">

        <select class="select-main" name="payment_type" id="payment_type">
            <option value="nal">Оплата при отриманні</option>
            <option value="fop">Реквізити ФОП</option>
            <option value="tov">Рахунок ТОВ</option>
        </select>

        <hr>
        <h3>Коментар</h3>
        <textarea class="input-main" name="comment" id="comment" cols="30" rows="5" placeholder="Коментар..."></textarea>

        <button type="submit" class="button-main chekout">Оформити замовлення</button>
    </form>

    <div class="basketBlock">
        <div class="basketTitle">
            <h3>Товари в замовленні</h3>
            <p id="closeBasket"></p>
        </div>
        <ul class="basket-list"></ul>
        <div class="totalBloc">
            <div style="font-weight: 600; font-size: 16px;">Загальна сума:</div>
            <div style="font-weight: 600; font-size: 16px;" class="totalBasket"></div>
        </div>
    </div>
</section>

<script src="/storage/js/startorder.js"></script>
@endsection
