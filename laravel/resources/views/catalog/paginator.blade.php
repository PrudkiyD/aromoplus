<style>
    .paginator nav {
        margin: 10px 0px;
        border: 1px solid rgba(138, 138, 138, 0.1);
    }
    
    /* Приховуємо блок з текстом "Showing..." */
    .paginator nav div:first-child:not([class]) {
        display: none;
    }

    /* Якщо використовується Tailwind-версія пагінації, цей селектор спрацює точніше */
    .paginator nav .flex.items-center.justify-between div:first-child p {
        display: none;
    }
    
    /* Центруємо кнопки, якщо вони зсунулися */
    .paginator nav .justify-between {
        justify-content: center;
    }

    .paginator nav span[aria-current="page"] {
        color: #7d7d7d;
    }
</style>

<div class="paginator">
    {{ $products->appends(['group' => request('group')])->links() }}
</div>