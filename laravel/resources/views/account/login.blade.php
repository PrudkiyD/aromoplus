<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Вхід | Aromoplus</title>
  <link rel="stylesheet" href="/storage/css/login.css">
</head>
<body>
  <main class="card" aria-labelledby="loginTitle">
    <h1 id="loginTitle">Увійти</h1>

    <form id="loginForm" autocomplete="on" method="post" action="/account/login/checkout">
      @csrf
      <div style="margin-bottom:12px">
        <label for="email">Електронна пошта</label>
        <input style="width: 83%;" id="email" name="email" type="text" inputmode="email" placeholder="Електронна пошта"
               autocomplete="email" required autofocus>
      </div>

      <div style="margin-bottom:8px">
        <label for="password">Пароль</label>
        <div class="row">
          <input id="password" name="password" type="password" placeholder="Пароль" autocomplete="current-password" required>
          <button type="button" class="show-pass" aria-label="Показати пароль" onclick="togglePassword()">👁️</button>
        </div>
      </div>

      <div class="actions">
        <label class="row small"><input type="checkbox" id="remember" name="remember"> Запам'ятати</label>
        <a class="small" href="/account/forgot">Забули пароль?</a>
      </div>
      
      <div style="margin-top:14px">
        <button type="submit">Увійти</button>
      </div>
      <div style="margin-top:14px; font-size:0.8em;">
        <a href="/account/register">
          Зареєструватися
        </a>
      </div>
    </form>

    @error('error')
        <div class="error" style="color:red; font-size:0.8em; margin-top: 20px; ">{{ $message }}</div>
    @enderror

  </main>

  <script src="/storage/js/login.js"></script>
</body>
</html>
