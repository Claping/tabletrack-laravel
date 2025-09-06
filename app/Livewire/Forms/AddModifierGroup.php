<?php

namespace App\Livewire\Forms;

use Livewire\Component;
use App\Models\MenuItem;
use App\Models\ItemModifier;
use App\Models\ModifierGroup;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class AddModifierGroup extends Component
{
    use LivewireAlert;

    public $name;
    public $description;

    public $modifierOptions = [];
    public $modifierOptionInput = [];
    public $modifierOptionName = [];
    public $menuItems;
    public $selectedMenuItems = [];
    public $search = '';
    public $isOpen = false;
    public $allMenuItems;

    public $languages = [];
    public $translationNames = [];
    public $translationDescriptions = [];
    public $currentLanguage;
    public $globalLocale;

    protected function rules()
    {
        $baseRules = [
            'description' => 'nullable',
            'modifierOptions.*.price' => 'required|numeric|min:0',
            'modifierOptions.*.is_available' => 'required|boolean',
        ];

        // Add dynamic validation rules for the global locale
        $baseRules['translationNames.' . $this->globalLocale] = 'required|max:255';
        $baseRules['modifierOptions.*.name.' . $this->globalLocale] = 'required|max:255';

        return $baseRules;
    }

    protected function messages()
    {
        return [
            'name.*.max' => 'The name may not be greater than 255 characters.',
            'modifierOptions.*.name.required' => 'Modifier option must have a name.',
            'modifierOptions.*.price.required' => 'Modifier option must have a price.',
            'modifierOptions.*.price.numeric' => 'Modifier option price must be a number.',
            'modifierOptions.*.price.min' => 'Modifier option price must be at least 0.',
            'translationNames.' . $this->globalLocale . '.required' => __('validation.modifierGroupNameRequired', ['language' => $this->languages[$this->globalLocale]]),
            'modifierOptions.*.name.' . $this->globalLocale . '.required' => __('validation.modifierOptionNameRequired', ['language' => $this->languages[$this->globalLocale]]),
        ];
    }

    public function mount()
    {
        $this->resetValidation();
        $this->languages = languages()->pluck('language_name', 'language_code')->toArray();
        $this->globalLocale = global_setting()->locale;
        $this->currentLanguage = $this->globalLocale;

        // Initialize translation arrays
        $languageKeys = array_keys($this->languages);
        $this->translationNames = array_fill_keys($languageKeys, '');
        $this->translationDescriptions = array_fill_keys($languageKeys, '');

        // Add first modifier option
        $this->modifierOptions[] = $this->newModifierOption();

        // Load menu items
        $this->menuItems = $this->allMenuItems = MenuItem::all();

        // Initialize modifier options
        $this->syncModifierOptions();
    }

    public function newModifierOption()
    {
        $langs = array_keys($this->languages);
        return [
            'id' => uniqid(),
            'name' => array_fill_keys($langs, ''),
            'price' => 0,
            'is_available' => true,
            'sort_order' => 0,
        ];
    }

    // Combine both sync methods for better maintainability
    protected function syncModifierOptions()
    {
        // Sync the translations first
        $this->name = $this->translationNames[$this->currentLanguage] ?? '';
        $this->description = $this->translationDescriptions[$this->currentLanguage] ?? '';

        // Then sync modifier options
        foreach ($this->modifierOptions as $index => $option) {
            // Sync inputs for all languages
            foreach (array_keys($this->languages) as $lang) {
                $this->modifierOptionInput[$index][$lang] = $option['name'][$lang] ?? '';
            }

            // Set the current language name
            $this->modifierOptionName[$index] = $this->modifierOptionInput[$index][$this->currentLanguage] ?? '';
        }
    }

    public function updateTranslation()
    {
        $this->translationNames[$this->currentLanguage] = $this->name;
        $this->translationDescriptions[$this->currentLanguage] = $this->description;
    }

    public function updateModifierOptionTranslation($index)
    {
        $lang = $this->currentLanguage;
        $this->modifierOptionInput[$index][$lang] = $this->modifierOptionName[$index];
        $this->modifierOptions[$index]['name'][$lang] = $this->modifierOptionName[$index];
    }

    public function updatedCurrentLanguage()
    {
        $this->syncModifierOptions();
    }

    public function updatedModifierOptionName($value, $index)
    {
        $lang = $this->currentLanguage;
        $this->modifierOptionInput[$index][$lang] = $value;
        $this->modifierOptions[$index]['name'][$lang] = $value;
    }

    public function addModifierOption()
    {
        $this->modifierOptions[] = $this->newModifierOption();
        $this->syncModifierOptions();
    }

    public function removeModifierOption($index)
    {
        unset($this->modifierOptions[$index], $this->modifierOptionInput[$index], $this->modifierOptionName[$index]);
        $this->modifierOptions = array_values($this->modifierOptions);
        $this->modifierOptionInput = array_values($this->modifierOptionInput);
        $this->modifierOptionName = array_values($this->modifierOptionName);
    }

    public function updateModifierOptionOrder($orderedIds)
    {
        $this->modifierOptions = collect($orderedIds)->map(function ($id) {
            return collect($this->modifierOptions)->firstWhere('id', $id['value']);
        })->toArray();
        $this->syncModifierOptions();
    }

    public function updatedIsOpen($value)
    {
        if (!$value) {
            $this->reset(['search']);
            $this->updatedSearch();
        }
    }

    public function updatedSearch()
    {
        $this->menuItems = $this->search
            ? MenuItem::where('item_name', 'like', '%' . $this->search . '%')->get()
            : $this->allMenuItems;
    }

    public function toggleSelectItem($item)
    {
        $itemId = $item['id'];
        if (($key = array_search($itemId, $this->selectedMenuItems)) !== false) {
            unset($this->selectedMenuItems[$key]);
        } else {
            $this->selectedMenuItems[] = $itemId;
        }
        $this->selectedMenuItems = array_values($this->selectedMenuItems);
    }

    public function submitForm()
    {
        $this->validate($this->rules(), $this->messages());

        // Prepare modifier options with all translations
        foreach ($this->modifierOptions as $index => &$option) {
            foreach (array_keys($this->languages) as $lang) {
                $option['name'][$lang] = $this->modifierOptionInput[$index][$lang] ?? '';
            }
            $option['name'] = array_filter($option['name'], 'trim');
        }

        // Create the modifier group
        $modifierGroup = ModifierGroup::create([
            'name' => $this->translationNames[$this->globalLocale],
            'description' => $this->translationDescriptions[$this->globalLocale],
            'branch_id' => branch()->id,
        ]);


        // Create translations
        $translations = collect($this->translationNames)
            ->filter(fn($name, $locale) => !empty($name) || !empty($this->translationDescriptions[$locale]))
            ->map(fn($name, $locale) => [
                'locale' => $locale,
                'name' => $name,
                'description' => $this->translationDescriptions[$locale]
            ])->values()->all();

        $modifierGroup->translations()->createMany($translations);

        // Create modifier options
        $options = collect($this->modifierOptions)->map(function($option) {
            return [
                'name' => $option['name'],
                'price' => $option['price'],
                'is_available' => $option['is_available'],
                'sort_order' => $option['sort_order'],
            ];
        })->all();

        $modifierGroup->options()->createMany($options);

        // Associate with menu items
        if (!empty($this->selectedMenuItems)) {
            $itemModifiers = collect($this->selectedMenuItems)->map(function($menuItemId) use ($modifierGroup) {
                return [
                    'menu_item_id' => $menuItemId,
                    'modifier_group_id' => $modifierGroup->id,
                ];
            })->all();

            ItemModifier::insert($itemModifiers);
        }

        $this->dispatch('hideAddModifierGroupModal');
        $this->reset(['name', 'description', 'modifierOptions', 'modifierOptionInput', 'modifierOptionName', 'selectedMenuItems', 'search', 'isOpen', 'translationNames', 'translationDescriptions']);

        $this->alert('success', __('messages.ModifierGroupAdded'), [
            'toast' => true,
            'position' => 'top-end',
            'showCancelButton' => false,
            'cancelButtonText' => __('app.close')
        ]);
    }

    public function render()
    {
        $this->syncModifierOptions();
        return view('livewire.forms.add-modifier-group');
    }
}
