// Зміни в кошику
window.addEventListener('click', function(element){
    if(element.target.id === "plus"){
        this.document.querySelector('.basketBlock').style.filter = 'blur(5px)';

        sendGetRequest(`/basket/plus/${element.target.dataset.id}/1`)
        
        this.setTimeout(function(){
            this.document.querySelector('.basketBlock').style.filter = 'blur(0px)';
        }, 1000)
    }
    if(element.target.id === "minus"){
        this.document.querySelector('.basketBlock').style.filter = 'blur(5px)';

        sendGetRequest(`/basket/minus/${element.target.dataset.id}/1`)

        this.setTimeout(function(){
            this.document.querySelector('.basketBlock').style.filter = 'blur(0px)';
        }, 1000)
    }
    if(element.target.id === "delete"){
        this.document.querySelector('.basketBlock').style.filter = 'blur(5px)';

        sendGetRequest(`/basket/quantity/${element.target.dataset.id}/0`)

        this.setTimeout(function(){
            this.document.querySelector('.basketBlock').style.filter = 'blur(0px)';
        }, 1000)
    }

    if(element.target.id === "add-to-card"){
        document.getElementById('add-to').style.display = 'flex'
        let val = element.target.closest('div').querySelector('input').value
        sendGetRequest(`/basket/plus/${element.target.dataset.id}/${val}`)
        element.target.closest('div').querySelector('input').value = 1

        this.setTimeout(function(){
            document.getElementById('add-to').style.display = 'none'
        },500)
    }
})


window.addEventListener('change', function(element){
    if(element.target.id === "change-val"){
        this.document.querySelector('.basketBlock').style.filter = 'blur(5px)';

        sendGetRequest(`/basket/quantity/${element.target.dataset.id}/${element.target.value}`)

        this.setTimeout(function(){
            this.document.querySelector('.basketBlock').style.filter = 'blur(0px)';
        }, 1000)
    }
})
// ---------------------------------------------------------





// Відкрити закрити кошик
document.getElementById('basketBtn').addEventListener('click', function(){
    if(document.querySelector('.basketBlock').style.right === "0px"){
        document.querySelector('.basketBlock').style.right = "-1500px"
        setTimeout(function(){
            document.querySelector('body').style = 'overflow-y: scroll;'
        },1000)
    }else{
        document.querySelector('.basketBlock').style.right = "0"
        document.querySelector('body').style = 'overflow: hidden;'
        
    }
})


document.getElementById('closeBasket').addEventListener('click', function(){
    document.querySelector('.basketBlock').style.right = "-1500px"
    document.querySelector('body').style = 'overflow-y: scroll;'
})


document.getElementById('shopingCards').addEventListener('click', function(){
    if(document.querySelector('.basketBlock').style.right === "0px"){
        document.querySelector('.basketBlock').style.right = "-1500px"
        document.querySelector('body').style = 'overflow-y: scroll;'
    }else{
        document.querySelector('.basketBlock').style.right = "0"
        setTimeout(function(){
            document.querySelector('body').style = 'overflow: hidden;'
        },1000)
        
    }
})


document.getElementById('basketTablet').addEventListener('click', function(){
    if(document.querySelector('.basketBlock').style.right === "0px"){
        document.querySelector('.basketBlock').style.right = "-1500px"
        
        document.querySelector('body').style = 'overflow-y: scroll;'
    }else{
        document.querySelector('.basketBlock').style.right = "0"
        setTimeout(function(){
            document.querySelector('body').style = 'overflow: hidden;'
        },1000)
        
        basketRender()

    }
})
// ---------------------------------------------------------


// Рендер кошика
function basketRender() {
    fetch('/basket')
        .then(response => response.json())
        .then(data => {
            const basketList = document.querySelector('.basket-list');
            basketList.innerHTML = ''; // Очищаємо список перед рендерингом

            if (data.product_items && data.product_items.length > 0) {

                document.querySelector('.cardCount').innerText = data.product_items.length;
                document.querySelector('.cardCountDoc').innerText = data.product_items.length;

                data.product_items.forEach(prod => {
                    const product = prod.product;
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `
                        <img src="/storage/${product.main_image}" alt="${product.name}">
                        <div class="info-product-basket">
                            <h4><a href="/catalog/product/${product.id}">${product.name}</a></h4>
                            <p>Ціна: ₴${prod.price}</p>
                            <div>
                                <button class="button-main" type="button" id="minus" data-id="${product.id}">-</button>
                                <input class="input-main" id="change-val" type="text" data-id="${product.id}" value="${prod.quantity}">
                                <button class="button-main" type="button" id="plus" data-id="${product.id}">+</button>
                            </div>
                        </div>
                        
                        <button class="delete-btn" type="button" id="delete" data-id="${product.id}">Прибрати</button>
                        `;
                    basketList.appendChild(listItem);
                });

                
                document.querySelector('.totalBasket').innerText = `₴${data.total}`;
                
                document.querySelector('.totalBloc').style.display = 'flex'
                document.querySelector('.chekout').style.display = 'block'

            } else {
                basketList.innerHTML = '<li>Кошик порожній</li>';
                document.querySelector('.chekout').style.display = 'none'
                document.querySelector('.totalBloc').style.display = 'none'
            }
        })
        .catch(error => {
            console.error('Помилка отримання даних:', error);
            basketList.innerHTML = '<li>Помилка завантаження кошика</li>';
        });
}

// ---------------------------------------------------------


// GET-запит
function sendGetRequest(url) {
    // Виконуємо GET-запит за допомогою fetch
    fetch(url)
        .then(response => {
            // Перевіряємо статус відповіді
            if (!response.ok) {
                // Якщо статус відповіді не є успішним, викидаємо помилку
                throw new Error('Помилка запиту: ' + response.status);
            }
            // Повертаємо відповідь у форматі JSON
            return response.json();
        })
        .then(data => {
            // Обробляємо отримані дані
            console.log(data);
        })
        .then(render=>{
            basketRender()
        })
        .catch(error => {
            // Обробляємо помилку, якщо вона виникла
            console.error(error.message);
        });
}

// Відображення кошика при завантаженні сторінки
basketRender()


