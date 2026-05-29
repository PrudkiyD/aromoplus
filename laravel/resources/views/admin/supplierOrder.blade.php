@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    <h1 class="mb-4">Замовлення постачальнику</h1>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-striped">
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

                    @foreach($products as $product)

                        @if($product->to_order > 0)

                            <tr>
                                <td>{{ $product->id }}</td>

                                <td>
                                    {{ $product->name }}
                                </td>

                                <td>
                                    {{ $product->internal_sku }}
                                </td>

                                <td>
                                    {{ $product->quantity }}
                                </td>

                                <td>
                                    {{ $product->sold_90 }}
                                </td>

                                <td>
                                    {{ $product->forecast_3_months }}
                                </td>

                                <td>
                                    <strong class="text-danger">
                                        {{ $product->to_order }}
                                    </strong>
                                </td>
                            </tr>

                        @endif

                    @endforeach

                </tbody>
            </table>

        </div>
    </div>

</div>

@endsection