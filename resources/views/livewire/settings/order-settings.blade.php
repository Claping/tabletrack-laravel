<div>
    <div class="p-4 mx-4 mb-3 bg-white rounded-lg border border-gray-200 shadow-sm dark:border-gray-700 sm:p-6 dark:bg-gray-800">
        <h3 class="mb-3 text-xl font-semibold dark:text-white">@lang('modules.settings.orderSetting')</h3>
        <x-help-text class="mb-6">@lang('modules.settings.orderSettingsHelp')</x-help-text>


        <!-- tabs -->
        <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
            <ul class="flex flex-wrap items-center -mb-px">
                <li class="me-2">
                    <span wire:click="$set('activeTab', 'prefix')"
                        @class([
                            'inline-flex items-center gap-x-1 cursor-pointer select-none p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300',
                            'border-transparent'=> $activeTab != 'prefix',
                            'active border-skin-base dark:text-skin-base dark:border-skin-base text-skin-base' => $activeTab == 'prefix',
                        ])>
                        #
                        @lang('modules.order.prefixSettings')
                    </span>
                </li>
            </ul>
        </div>

        @if($activeTab === 'prefix')
        <!-- Prefix Settings Form -->
        <form wire:submit="saveOrderSettings" class="p-4 space-y-6 md:p-6">
            <div class="flex gap-x-3 items-center p-4 bg-gray-100 rounded-lg shadow-sm dark:bg-gray-700">
                <x-checkbox name="enableFeature" id="enableFeature" wire:model.live='enableFeature'
                    class="mr-4" />
                <div class="flex-1">
                    <x-label for="enableFeature" :value="__('modules.order.enableOrderPrefix')" class="!mb-1" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('modules.order.enableOrderPrefixDescription')</p>
                </div>
            </div>

            @if($enableFeature)
                <!-- Compact Row for Prefix, Separator, Digits -->
                <div class="grid grid-cols-1 gap-4 mt-2 md:grid-cols-2">
                    <div>
                        <x-label for="prefix" class="mb-1" :value="__('modules.order.customPrefix')" />
                        <x-input id="prefix" type="text" class="w-full" wire:model.live="prefix" />
                        <x-input-error for="prefix" class="mt-1 text-xs" />
                        <x-help-text class="mt-1 text-xs">@lang('modules.order.branchPrefixHelp')</x-help-text>
                    </div>
                    <div class="flex gap-2">
                        <div class="w-1/2">
                            <x-label for="separator" class="mb-1" :value="__('modules.order.separator')" />
                            <x-input id="separator" type="text" maxlength="1" pattern="[^a-zA-Z0-9]" oninput="this.value = this.value.replace(/[a-zA-Z0-9]/g, '')" class="w-full" wire:model.live="separator" />
                            <x-input-error for="separator" class="mt-1 text-xs" />
                        </div>
                        <div class="w-1/2">
                            <x-label for="digits" class="mb-1" :value="__('modules.order.digits')" />
                            <x-input id="digits" type="number" min="1" max="10" class="w-full" wire:model.live="digits" />
                            <x-input-error for="digits" class="mt-1 text-xs" />
                        </div>
                    </div>
                </div>

                <!-- Date Parts Simple Row -->
                <div class="mt-3">
                    <x-label :value="__('modules.order.dateParts')" class="mb-2" />
                    <div class="flex flex-wrap gap-4 mt-2">
                        <label for="showYear" class="flex gap-2 items-center px-4 py-2 bg-white rounded-lg border border-gray-200 shadow-sm transition cursor-pointer dark:bg-gray-900 dark:border-gray-700 hover:border-indigo-400">
                            <x-checkbox wire:model.live="showYear" id="showYear" class="accent-indigo-600" />
                            <span class="text-gray-700 dark:text-gray-200">@lang('modules.order.showYear')</span>
                        </label>
                        <label for="showMonth" class="flex gap-2 items-center px-4 py-2 bg-white rounded-lg border border-gray-200 shadow-sm transition cursor-pointer dark:bg-gray-900 dark:border-gray-700 hover:border-indigo-400">
                            <x-checkbox wire:model.live="showMonth" id="showMonth" class="accent-indigo-600" />
                            <span class="text-gray-700 dark:text-gray-200">@lang('modules.order.showMonth')</span>
                        </label>
                        <label for="showDay" class="flex gap-2 items-center px-4 py-2 bg-white rounded-lg border border-gray-200 shadow-sm transition cursor-pointer dark:bg-gray-900 dark:border-gray-700 hover:border-indigo-400">
                            <x-checkbox wire:model.live="showDay" id="showDay" class="accent-indigo-600" />
                            <span class="text-gray-700 dark:text-gray-200">@lang('modules.order.showDay')</span>
                        </label>
                        <label for="showTime" class="flex gap-2 items-center px-4 py-2 bg-white rounded-lg border border-gray-200 shadow-sm transition cursor-pointer dark:bg-gray-900 dark:border-gray-700 hover:border-indigo-400">
                            <x-checkbox wire:model.live="showTime" id="showTime" class="accent-indigo-600" />
                            <span class="text-gray-700 dark:text-gray-200">@lang('modules.order.showTime')</span>
                        </label>
                    </div>
                </div>

                <!-- Reset Daily Setting -->
                <div class="mt-4">
                    <div class="flex gap-x-3 items-center p-4 bg-gray-100 rounded-lg shadow-sm dark:bg-gray-700">
                        <x-checkbox name="resetDaily" id="resetDaily" wire:model.live='resetDaily'
                            class="mr-4 accent-indigo-600" />
                        <div class="flex-1">
                            <x-label for="resetDaily" :value="__('modules.order.resetDaily')" class="!mb-1" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('modules.order.resetDailyHelp')</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Preview Section -->
            <div class="flex flex-col justify-center items-center p-4 space-y-4 bg-indigo-50 rounded-2xl border-2 border-indigo-200 border-dashed dark:bg-gray-950 md:p-6 dark:border-gray-700">
                <span class="text-xl font-bold text-gray-500 dark:text-gray-400">@lang('modules.order.preview')</span>
                <span class="font-mono text-4xl font-extrabold tracking-wider text-indigo-700 dark:text-indigo-400">
                    {{ $this->preview }}
                </span>
                <p class="max-w-sm text-sm text-center text-gray-500 dark:text-gray-400">
                    @lang('modules.order.previewHelp')
                </p>
            </div>

            <!-- Save Button -->
            <div class="pt-4">
                <x-button type="submit" wire:loading.attr="disabled" wire:target='saveOrderSettings' class="inline-flex gap-x-2 items-center">
                    <!-- Optimized right arrow icon -->
                    <div>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" wire:loading.remove wire:target='saveOrderSettings'>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg aria-hidden="true" wire:loading wire:target='saveOrderSettings' class="w-4 h-4 text-gray-200 animate-spin dark:text-gray-600 fill-skin-base" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/><path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/></svg>
                    </div>
                    <span>
                        @lang('modules.settings.save')
                    </span>
                </x-button>
            </div>
        </form>
        @endif
    </div>
</div>
