<aside>
    <h2>Товари</h2>
    <hr>
    <ul class="main-categories">
        @foreach ($categoryTree ?? [] as $category)
            
            <li class="main-category">
                <a href="/catalog/{{ $category->slug }}/">
                    <strong>{{ $category->name }}</strong>
                </a>
            
                @if($category->childrenTree)
                    <ul class="sub-categories">
                        @foreach ($category->childrenTree as $children)
                            <li class="sub-category">
                                <a href="/catalog/{{ $category->slug }}/{{ $children->slug }}/">
                                    {{ $children->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>

    <br>
    <h2>Послуги</h2>
    <hr>
    <ul>
        @foreach ($services as $service)
            <li class="main-category">
                <a href="/services/{{$service->slug}}/"><strong>{{$service->name}}</strong></a>
            </li>
        @endforeach
    </ul>

    <br>
    <h2>Інформація</h2>
    <hr>
    <ul>
        @foreach ($infos as $info)
            <li><a href="/info/{{$info->slug}}">{{$info->name}}</a></li>
        @endforeach
    </ul>
</aside>
