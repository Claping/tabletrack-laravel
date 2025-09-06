<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ isRtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $restaurant->name ?? 'Demo Restaurant' }} - @lang('modules.order.kotTicket')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        [dir="rtl"] { text-align: right; }
        [dir="ltr"] { text-align: left; }
        .receipt {
            width: 80mm;
            padding: 6.35mm;
            page-break-after: always;
        }
        .header {
            text-align: center;
            margin-bottom: 3mm;
        }
        .bold {
            font-weight: bold;
        }
        .restaurant-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        .restaurant-info {
            font-size: 9pt;
            margin-bottom: 1mm;
        }
        .order-info {
            text-align: center;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
            margin-bottom: 3mm;
            font-size: 9pt;
        }
        .kot-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2mm;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
            font-size: 9pt;
        }
        .items-table th {
            padding: 1mm;
            border-bottom: 1px solid #000;
        }
        [dir="rtl"] .items-table th { text-align: right; }
        [dir="ltr"] .items-table th { text-align: left; }
        .items-table td {
            padding: 1mm 0;
            vertical-align: top;
        }
        .qty {
            width: 15%;
            text-align: center;
        }
        .description {
            width: 85%;
        }
        .modifiers {
            font-size: 8pt;
            color: #555;
        }
        .footer {
            text-align: center;
            margin-top: 3mm;
            font-size: 9pt;
            padding-top: 2mm;
            border-top: 1px dashed #000;
        }
        .italic {
            font-style: italic;
        }
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            @if(isset($kotPlace) && $kotPlace)
                <div class="restaurant-info">{{ $kotPlace->name }}</div>
            @endif
        </div>
        <div class="kot-title">
            @lang('modules.order.kitchenOrderTicket')
        </div>
        <div class="order-info" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="text-align: left;">
                <div>
                    <span class="bold">
                        {{ $kot->order->show_formatted_order_number }}
                    </span>
                    @if($kot->order->table)
                        <span style="margin-left: 10px;">@lang('modules.table.table') - <span class="bold">{{ $kot->order->table->table_code }}</span></span>
                    @endif
                </div>
                <div style="margin-top: 5px;">
                    @lang('app.date'): {{ $kot->created_at->timezone($kot->branch->restaurant->timezone)->format('d-m-Y') }}
                </div>
                @if($kot->order->waiter)
                    <div style="margin-top: 5px;">
                        @lang('modules.order.waiter'): <span class="bold">{{ $kot->order->waiter->name }}</span>
                    </div>
                @endif
            </div>
            <div style="text-align: right;">
                <div>
                    KOT <span class="bold">#{{ $kot->id }}</span>
                </div>
                <div style="margin-top: 5px;">
                    @lang('app.time'): {{ $kot->created_at->timezone($kot->branch->restaurant->timezone)->format('h:i A') }}
                </div>
            </div>
        </div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="description">@lang('modules.menu.itemName')</th>
                    <th class="qty">@lang('modules.order.qty')</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = isset($kotPlaceId)
                        ? $kot->items->filter(function($item) use($kotPlaceId) {
                            return $item->menuItem && $item->menuItem->kot_place_id == $kotPlaceId;
                        })
                        : $kot->items;
                @endphp
                @foreach($items as $item)
                    <tr>
                        <td class="description">
                            {{ $item->menuItem->item_name }}
                            @if (isset($item->menuItemVariation))
                                <br><small>({{ $item->menuItemVariation->variation }})</small>
                            @endif
                            @foreach ($item->modifierOptions as $modifier)
                                <div class="modifiers">• {{ $modifier->name }}</div>
                            @endforeach
                            @if ($item->note)
                                <div class="modifiers"><strong>@lang('modules.order.note'):</strong> {{ $item->note }}</div>
                            @endif
                        </td>
                        <td class="qty">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($kot->note)
            <div class="footer">
                <strong>@lang('modules.order.specialInstructions'):</strong>
                <div class="italic">{{$kot->note}}</div>
            </div>
        @endif
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
