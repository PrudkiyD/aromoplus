<link rel="stylesheet" href="/storage/css/feedback.css">
<section id="feedback-form" class="section-container">
    <h3>Форма зворотного зв'язку </h3>
    <form action="/feedback" method="post">
        @csrf
        <input type="text" name="name" class="input-field" placeholder="Прізвище та ім'я" id="fedbackname" >
        <input type="tel" name="phone" class="feedback-phone input-field" placeholder="Номер телефону"  id="fedbackphone">
        <textarea name="message" class="input-field" placeholder="Коментар" id="fedbacktext" rows="4"></textarea>

        <button type="button" id="sendFormBtn" class="input-button sendFormBtn">Відправити</button>
    </form>
</section>

<script src="/storage/js/feedback.js"></script>