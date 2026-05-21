const city = document.querySelector('.city')
const department = document.querySelector('.department')
const street = document.querySelector('.street')
const addresses = document.querySelector('.addresses')


street.style.display = 'none'
addresses.style.display = 'none'

document.querySelector('.delivery').addEventListener('change', function(el){
  if(el.target.value === 'cat'){
    street.style.display = 'block'
    addresses.style.display = 'block'
    department.style.display = 'none'
  }
  if(el.target.value === 'nova'){
    street.style.display = 'none'
    addresses.style.display = 'none'

    department.style.display = 'block'
  }
})

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
