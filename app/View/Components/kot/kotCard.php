<?php

namespace App\View\Components\kot;

use Closure;
use Illuminate\View\Component;
use App\Models\KotCancelReason;
use Illuminate\Contracts\View\View;

class kotCard extends Component
{
    public $kot;
    public $kotSettings;
    public $printer;
    public $cancelReasons;

    /**
     * Create a new component instance.
     */

    //
    public function __construct($kot, $kotSettings, $cancelReasons = null)
    {
        $this->kot = $kot;
        $this->kotSettings = $kotSettings;
        $this->cancelReasons = $cancelReasons;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {

        return view('components.kot.kot-card');
    }

}
