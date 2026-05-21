<section class="breadcrumbs">
    <nav aria-label="breadcrumb">
        <ol>
            <li><a href="/">Головна</a></li>
            <li><a href="/"></a></li>
        </ol>
    </nav>
</section>

<style>
    .breadcrumbs ol {
    list-style: none;
    display: flex;
    gap: 5px;
    padding: 0;
}

.breadcrumbs li::after {
    content: "/";
    margin-left: 5px;
    font-size:13px;
}

.breadcrumbs li:last-child::after {
    content: "";
}

.breadcrumbs a {
    text-decoration: none;
    font-size:13px;
    color: #616161ff;
}

.breadcrumbs a:hover {
    text-decoration: underline;
}

</style>