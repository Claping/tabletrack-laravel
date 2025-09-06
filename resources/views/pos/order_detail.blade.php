<div class="flex flex-col h-auto min-h-screen px-2 py-4 pr-4 bg-white border-l lg:w-5/12 dark:border-gray-700 dark:bg-gray-800">
    <div>
        <h2 class="text-lg dark:text-neutral-200">
            {{ $orderDetail->show_formatted_order_number }}
        </h2>
        <div class="flex justify-between gap-3 my-4 space-y-1">
            <div class="inline-flex gap-4">
                <div @class(['p-3 rounded-lg tracking-wide bg-skin-base/[0.2] text-skin-base'])>
                    <h3 wire:loading.class.delay='opacity-50'
                        @class(['font-semibold'])>
                        {{ $orderDetail->table->table_code ?? '--' }}
                    </h3>
                </div>
                <div>
                    @if ($orderDetail->customer_id)
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-gray-700 dark:text-gray-300">{{ $orderDetail->customer->name }}</div>
                            @if(user_can('Update Order'))
                                <button  wire:click="$dispatch('showAddCustomerModal', { id: {{ $orderDetail->id }}, customerId: {{ $orderDetail->customer_id }}, fromPos: true })" title="{{__('modules.order.updateCustomerDetails')}}" class="p-1 text-gray-500 transition-colors bg-gray-100 rounded-md hover:text-gray-700 hover:bg-gray-200 rtl:ml-2 ltr:mr-2 dark:text-gray-300 dark:bg-gray-600 dark:hover:text-gray-200 dark:hover:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>
                                </button>
                            @endif
                        </div>
                    @elseif(user_can('Update Order'))
                        <a href="javascript:;" wire:click="$dispatch('showAddCustomerModal', { id: {{ $orderDetail->id }}, customerId: null, fromPos: true })"
                        class="text-sm underline dark:text-gray-300 underline-offset-2">&plus; @lang('modules.order.addCustomerDetails')</a>
                    @endif
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $orderDetail->date_time->timezone(timezone())->translatedFormat('F d, Y h:i A') }}</div>
                </div>

            </div>
            <div>
                <span @class(['text-sm font-medium px-2 py-1 rounded uppercase tracking-wide whitespace-nowrap ',
                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400 border border-gray-400' => ($orderDetail->status == 'draft'),
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-400 border border-yellow-400' => ($orderDetail->status == 'kot'),
                'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-400 border border-blue-400' => ($orderDetail->status == 'billed'),
                'bg-green-100 text-green-800 dark:bg-gray-700 dark:text-green-400 border border-green-400' => ($orderDetail->status == 'paid'),
                'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-400 border border-red-400' => ($orderDetail->status == 'canceled'),
                ])>
                    @lang('modules.order.' . $orderDetail->status)
                </span>
            </div>

        </div>

        @if ($orderDetail->order_status->value === 'cancelled')
            <span class="inline-block px-2 py-1 my-2 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                @lang('modules.order.info_cancelled')
                            </span>
                                @else
        <div class="p-4 mb-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
            @php
                $statuses = match($orderDetail->order_type) {
                    'delivery' => ['placed', 'confirmed', 'preparing', 'out_for_delivery', 'delivered'],
                    'pickup' => ['placed', 'confirmed', 'preparing', 'ready_for_pickup', 'delivered'],
                    default => ['placed', 'confirmed', 'preparing', 'served']
                };

                $currentIndex = array_search($orderDetail->order_status->value, $statuses);
                $currentIndex = $currentIndex !== false ? $currentIndex : 0;
                $nextIndex = min($currentIndex + 1, count($statuses) - 1);
            @endphp

            @if ($orderDetail->order_status->value === 'canceled')
                <div class="flex items-center justify-center">
                    <h3 class="text-lg font-semibold text-red-600 dark:text-red-400">
                        {{ __('modules.order.orderCancelled') }}
                    </h3>

                </div>
            @else
                <div class="flex flex-col space-y-4">
                    <div class="flex items-center justify-between text-gray-900 dark:text-white">
                        <h3 class="text-lg font-semibold">
                            {{ __('modules.order.orderStatus') }}
                        </h3>
                        <span class="px-3 py-1 text-sm font-medium rounded-full"
                            @class([
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $orderDetail->order_status->value === 'delivered' || $orderDetail->order_status->value === 'served',
                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $orderDetail->order_status->value === 'placed',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $orderDetail->order_status->value !== 'delivered' && $orderDetail->order_status->value !== 'served' && $orderDetail->order_status->value !== 'placed',
                            ])>
                            {{ __('modules.order.' . App\Enums\OrderStatus::from($orderDetail->order_status->value)->label()) }}
                        </span>


                    </div>

                    <div class="relative">
                        <div class="relative flex justify-between">
                            @foreach($statuses as $index => $status)
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mb-2
                                        @if($index <= $currentIndex)
                                            bg-skin-base text-white
                                        @else
                                            bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500
                                        @endif">
                                        @switch($status)
                                            @case('placed')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                @break
                                            @case('confirmed')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                @break
                                            @case('preparing')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 7.68 7.68" xmlns="http://www.w3.org/2000/svg"><path d="M7.584 3.072 6.72 3.72v1.8a0.961 0.961 0 0 1 -0.96 0.96H1.92a0.961 0.961 0 0 1 -0.96 -0.96v-1.8L0.096 3.072a0.24 0.24 0 0 1 0.288 -0.384L0.96 3.12V2.64a0.481 0.481 0 0 1 0.48 -0.48h4.8a0.481 0.481 0 0 1 0.48 0.48v0.48l0.576 -0.432a0.24 0.24 0 0 1 0.288 0.384M4.8 1.68a0.24 0.24 0 0 0 0.24 -0.24V0.48a0.24 0.24 0 0 0 -0.48 0v0.96a0.24 0.24 0 0 0 0.24 0.24m-0.96 0a0.24 0.24 0 0 0 0.24 -0.24V0.48a0.24 0.24 0 0 0 -0.48 0v0.96a0.24 0.24 0 0 0 0.24 0.24m-0.96 0a0.24 0.24 0 0 0 0.24 -0.24V0.48a0.24 0.24 0 0 0 -0.48 0v0.96a0.24 0.24 0 0 0 0.24 0.24" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                @break
                                            @case('out_for_delivery')
                                            @case('ready_for_pickup')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                @break
                                            @case('delivered')
                                            @case('served')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @break
                                        @endswitch
                                    </div>
                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('modules.order.' . App\Enums\OrderStatus::from($status)->label()) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(user_can('Update Order'))
                        <div class="flex justify-end items-center mt-4 space-x-2 rtl:!space-x-reverse">
                            @if($orderDetail->order_status->value === 'placed')
                                <x-danger-button class="inline-flex items-center gap-2 dark:text-gray-200" wire:click="$toggle('confirmDeleteModal')">
                                    <span>{{ __('modules.order.cancelOrder') }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </x-danger-button>
                            @endif

                            @if($currentIndex < count($statuses) - 1)
                                <x-secondary-button class="inline-flex items-center gap-2" wire:click="$set('orderStatus', '{{ $statuses[$nextIndex] }}')">
                                    <span>{{ __('modules.order.moveTo') }} {{ __('modules.order.' . App\Enums\OrderStatus::from($statuses[$nextIndex])->label()) }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </x-secondary-button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
        @endif

        @if ($orderDetail)
        <div class="flex flex-col rounded ">
            <table class="flex-1 min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th scope="col"
                            class="p-2 text-xs font-medium text-gray-500 uppercase dark:text-gray-400 rtl:text-right ltr:text-left">
                            @lang('modules.menu.itemName')
                        </th>
                        <th scope="col"
                            class="p-2 text-xs text-center text-gray-500 uppercase dark:text-gray-400">
                            @lang('modules.order.qty')
                        </th>
                        <th scope="col"
                            class="p-2 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">
                            @lang('modules.order.price')
                        </th>
                        <th scope="col"
                            class="p-2 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">
                            @lang('modules.order.amount')
                        </th>
                        @if (user_can('Delete Order') && $orderDetail->status !== 'paid')
                        <th scope="col"
                            class="p-2 text-xs font-medium text-right text-gray-500 uppercase dark:text-gray-400">
                            @lang('app.action')
                        </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700" wire:key='menu-item-list-{{ microtime() }}'>

                    @forelse ($orderDetail->items->load('modifierOptions') as $key => $item)
                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700" wire:key='menu-item-{{ $key . microtime() }}' wire:loading.class.delay='opacity-10'>
                        <td class="flex flex-col p-2 mr-12 lg:min-w-28">
                            <div class="inline-flex items-center text-xs text-gray-900 dark:text-white">
                                {{ $item->menuItem->item_name }}
                            </div>
                            @if (isset($item->menuItemVariation))
                            <div class="inline-flex items-center text-xs text-gray-600 dark:text-white">
                                {{ $item->menuItemVariation->variation }}
                            </div>
                            @endif
                            @if ($item->modifierOptions->isNotEmpty())
                                <div class="mt-1 text-xs text-gray-600 dark:text-white">
                                    @foreach ($item->modifierOptions as $modifier)
                                        <div class="flex items-center justify-between text-xs mb-1 py-0.5 px-1 border-l-2 border-blue-500 bg-gray-200 dark:bg-gray-900 rounded-md">
                                            <span class="text-gray-900 dark:text-white">{{ $modifier->name }}</span>
                                            <span class="text-gray-600 dark:text-gray-300">{{ currency_format($modifier->price) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="p-2 text-base text-center text-gray-900 dark:text-gray-200 whitespace-nowrap">
                            {{ $item->quantity }}
                        </td>

                        @php
                            $displayPrice = $this->getItemDisplayPrice($key);
                        @endphp

                        <td class="p-2 text-xs font-medium text-right text-gray-700 whitespace-nowrap dark:text-white">
                            {{ currency_format($displayPrice, restaurant()->currency_id) }}
                        </td>
                        <td class="p-2 text-xs font-medium text-right text-gray-900 whitespace-nowrap dark:text-white">
                            {{ currency_format($item->amount + $item->modifierOptions->sum('price')) }}
                        </td>
                        @if (user_can('Delete Order') && $orderDetail->status !== 'paid')
                        <td class="p-2 text-right whitespace-nowrap">
                            <button class="p-2 text-gray-800 border rounded dark:text-gray-400 dark:border-gray-500 hover:bg-gray-200 dark:hover:bg-gray-900/20" wire:click="deleteOrderItems('{{ $item->id }}')">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9 2a1 1 0 0 0-.894.553L7.382 4H4a1 1 0 0 0 0 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V6a1 1 0 1 0 0-2h-3.382l-.724-1.447A1 1 0 0 0 11 2zM7 8a1 1 0 0 1 2 0v6a1 1 0 1 1-2 0zm5-1a1 1 0 0 0-1 1v6a1 1 0 1 0 2 0V8a1 1 0 0 0-1-1" clip-rule="evenodd"/></svg>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                        <td class="p-2 space-x-6 text-gray-800 dark:text-gray-200" colspan="5">
                            @lang('messages.noItemAdded')
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div>
            <div class="w-full h-auto p-4 mt-3 space-y-4 text-center rounded select-none bg-gray-50 dark:bg-gray-700">
                <div class="flex justify-between text-sm text-gray-500 dark:text-neutral-400">
                    <div>
                        @lang('modules.order.totalItem')
                    </div>
                    <div>
                        {{ count($orderDetail->items) }}
                    </div>
                </div>
                <div class="flex justify-between text-sm text-gray-500 dark:text-neutral-400">
                    <div>
                        @lang('modules.order.subTotal')
                    </div>
                    <div>
                        {{ currency_format($orderDetail->sub_total, restaurant()->currency_id) }}
                    </div>
                </div>

                @if (!is_null($orderDetail->discount_amount))
                <div wire:key="discountAmount" class="flex justify-between text-sm text-green-500 dark:text-green-400">
                    <div>
                        @lang('modules.order.discount') @if ($orderDetail->discount_type == 'percent') ({{ rtrim(rtrim($orderDetail->discount_value), '.') }}%) @endif
                    </div>
                    <div>
                        -{{ currency_format($orderDetail->discount_amount, restaurant()->currency_id) }}
                    </div>
                </div>
                @endif

                @foreach ($extraCharges as $charge)
                <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                    <div class="inline-flex items-center gap-x-1">{{ $charge->charge_name }}
                        @if ($charge->charge_type == 'percent')
                            ({{ $charge->charge_value }}%)
                        @endif
                        @if($orderDetail->status !== 'paid' && user_can('Update Order'))
                        <span class="text-red-500 cursor-pointer hover:scale-110 active:scale-100"
                            wire:click="removeExtraCharge('{{ $charge->id }}', '{{ $orderType }}')">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        @endif
                    </div>
                    <div>
                        {{ currency_format($charge->getAmount($subTotal - ($discountAmount ?? 0)), restaurant()->currency_id) }}
                    </div>
                </div>
                @endforeach

                @if ($orderDetail->tip_amount > 0)
                <div class="flex justify-between text-sm text-gray-500 dark:text-neutral-400">
                    <div>
                        @lang('modules.order.tip')
                    </div>
                    <div>
                        {{ currency_format($orderDetail->tip_amount, restaurant()->currency_id) }}
                    </div>
                </div>
                @endif

                @if ($orderType === 'delivery' && !is_null($deliveryFee))
                    <div class="flex justify-between text-sm text-gray-500 dark:text-neutral-400">
                        <div>
                            @lang('modules.delivery.deliveryFee')
                        </div>
                        <div>
                            @if($deliveryFee > 0)
                                {{ currency_format($deliveryFee, restaurant()->currency_id) }}
                            @else
                                <span class="font-medium text-green-500">@lang('modules.delivery.freeDelivery')</span>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($taxMode == 'order')
                    @foreach ($orderDetail->taxes as $item)
                        <div class="flex justify-between text-sm text-gray-500 dark:text-neutral-400">
                            <div>
                                {{ $item->tax->tax_name }} ({{ $item->tax->tax_percent }}%)
                            </div>
                            <div>
                                {{ currency_format(($item->tax->tax_percent / 100) * ($orderDetail->sub_total - ($orderDetail->discount_amount ?? 0)), restaurant()->currency_id) }}
                            </div>
                        </div>
                    @endforeach
                @else
                    @php
                    // Show item-wise tax breakdown using actual order items data
                    $taxTotals = [];
                    foreach ($orderItemTaxDetails as $item) {
                        $qty = $item['qty'] ?? 1;
                        if (!empty($item['tax_breakup'])) {
                            foreach ($item['tax_breakup'] as $taxName => $taxInfo) {
                                if (!isset($taxTotals[$taxName])) {
                                    $taxTotals[$taxName] = [
                                        'percent' => $taxInfo['percent'],
                                        'amount' => 0
                                    ];
                                }
                                $taxTotals[$taxName]['amount'] += $taxInfo['amount'] * $qty;
                            }
                        }
                    }
                @endphp
                @foreach ($taxTotals as $taxName => $taxInfo)
                    <div class="flex justify-between text-gray-500 text-xs dark:text-neutral-400">
                        <div>
                            {{ $taxName }} ({{ $taxInfo['percent'] }}%)
                        </div>
                        <div>
                            {{ currency_format($taxInfo['amount'], restaurant()->currency_id) }}
                        </div>
                    </div>
                @endforeach
                <div class="flex justify-between text-gray-500 text-sm dark:text-neutral-400">
                    <div>
                        @lang('modules.order.totalTax')
                        @if($taxMode === 'item')
                            <span
                                class="ml-2 px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300">
                                @lang(restaurant()->tax_inclusive ? 'modules.settings.taxInclusive' : 'modules.settings.taxExclusive')
                            </span>
                        @endif
                    </div>
                    <div>
                        {{ currency_format($totalTaxAmount, restaurant()->currency_id) }}
                    </div>
                </div>
                @endif

                <div class="flex justify-between font-medium dark:text-neutral-300">
                    <div>
                        @lang('modules.order.total')
                    </div>
                    <div>
                        {{ currency_format($orderDetail->total, restaurant()->currency_id) }}
                    </div>
                </div>
            </div>

            <div class="w-full h-auto pt-3 pb-4 text-center select-none">
                <div class="flex gap-2">

                    @if ($orderDetail->status == 'billed' && user_can('Update Order'))
                    <button class="w-full p-2 text-white bg-green-600 rounded" wire:click='showPayment({{ $orderDetail->id }})'>
                        @lang('modules.order.addPayment')
                    </button>
                    @endif

                    @if($orderDetail->status == 'paid')
                    <button class="inline-flex items-center justify-center w-full p-2 mt-2 text-gray-800 border border-gray-300 rounded dark:border-gray-600 dark:text-gray-200 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 gap-x-1"
                        wire:click="printOrder({{ $orderDetail->id }})" type="button">
                        <svg class="w-6 h-6 text-current" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z"/>
                        </svg>
                        @lang('app.print')
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>
    <x-confirmation-modal wire:model="confirmDeleteModal">
        <x-slot name="title">
            <div class="flex items-center gap-4">

                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">@lang('modules.order.cancelOrder')</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">This action cannot be undone</p>
                </div>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="space-y-6">
                <!-- Warning Message -->
                <div class="p-4 border bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">@lang('modules.order.cancelOrderMessage')</p>
                            <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">Please select a reason for cancellation</p>
                        </div>
                    </div>
                </div>

                <!-- Reason Selection -->
                 <div>
                <x-label for="cancelReason" value="{{ __('modules.settings.selectCancelReason') }}" class="text-sm font-medium text-gray-700 dark:text-gray-200" />
                <x-select id="cancelReason" class="block w-full mt-2" wire:model="cancelReason">
                    <option value="">{{ __('modules.settings.selectCancelReason') }}</option>
                    @foreach ($cancelReasons as $reason)
                        <option value="{{ $reason->id }}">{{ $reason->reason }}</option>
                    @endforeach
                </x-select>
                <x-input-error for="cancelReason" class="mt-2" />
            </div>

                <!-- Custom Reason Textarea -->
               <textarea
                            wire:model.defer="cancelReasonText"
                            id="cancelReasonText"
                            rows="4"
                            class="block w-full px-4 py-3 transition-all duration-200 border-2 border-gray-300 shadow-sm resize-none dark:border-gray-600 rounded-xl focus:ring-2 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="@lang('modules.settings.enterCancelReason')"
                        ></textarea>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('confirmDeleteModal', false)" wire:loading.attr="disabled">
                {{ __('app.cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-3" wire:click="cancelOrder" wire:loading.attr="disabled">
                @lang('modules.order.cancelOrder')
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

</div>
{{-- href="{{ route('orders.print', $orderDetail->id) }}" --}}
