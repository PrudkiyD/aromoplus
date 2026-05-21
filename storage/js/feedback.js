document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#feedback-form form');
    const sendBtn = document.querySelector('.sendFormBtn');

    // Блок для повідомлень
    let messageBox = document.createElement('div');
    messageBox.className = 'feedback-message';
    form.appendChild(messageBox);

    const showMessage = (text, success = true) => {
        messageBox.textContent = text;
        messageBox.style.color = success ? 'green' : 'red';
        messageBox.style.marginTop = '10px';
    };

    // Формат номера телефону
    const phoneBasket = document.querySelector('.feedback-phone')

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

    sendBtn.addEventListener('click', async () => {
        let phoneInput = document.querySelector('#fedbackphone')
        const name = document.querySelector('#fedbackname').value.trim();
        const phone = phoneInput.value.trim();
        const message = document.querySelector('#fedbacktext').value.trim();

        // Очистка повідомлень
        messageBox.textContent = '';

        // Перевірка порожніх полів
        let hasError = false;
        [document.querySelector('#fedbackname'), phoneInput, document.querySelector('#fedbacktext')].forEach(input => {
            input.style.borderColor = '';
            if (!input.value.trim()) {
                input.style.borderColor = 'red';
                hasError = true;
            }
        });
        if (hasError) {
            showMessage('Будь ласка, заповніть усі поля!', false);
            return;
        }

        // CSRF токен Laravel
        const token = document.getElementById('feedback-form').querySelector('input[name="_token"]').value;

        try {
            const response = await fetch('/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ name, phone, message })
            });

            const data = await response.json();

            if (response.ok && data.feedback === '200') {
                showMessage('Повідомлення надіслано успішно!');
                form.reset();
            } else {
                showMessage('Сталася помилка при відправці.', false);
            }
        } catch (error) {
            console.error('Помилка:', error);
            showMessage('Помилка при відправці.', false);
        }
    });
});



    
