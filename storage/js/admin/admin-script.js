const currentUrl = window.location.pathname
const pattern = /^\/admin\/order\/orders\/\d+\/edit$/
console.log(currentUrl)
const domen = 'https://aromoplus.com.ua'



async function updatePrice() {
    document.querySelector('.loader-box').style.display = 'flex'
    let pricelistSelect = document.getElementById('data.price_list_id')
    let productRows = document.querySelectorAll('tbody tr')
    
    // Створюємо масив промісів для кожного запиту fetch
    let promises = []

    productRows.forEach(row => {
        let links = row.querySelectorAll('a[data-product_id]')
        
        links.forEach(infoEl => {
            let url = domen + `/admin/change-product/${infoEl.dataset.order_id}/${infoEl.dataset.product_id}/${pricelistSelect.value}/${infoEl.dataset.quantity}/1`
            infoEl.closest('tr').remove()
            // Створюємо проміс для кожного товару
            let request = fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`Помилка: ${response.status}`)
                    return response.json()
                })
                .then(data => console.log("Дані отримано:", data))
                .catch(error => console.error("Помилка:", error))
            
            promises.push(request)
        })
    })

    // Чекаємо, поки ВСІ запити з масиву завершаться
    await Promise.all(promises)
    console.log("Усі запити до БД завершено.")
}

function findeLive(){
    let components = Livewire.all()
    let target = components.find(c => c.name.includes('product-item') || c.name.includes('relation-manager'))

    if (target) {
        console.log('Компонент знайдено:', target.id)
        return target.id
    } else {
        console.log('Компонент не знайдено, оновлюємо все через $refresh')
        return false
    }
}

async function updateRelation(target_id){
    await updatePrice()

    if(target_id){
        document.querySelector('table').remove()
        let component = Livewire.find(target_id)

        if (component) {
            component.call('$refresh')
            setTimeout(function(){
                document.querySelector('.loader-box').style.display = 'none'
            }, 1500)
        
        }
    }
}

function renderCreateTTNWindow(){
    document.querySelector('.custom-html-start').innerHTML +=`
        <div class="create-ttn-win-bg" style="display:none;">
            <div class="create-ttn-win-box">
            </div>
        </div>
    `
}

function renderLoader(){
    document.querySelector('.custom-html-start').innerHTML +=`
        <div class="loader-box" style="display:none;">
            <div class="loader"></div>
        </div>
    `
}

function renderBtn(){
    this.document.querySelector('.fi-form-actions').querySelector('div').innerHTML += `
        <button data-action="copy-row" type="button" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg  fi-btn-color-gray fi-color-gray fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 ring-1 ring-gray-950/10 dark:ring-white/20 [input:checked+&]:bg-gray-400 [input:checked+&]:text-white [input:checked+&]:ring-0 [input:checked+&]:hover:bg-gray-300 dark:[input:checked+&]:bg-gray-600 dark:[input:checked+&]:hover:bg-gray-500 fi-ac-action fi-ac-btn-action" >
            <p data-action="copy-row">Реєстр</p>
            <svg data-action="copy-row" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
            </svg>
        </button>
        <button data-action="copy-message" type="button" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg  fi-btn-color-gray fi-color-gray fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-white text-gray-950 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 ring-1 ring-gray-950/10 dark:ring-white/20 [input:checked+&]:bg-gray-400 [input:checked+&]:text-white [input:checked+&]:ring-0 [input:checked+&]:hover:bg-gray-300 dark:[input:checked+&]:bg-gray-600 dark:[input:checked+&]:hover:bg-gray-500 fi-ac-action fi-ac-btn-action">
            <p data-action="copy-message">Повідомлення</p>
            <svg data-action="copy-message" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
            </svg>
        </button>
    `
    this.document.querySelector('[wire\\:model="data.ttn"]').parentElement.parentElement.innerHTML += `
        <button data-action="new-ttn" type="button" class="add-btn-js-dop">
            <p data-action="new-ttn">Створити</p>
        </button>
    `

    this.document.querySelector('[wire\\:model="data.total"]').parentElement.parentElement.innerHTML += `
        <button data-action="update-total" type="button" class="add-btn-js-dop">
            <p data-action="update-total">Перерахувати</p>
        </button>
    `

    this.document.querySelector('[wire\\:model="data.price_list_id"]').parentElement.parentElement.innerHTML += `
        <button data-action="update-price" type="button" class="add-btn-js-dop">
            <p data-action="update-price">Перерахувати</p>
        </button>
    `

}

function copyRow(el){
    let targetBtn = el.target 
    let text = ''
    let organization = this.document.querySelector('[wire\\:model="data.organization"]').value

    if(organization){
        organization = `/ ${organization}`
    }else{
        organization = ''
    }

    text = `${this.document.querySelector('[wire\\:model="data.last_name"]').value} ${this.document.querySelector('[wire\\:model="data.first_name"]').value} ${organization}\t`
    text += `${this.document.querySelector('[wire\\:model="data.phone_number"]').value}\t`
    text += `${this.document.querySelector('[wire\\:model="data.number"]').value}\t`
    text += `${this.document.querySelector('[wire\\:model="data.total"]').value}\t`

    let paySelect = this.document.getElementById('data.payment_type')
    text += `${paySelect.options[paySelect.selectedIndex].text}`

    text = text.replace('.',',')

    navigator.clipboard.writeText(text).then(() => {
            targetBtn.innerText = 'Cкопійовано'
        }).catch(err => {
            targetBtn.innerText = 'Помилка'
            console.error('Помилка копіювання: ', err)
        }

        
        
    ).then(() =>{
        setTimeout(function(){
            targetBtn.innerText = 'Реєстр'
        },2000)
    })

        
}

function copyMessage(el){
    let targetBtn = el.target 
    let text = ''

    let organization = this.document.querySelector('[wire\\:model="data.organization"]').value

    if(organization){
        organization = `/ ${organization}`
    }else{
        organization = ''
    }

    text = `Номер замовлення: ${this.document.querySelector('[wire\\:model="data.number"]').value}\n`
    text += `Прізвище та ім'я: ${this.document.querySelector('[wire\\:model="data.last_name"]').value} ${this.document.querySelector('[wire\\:model="data.first_name"]').value} ${organization}\n`
    text += `Номер телефону: ${this.document.querySelector('[wire\\:model="data.phone_number"]').value}\n`

    let paySelect = this.document.getElementById('data.payment_type')
    text += `Оплата: ${paySelect.options[paySelect.selectedIndex].text}`

    text += `\n\n`

    let productList = this.document.querySelector('tbody').querySelectorAll('tr')

    productList.forEach(el =>{
        let productInfo = el.querySelectorAll('td')
        
        productInfo.forEach(infoEl =>{
            if(infoEl.getAttribute('wire:key')){
                let info = infoEl.getAttribute('wire:key').split('.').at(-1)

                if(info === 'name'){
                    text += `${infoEl.innerText}\n`
                }

                if(info === 'price'){
                    text += `Ціна: ${infoEl.querySelector('input').value}\n`
                }

                if(info === 'quantity'){
                    text += `Кількість: ${infoEl.querySelector('input').value}\n`
                }
            }
            
        })           
        
        text += `\n`
    })

    text += `\n`
    text += `Загальна сума: ${this.document.querySelector('[wire\\:model="data.total"]').value} грн. \n`
    text += `В реєстер вніс.`



    navigator.clipboard.writeText(text).then(() => {
            targetBtn.innerText = 'Cкопійовано'
        }).catch(err => {
            targetBtn.innerText = 'Помилка'
            console.error('Помилка копіювання: ', err)
        }

        
        
    ).then(() =>{
        setTimeout(function(){
            targetBtn.innerText = 'Повідомлення'
        },2000)
    })

        
}

function updateTotal(){
    document.querySelector('.loader-box').style.display = 'flex'
    let totalInput = document.querySelector('[wire\\:model="data.total"]')
    let total = 0 

    let productList = this.document.querySelector('tbody').querySelectorAll('tr')

    productList.forEach(el =>{
        let productInfo = el.querySelectorAll('td')

        let price = 0
        let quantity = 0
        
        productInfo.forEach(infoEl =>{
            if(infoEl.getAttribute('wire:key')){

                let info = infoEl.getAttribute('wire:key').split('.').at(-1)
                
                if(info === 'price'){
                    price = infoEl.querySelector('input').value
                }

                if(info === 'quantity'){
                    quantity = infoEl.querySelector('input').value
                }
            }
        })

        total += price * quantity
            
    })
    
    totalInput.value =  total.toFixed(1)
    totalInput.dispatchEvent(new Event('input'))

    setTimeout(function(){
        document.querySelector('.loader-box').style.display = 'none'
    }, 500)
    
}

function sendAddProduct(selectAddProduct, quantityInput){
    let product_id = selectAddProduct.value
    let pricelistSelect = document.getElementById('data.price_list_id')
    let url = domen + `/admin/change-product/${currentUrl.split('/')[6]}/${product_id}/${pricelistSelect.value}/${quantityInput.value}/0`

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`Помилка: ${response.status}`)
            return response.json()
        })
        .then(data => {
            console.log("Дані отримано:", data)
            
            // Знаходимо інпут
            const input = document.querySelector('[wire\\:model="mountedTableActionsData.0.price"]')
            const img = document.getElementById('addProductImg')
            
            if (input && data.price !== undefined) {
                input.value = data.price
                img.src = data.image

                input.dispatchEvent(new Event('input'))
            }
        })
        .catch(error => console.error("Помилка:", error))
}

function addProduct(el){
    let selectAddProduct = document.getElementById('mountedTableActionsData.0.product_id')
    let quantityInput = document.getElementById('mountedTableActionsData.0.quantity')

    if(selectAddProduct){
        selectAddProduct.addEventListener('change', function(){
           sendAddProduct(selectAddProduct, quantityInput)         
        })

        quantityInput.addEventListener('change', function(){
           sendAddProduct(selectAddProduct, quantityInput)         
        })
    }
}

function createTtn(){
    document.querySelector('.loader-box').style.display = 'flex'
    setTimeout(function(){
       document.querySelector('.loader-box').style.display = 'none' 
    }, 2000)
    
}

if (pattern.test(currentUrl)) {
    renderBtn()
    renderCreateTTNWindow()
}

renderLoader()


document.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key === 's' || event.ctrlKey && event.key === 'і') {
            setTimeout(function(){
                if (pattern.test(currentUrl)) {
                    renderBtn()
                }
            }, 2000)
        }

        
})

window.addEventListener('click', function(el){

    if(el.target.dataset.action == "copy-row"){
        copyRow(el)
    }

    if(el.target.dataset.action == "copy-message"){
       copyMessage(el)
    }


    if(el.target.dataset.action == "update-total"){
        updateTotal()
    }

    if(el.target.dataset.action == "update-price"){
        updateRelation(findeLive())
    }

    if(el.target.dataset.action == "new-ttn"){
        createTtn()
    }

    addProduct(el) 
})

document.querySelectorAll('input[type="file"][data-path]').forEach(input => {
    input.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('path', this.dataset.path);
        formData.append('model', this.dataset.model);
        formData.append('col', this.dataset.col);
        formData.append('model_id', this.dataset.modelId ?? ''); // якщо є

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        console.log(csrfToken)

        try {
            const response = await fetch('/admin/img-import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                console.error('Помилка:', data.message);
                alert('Помилка: ' + data.message);
                return;
            }

            alert('Зображення збережено:', data.path);
            document.querySelector(`${this.dataset.preview}`).src = '/storage/' + data.path;
        } catch (e) {
            console.error('Fetch error:', e);
        }
    });
});