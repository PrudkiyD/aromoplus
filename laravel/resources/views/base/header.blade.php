<header>
    <nav>
        <a href="/">
            <div class="logo">Aromoplus</div>
            <div class="img-logo"><img src="/storage/img/whiteLogo.png" width="30" alt="logo"></div>
        </a>
        
    </nav>
    <nav class="search-block">
        <form action="/catalog/search/" >
            <input class="input-main" id="searchSpace" type="search" name="search" 
            placeholder="Пошук запчастин (назва, артикул, виробник)"
            value="{{ $search ?? '' }}"
            >
            <button class="button-main">Пошук</button>
        </form>
        <ul id="resSearch"></ul>
    </nav>
    <nav class="nav-basket-profile-menu">
        <ul class="basket-profile">
            <li id="shopingCards">
                <button class="button-main shopingCards" type="button" title="Кошик">
                    <img src="/storage/img/shopping-cart-svgrepo-com.svg" alt=""><div class="cardCount">0</div>
                </button>
            </li>
            <li>
                <a href="/account">
                    <button class="button-main" title="Увійти в особистий кабінет">
                        <img src="/storage/img/avatar-svgrepo-com.svg" alt="">
                    </button>
                </a>
            </li>
        </ul>
        <ul class="basket-profile-tablet"> 
            <li id="basketTablet"><img src="/storage/img/shopping-cart-white.svg" alt=""></li>
            <li class="burger"><img width="30" src="/storage/img/menu-tablet.svg" id="burger" alt=""></li>
        </ul>
    </nav>
</header>