<style>
    body{
        background: #f4f6f9;
        font-family: Arial, sans-serif;
    }

    .container-fluid{
        max-width: 1500px;
        margin: 40px auto;
        padding: 20px;
    }

    .page-title{
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 25px;
        color: #1f2937;
    }

    .card{
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .card-body{
        padding: 0;
    }

    .supplier-table{
        width: 100%;
        border-collapse: collapse;
    }

    .supplier-table thead{
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
    }

    .supplier-table thead th{
        padding: 18px 14px;
        text-align: left;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .supplier-table tbody tr{
        transition: 0.2s;
        border-bottom: 1px solid #e5e7eb;
    }

    .supplier-table tbody tr:hover{
        background: #f9fafb;
    }

    .supplier-table tbody td{
        padding: 16px 14px;
        font-size: 14px;
        color: #374151;
        vertical-align: middle;
    }

    .product-name{
        font-weight: 600;
        color: #111827;
    }

    .sku{
        font-family: monospace;
        color: #6b7280;
    }

    .stock{
        font-weight: 700;
        color: #059669;
    }

    .sales{
        color: #2563eb;
        font-weight: 600;
    }

    .forecast{
        color: #7c3aed;
        font-weight: 600;
    }

    .to-order{
        display: inline-block;
        background: #fee2e2;
        color: #dc2626;
        padding: 6px 12px;
        border-radius: 999px;
        font-weight: 700;
        min-width: 60px;
        text-align: center;
    }

    .empty{
        text-align: center;
        padding: 40px;
        color: #9ca3af;
        font-size: 18px;
    }

    @media(max-width: 1100px){

        .card{
            overflow-x: auto;
        }

        .supplier-table{
            min-width: 1000px;
        }
    }
</style>

<div class="container-fluid">

    <h1 class="page-title">
        Замовлення постачальнику
    </h1>

    <div class="card">

        <div class="card-body">

            <table class="supplier-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Товар</th>
                        <th>SKU</th>
                        <th>Залишок</th>
                        <th>Продано за 90 днів</th>
                        <th>Прогноз на 3 міс.</th>
                        <th>Треба замовити</th>
                    </tr>
                </thead>

                <tbody>

                    @php
                        $hasProducts = false;
                    @endphp

                    @foreach($products as $product)

                        @if($product->to_order > 0)

                            @php
                                $hasProducts = true;
                            @endphp

                            <tr>

                                <td>
                                    #{{ $product->id }}
                                </td>

                                <td class="product-name">
                                    {{ $product->name }}
                                </td>

                                <td class="sku">
                                    {{ $product->internal_sku }}
                                </td>

                                <td class="stock">
                                    {{ $product->quantity }}
                                </td>

                                <td class="sales">
                                    {{ $product->sold_90 }}
                                </td>

                                <td class="forecast">
                                    {{ $product->forecast_3_months }}
                                </td>

                                <td>
                                    <span class="to-order">
                                        {{ $product->to_order }}
                                    </span>
                                </td>

                            </tr>

                        @endif

                    @endforeach

                    @if(!$hasProducts)

                        <tr>
                            <td colspan="7" class="empty">
                                Усі товари в достатній кількості
                            </td>
                        </tr>

                    @endif

                </tbody>

            </table>

        </div>

    </div>

</div>