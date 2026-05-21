<footer>
    <nav>
        {!! $kontakti->content !!}
    </nav>
    <nav>
        {!! $adresa->content !!}
    </nav>
    <nav>
        <ul>
            <div class="footerTitle">Товари</div>
            @foreach ($categoryTree ?? [] as $category)
                {{-- Відображаємо тільки верхній рівень --}}
                @if(is_null($category->parent_id))
                    <li><a href="/catalog/{{ $category->slug }}/"><strong>{{ $category->name }}</strong></a></li>
                @endif
            @endforeach
        </ul>
    </nav>

    <nav>
        <ul>
            <div class="footerTitle">Послуги</div>
            @foreach ($services as $service)
                <li><a href="/services/{{$service->slug}}/">{{$service->name}}</a></li>
            @endforeach
        </ul>
    </nav>

    <nav>
        <ul>
            <div class="footerTitle">Інформація</div>
            @foreach ($infos as $info)
                <li><a href="/info/{{$info->slug}}">{{$info->name}}</a></li>
            @endforeach
        </ul>
    </nav>
</footer>
