<?php

use App\Models\Category;
use App\Models\Ticket;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New ticket')] class extends Component {
    public string $subject = '';
    public string $description = '';
    public string $category_id = '';
    public string $priority = 'medium';

    public function mount(): void
    {
        $this->authorize('create', Ticket::class);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function save(): void
    {
        $this->authorize('create', Ticket::class);

        $validated = $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        Ticket::create([...$validated, 'user_id' => auth()->id()]);

        Flux::toast(variant: 'success', text: __('Ticket created.'));

        $this->redirectRoute('tickets.index', navigate: true);
    }
}; ?>

<section class="w-full max-w-xl">
    <flux:heading size="xl">{{ __('New ticket') }}</flux:heading>
    <flux:subheading>{{ __('Describe your problem and we will get back to you.') }}</flux:subheading>

    <form wire:submit="save" class="mt-6 space-y-6">
        <flux:input wire:model="subject" :label="__('Subject')" type="text" autofocus />

        <flux:select wire:model="category_id" :label="__('Category')" :placeholder="__('Choose a category')">
            @foreach ($this->categories as $category)
                <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="priority" :label="__('Priority')">
            <flux:select.option value="low">{{ __('Low') }}</flux:select.option>
            <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
            <flux:select.option value="high">{{ __('High') }}</flux:select.option>
        </flux:select>

        <flux:textarea wire:model="description" :label="__('Description')" rows="6" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">{{ __('Create ticket') }}</flux:button>
            <flux:button variant="ghost" :href="route('tickets.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</section>