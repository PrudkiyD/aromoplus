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
      <div id="result" class="ms-block" style="font-size: 13px; margin-bottom: 10px; color: rgb(137, 137, 137);">
        На цю електронну пошту буде відправлено код відновлення пароля
      </div>

      <div style="margin-bottom:12px" class="email-block">
        <label for="email">Електронна пошта</label>
        <input style="width: 83%;" id="email" name="email" type="text" inputmode="email" placeholder="Електронна пошта"
               autocomplete="email" required autofocus>
      </div>

      

      <div style="margin-bottom:12px" class="code-block">
        <label for="code">Код відновлення</label>
        <input style="width: 83%;" id="code" name="code" type="text" inputmode="code" placeholder="Код відновлення" required>
      </div>

      <div style="margin-bottom:8px" class="password-block">
        <label for="password">Новий пароль</label>
        <div class="row">
          <input id="password" name="password" type="password" placeholder="Пароль" autocomplete="current-password" required>
          <button type="button" class="show-pass" aria-label="Показати пароль" onclick="togglePassword()">👁️</button>
        </div>
      </div>

      <div style="margin-top:14px" class="send-code-block">
        <button type="button" onclick="createCode()">Відправити код</button>
      </div>

      <div style="margin-top:14px" class="next-block">
        <button type="button" onclick="checkCode()">Далі</button>
      </div>

      <div style="margin-top:14px" onclick="changePassword()" class="change-password-block">
        <button type="button">Змінити пароль</button>
      </div>
      
      <div style="margin-top:14px" class="login-block">
        <button type="submit">Увійти</button>
      </div>

    </form>

  </main>

  <script src="/storage/js/login.js"></script>

  <script>
    document.querySelector('.code-block').style.display = 'none'
    document.querySelector('.password-block').style.display = 'none'
    document.querySelector('.next-block').style.display = 'none'
    document.querySelector('.login-block').style.display = 'none'
    document.querySelector('.change-password-block').style.display = 'none'
    
    
  </script>

<script>
  function createCode() {
      const email = document.getElementById('email').value.trim();

      if (!email) {
          document.getElementById('result').innerText = "Введіть email!";
          return;
      }

      fetch(`/account/create-code?email=${encodeURIComponent(email)}`)
          .then(response => response.json())
          .then(data => {
              if (data.status === "success") {
                  document.getElementById('result').innerText = data.message
                    
                    document.querySelector('.send-code-block').style.display = 'none'
                    document.querySelector('.code-block').style.display = 'block'
                    document.querySelector('.next-block').style.display = 'block'
              } else {
                  document.getElementById('result').innerText = data.message;
              }
          })
          .catch(error => {
              console.log(error)
              document.getElementById('result').innerText = "Помилка: " + error;
          });
  }
</script>

<script>
  function checkCode() {
      const email = document.getElementById('email').value.trim();
      const code = document.getElementById('code').value;

      if (!email) {
          document.getElementById('result').innerText = "Введіть код!";
          return;
      }

      fetch(`/account/check-code?email=${encodeURIComponent(email)}&code=${code}`)
          .then(response => response.json())
          .then(data => {
              if (data.status === "success") {
                  document.getElementById('result').innerText = data.message
                    document.querySelector('.next-block').style.display = 'none'
                    document.querySelector('.password-block').style.display = 'block'
                    document.querySelector('.change-password-block').style.display = 'block'
              } else {
                  document.getElementById('result').innerText = data.message;
              }
          })
          .catch(error => {
              console.log(error)
              document.getElementById('result').innerText = "Помилка: " + error;
          });
  }
</script>

<script>
  function changePassword() {
      const email = document.getElementById('email').value.trim();
      const code = document.getElementById('code').value;
      const password = document.getElementById('password').value;

      if (!email) {
          document.getElementById('result').innerText = "Введіть код!";
          return;
      } 


      fetch(`/account/change-password?email=${encodeURIComponent(email)}&code=${code}&password=${password}`)
          .then(response => response.json())
          .then(data => {
              if (data.status === "success") {
                  document.getElementById('result').innerText = data.message
                    document.querySelector('.change-password-block').style.display = 'none'
                    
                    document.querySelector('.next-block').style.display = 'none'
                    document.querySelector('.code-block').style.display = 'none'
                    document.querySelector('.login-block').style.display = 'block'
                    
                    document.getElementById('password').value = password
                    document.getElementById('email').value = email
              } else {
                  document.getElementById('result').innerText = data.message;
              }
          })
          .catch(error => {
              console.log(error)
              document.getElementById('result').innerText = error;
          });
  }
</script>

</body>
</html>
