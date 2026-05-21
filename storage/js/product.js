window.addEventListener('click', function(e){
    if (e.target.classList.contains('img-slider-item')) {
        // Змінюємо головне зображення
        e.target.closest('.img-product').querySelector('.main-img').src = e.target.src;

        // Прибираємо бордер у всіх
        document.querySelectorAll('.img-slider-item').forEach(item => {
            item.style.border = "none";
        });

        // Виділяємо активний
        e.target.style.border = "1px solid #ffd037";
    }

    if (e.target.classList.contains('main-img')){
        this.document.querySelector('.zoom-img-bloc').style.display = 'flex'
        this.document.querySelector('.zoom-img').querySelector('img').src = e.target.src
        this.document.querySelector('body').style.overflow = 'hidden'

    }

    if (e.target.classList.contains('zoom-img-bloc') || e.target.classList.contains('zoom-img-close')){
        this.document.querySelector('.zoom-img-bloc').style.display = 'none'
        this.document.querySelector('body').style.overflow = 'scroll'

    }
});
