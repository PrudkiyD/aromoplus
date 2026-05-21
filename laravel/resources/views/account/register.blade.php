<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Реєстрація | Aromoplus</title>
  <link rel="stylesheet" href="/storage/css/login.css">
</head>
<body>
  <main class="card" aria-labelledby="registerTitle">
    <h1 id="registerTitle">Реєстрація</h1>

    <form style="width: 100%;" method="POST" action="/account/register">
      @csrf
      <div style="margin-bottom:12px; width: 100%;">
        <label for="name">Ім'я</label>
        <input style="width: 320px;" id="name" name="name" type="text" placeholder="Ім'я" value="{{ old('name') }}" required>
      </div>

      <div style="margin-bottom:12px">
        <label for="email">Електронна пошта</label>
        <input style="width: 320px;" id="email" name="email" type="email" placeholder="Електронна пошта" value="" required>
      </div>

      <div style="margin-bottom:12px">
        <label for="phone">Номер телефону</label>
        <input class="phone" style="width: 320px;" id="phone" name="phone" type="phone" placeholder="Номер телефону" value="" required>
      </div>

      <div style="margin-bottom:12px">
        <label for="password">Пароль</label>
        <input style="width: 320px;" id="password" name="password" type="password" placeholder="Пароль" required>
      </div>

      <div style="margin-top:14px">
        <button type="submit">Зареєструватися</button>
      </div>
      <div style="margin-top:14px; font-size:0.8em;">
        <a href="/account/login">
          Увійти
        </a>
      </div>
        @error('error')
            <div class="error" style="color:red; font-size:0.8em; margin-top: 20px; ">{{ $message }}</div>
        @enderror
    </form>
  </main>
  <script>
    // Формат номера телефону
    const phoneBasket = document.querySelector('.phone')

    phoneBasket.addEventListener('change', function(){
        let phone = phoneBasket
        let phone_number;
        let number = phone.value.replace(/\D/g, '').substr(-10)


        if (number[0] === '0'){
            phone_number = '+38' + number
        }
        
        else{
            phone_number = '+380' + number
        }

        console.log(phone_number.length)

        phone.value = `${phone_number.substr(0, 4)} ${phone_number.substr(4, 2)} ${phone_number.substr(6, 3)} ${phone_number.substr(9, 2)} ${phone_number.substr(11, 2)}`
    })

  </script>
</body>
</html>
