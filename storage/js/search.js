/*
async function searchProducts() {
    // Отримуємо значення з інпута
    var searchQuery = document.getElementById('searchSpace').value;

    // Формуємо URL для GET-запиту
    var url = 'https://aromoplus.com.ua/search/?searchlist=true&product=' + encodeURIComponent(searchQuery);

    try {
        // Виконуємо GET-запит за допомогою fetch і очікуємо відповідь
        const response = await fetch(url);
        
        // Перетворюємо отриману відповідь у форматі JSON
        const data = await response.json();

        // Отримали дані (data)
        var resSearch = document.getElementById('resSearch');
        // Очищаємо список перед вставкою нових результатів
        resSearch.innerHTML = '';
        // Додаємо кожен результат як новий елемент списку
        data.products.forEach(product => {
            let html = `<li class="res-item">
                            <a href="/detali/${product.id}">
                            <div class="img-item"><img src="/public/${product.img}"></div>
                            <div>
                                <div><strong>${product.name}</strong></div>
                                <div>Ціна: <strong>${product.price}</strong> грн.</div>
                            </div>
                        </li>`
            resSearch.innerHTML += html;
        });
    } catch (error) {
        console.error('Помилка:', error);
    }
}

document.getElementById('searchSpace').addEventListener('keyup', function(){
    setTimeout(searchProducts, 1000)
});
*/