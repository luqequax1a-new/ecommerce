@php
use App\\Services\\StockHelper;

$stockStatus = $product->getStockStatus($lowStockThreshold ?? 5);
$totalStock = $product->total_stock;
$unit = $product->unit;
@endphp

@if($stockStatus === StockHelper::STATUS_IN_STOCK)
    <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800\">
        <svg class=\"w-2 h-2 mr-1\" fill=\"currentColor\" viewBox=\"0 0 8 8\">
            <circle cx=\"4\" cy=\"4\" r=\"3\"/>
        </svg>
        Stokta
        @if($showQuantity ?? true)
            ({{ StockHelper::formatStockWithUnit($totalStock, $unit) }})
        @endif
    </span>
@elseif($stockStatus === StockHelper::STATUS_LOW_STOCK)
    <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800\">
        <svg class=\"w-2 h-2 mr-1\" fill=\"currentColor\" viewBox=\"0 0 8 8\">
            <circle cx=\"4\" cy=\"4\" r=\"3\"/>
        </svg>
        Az Stok
        @if($showQuantity ?? true)
            ({{ StockHelper::formatStockWithUnit($totalStock, $unit) }})
        @endif
    </span>
@elseif($stockStatus === StockHelper::STATUS_OUT_OF_STOCK)
    <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800\">
        <svg class=\"w-2 h-2 mr-1\" fill=\"currentColor\" viewBox=\"0 0 8 8\">
            <circle cx=\"4\" cy=\"4\" r=\"3\"/>
        </svg>
        Stokta Yok
    </span>
@else
    <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800\">
        <svg class=\"w-2 h-2 mr-1\" fill=\"currentColor\" viewBox=\"0 0 8 8\">
            <circle cx=\"4\" cy=\"4\" r=\"3\"/>
        </svg>
        Mevcut Değil
    </span>
@endif