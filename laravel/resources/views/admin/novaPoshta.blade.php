<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Нова Пошта — Форма відправлення</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="/storage/css/admin/novaPoshta.css">
</head>
<body>

<div class="np-wrap">
  <h1>Створити експрес-накладну</h1>
  <!-- Відправник -->
  <div class="np-section">
    <div class="np-section-title">
      <i class="ti ti-map-pin" aria-hidden="true"></i> Відправник
    </div>
    <div class="np-grid np-grid-2">
      <div class="np-field">
        <label class="np-label" for="CitySender">Місто відправника</label>
        <input type="text" name="CitySender" id="CitySender" placeholder="Ref міста">
      </div>
      <div class="np-field">
        <label class="np-label" for="Sender">Контрагент</label>
        <input type="text" name="Sender" id="Sender" placeholder="Ref відправника">
      </div>
      <div class="np-field">
        <label class="np-label" for="SenderAddress">Адреса / відділення</label>
        <input type="text" name="SenderAddress" id="SenderAddress" placeholder="Ref адреси">
      </div>
      <div class="np-field">
        <label class="np-label" for="ContactSender">Контактна особа</label>
        <input type="text" name="ContactSender" id="ContactSender" placeholder="Ref контакту">
      </div>
      <div class="np-field">
        <label class="np-label" for="SendersPhone">Телефон відправника</label>
        <input type="text" name="SendersPhone" id="SendersPhone" placeholder="380XXXXXXXXX">
      </div>
      <div class="np-field">
        <label class="np-label" for="DateTime">Дата відправки</label>
        <input type="date" name="DateTime" id="DateTime">
      </div>
    </div>
  </div>

  <br/>

  <!-- Вантаж -->
  <div class="np-section">
    <div class="np-section-title">
      <i class="ti ti-package" aria-hidden="true"></i> Вантаж
    </div>
    <div class="np-grid np-grid-2">
      <div class="np-field">
        <label class="np-label" for="CargoType">Тип вантажу</label>
        <select name="CargoType" id="CargoType">
          <option value="Cargo">Вантаж</option>
          <option value="Parcel">Посилка</option>
          <option value="Documents">Документи</option>
        </select>
      </div>
      <div class="np-field">
        <label class="np-label" for="Description">Опис</label>
        <input type="text" name="Description" id="Description" value="Запчастини">
      </div>
      <div class="np-field">
        <label class="np-label" for="Cost">Оголошена вартість, грн</label>
        <input type="text" name="Cost" id="Cost" placeholder="500">
      </div>
      <div class="np-field">
        <label class="np-label" for="AfterpaymentOnGoodsCost">Накладений платіж, грн</label>
        <input type="text" name="AfterpaymentOnGoodsCost" id="AfterpaymentOnGoodsCost" placeholder="0">
      </div>
    </div>
  </div>

  <br/>

  <!-- Параметри відправлення -->
  <div class="np-section">
    <div class="np-section-title">
      <i class="ti ti-ruler" aria-hidden="true"></i> Параметри відправлення
    </div>
    <div class="np-grid np-grid-2" style="margin-bottom: 10px;">
      <div class="np-field">
        <label class="np-label" for="ServiceType">Тип доставки</label>
        <select name="ServiceType" id="ServiceType">
          <option value="WarehouseWarehouse">Відділення → Відділення</option>
          <option value="WarehouseDoors">Відділення → Адреса</option>
        </select>
      </div>
      <div class="np-field">
        <label class="np-label" for="SeatsAmount">Кількість місць</label>
        <input type="number" name="SeatsAmount" id="SeatsAmount" value="1" min="1">
      </div>
      <div class="np-field">
        <label class="np-label" for="Weight">Вага, кг</label>
        <input type="number" name="Weight" id="Weight" value="0.2" step="0.1">
      </div>
      <div class="np-field">
        <label class="np-label" for="volumetricVolume">Об'єм, м³</label>
        <input type="text" name="volumetricVolume" id="volumetricVolume" placeholder="0.2">
      </div>
    </div>
    <div class="np-dims-label">Габарити (см)</div>
    <div class="np-dims-grid">
      <div class="np-field">
        <label class="np-label" for="volumetricWidth">Ширина</label>
        <input type="text" name="volumetricWidth" id="volumetricWidth" placeholder="10">
      </div>
      <div class="np-field">
        <label class="np-label" for="volumetricLength">Довжина</label>
        <input type="text" name="volumetricLength" id="volumetricLength" placeholder="10">
      </div>
      <div class="np-field">
        <label class="np-label" for="volumetricHeight">Висота</label>
        <input type="text" name="volumetricHeight" id="volumetricHeight" placeholder="10">
      </div>
    </div>
  </div>

  <br/>

  <!-- Оплата -->
  <div class="np-section">
    <div class="np-section-title">
      <i class="ti ti-credit-card" aria-hidden="true"></i> Оплата
    </div>
    <div class="np-grid np-grid-2">
      <div class="np-field">
        <label class="np-label" for="PayerType">Платник</label>
        <select name="PayerType" id="PayerType">
          <option value="Sender">Відправник</option>
          <option value="Recipient">Отримувач</option>
          <option value="ThirdPerson">Третя особа</option>
        </select>
      </div>
      <div class="np-field">
        <label class="np-label" for="PaymentMethod">Форма оплати</label>
        <select name="PaymentMethod" id="PaymentMethod">
          <option value="NonCash">Безготівкова</option>
          <option value="Cash">Готівкова</option>
        </select>
      </div>
    </div>
  </div>

  <br/>

  <!-- Одержувач -->
  <div class="np-section">
    <div class="np-section-title">
      <i class="ti ti-map-pin" aria-hidden="true"></i> Одержувач
    </div>
    <div class="np-grid np-grid-2">
      <div class="np-field">
        <label class="np-label" for="CityRecipient">Місто одержувача</label>
        <input type="text" name="CityRecipient" id="CityRecipient" placeholder="Ref міста">
      </div>
      <div class="np-field">
        <label class="np-label" for="Recipient">Контрагент</label>
        <input type="text" name="Recipient" id="Recipient" placeholder="Ref одержувача">
      </div>
      <div class="np-field">
        <label class="np-label" for="RecipientAddress">Адреса / відділення</label>
        <input type="text" name="RecipientAddress" id="RecipientAddress" placeholder="Ref адреси">
      </div>
      <div class="np-field">
        <label class="np-label" for="ContactRecipient">Контактна особа</label>
        <input type="text" name="ContactRecipient" id="ContactRecipient" placeholder="Ref контакту">
      </div>
      <div class="np-field">
        <label class="np-label" for="RecipientsPhone">Телефон одержувача</label>
        <input type="text" name="RecipientsPhone" id="RecipientsPhone" placeholder="380XXXXXXXXX">
      </div>
    </div>
  </div>

  <button class="np-submit" type="button">
    <i class="ti ti-send" aria-hidden="true"></i> Сформувати експрес-накладну
  </button>
</div>

</body>
</html>