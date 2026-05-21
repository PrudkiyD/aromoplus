<!DOCTYPE html>
<html lang="uk">
<head>
   @include('base.head') 
</head>
<body id="search">
    @include('base.loader') 
    @include('base.massage')
    @include('base.doc')

    <div class="wrapper">
        @include('base.header')
        
        <div class="container">
            @include('base.aside')
            <main>
                @yield('content') 
                <hr>
                @include('base.feedback')              
            </main>
            @if (!Request::is('order/start'))
                @include('base.basket') 
            @endif
        </div>
        @include('base.footer') 
    </div>
    @include('base.script') 
</body>
</html>
