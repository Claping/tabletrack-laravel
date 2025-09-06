<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\OrderNumberSetting;
use App\Models\Branch;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class OrderSettings extends Component
{
    use LivewireAlert;

    public $activeTab;
    public $branchId;
    public $enableFeature = false;
    public $prefix = 'Order';
    public $digits = 3;
    public $separator = '-';
    public $includeDate = false;
    public $showYear = false;
    public $showMonth = false;
    public $showDay = false;
    public $showTime = false;
    public $resetDaily = false;

    public function mount()
    {
        $this->branchId = branch()->id ?? null;

        if (!$this->branchId) {
            throw new \Exception('No branch found. Please ensure at least one branch exists.');
        }

        $this->loadSettings($this->branchId);
        $this->activeTab = 'prefix';
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveOrderSettings()
    {
        // Automatically enable includeDate if any date part is enabled
        if ($this->showYear || $this->showMonth || $this->showDay || $this->showTime) {
            $this->includeDate = true;
        } else {
            $this->includeDate = false;
        }

        $this->validate([
            'branchId' => 'required|exists:branches,id',
            'prefix' => 'required|string|max:50',
            'digits' => 'required|integer|min:1|max:10',
            'separator' => ['required', 'string', 'size:1', 'regex:/[^a-zA-Z0-9]/'],
        ]);

        OrderNumberSetting::updateOrCreate(
            ['branch_id' => $this->branchId],
            [
                'enable_feature' => $this->enableFeature,
                'prefix' => $this->prefix,
                'digits' => $this->digits,
                'separator' => $this->separator,
                'include_date' => $this->includeDate,
                'show_year' => $this->showYear,
                'show_month' => $this->showMonth,
                'show_day' => $this->showDay,
                'show_time' => $this->showTime,
                'reset_daily' => $this->resetDaily,
            ]
        );

        $this->alert('success', __('messages.settingsUpdated'), [
            'position' => 'top-end',
            'toast' => true,
        ]);
    }

    public function getPreviewProperty()
    {
        // Automatically enable includeDate if any date part is enabled
        $includeDate = ($this->showYear || $this->showMonth || $this->showDay || $this->showTime);

        if (!$this->enableFeature) {
            return __('modules.order.customPrefixFeatureDisabled');
        }

        $prefix = $this->prefix ?: 'Order';
        $seq = str_pad('1', (int) $this->digits, '0', STR_PAD_LEFT);
        $sep = $this->separator ?: '-';

        $dateParts = [];
        if ($includeDate) {
            if ($this->showYear) {
                $dateParts[] = now()->format('Y');
            }
            if ($this->showMonth) {
                $dateParts[] = now()->format('m');
            }
            if ($this->showDay) {
                $dateParts[] = now()->format('d');
            }
            if ($this->showTime) {
                $dateParts[] = now()->format('Hi');
            }
        }

        $parts = array_filter([
            $prefix,
            $dateParts ? implode('', $dateParts) : null,
            $seq
        ]);

        return implode($sep, $parts);
    }

    public function getBranchesProperty()
    {
        return Branch::select('id', 'name')->get();
    }

    protected function loadSettings()
    {
        $settings = OrderNumberSetting::where('branch_id', $this->branchId)->first();

        if ($settings) {
            $this->enableFeature = $settings->enable_feature;
            $this->prefix = $settings->prefix;
            $this->digits = $settings->digits;
            $this->separator = $settings->separator;
            $this->includeDate = $settings->include_date;
            $this->showYear = $settings->show_year;
            $this->showMonth = $settings->show_month;
            $this->showDay = $settings->show_day;
            $this->showTime = $settings->show_time;
            $this->resetDaily = $settings->reset_daily;
        } else {
            $this->enableFeature = false;
            $this->prefix = 'Order';
            $this->digits = 3;
            $this->separator = '-';
            $this->includeDate = false;
            $this->showYear = false;
            $this->showMonth = false;
            $this->showDay = false;
            $this->showTime = false;
            $this->resetDaily = false;
        }
    }

    public function render()
    {
        return view('livewire.settings.order-settings');
    }
}
