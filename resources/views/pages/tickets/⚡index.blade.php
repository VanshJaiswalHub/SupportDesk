<?php

use App\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Tickets')] class extends Component {
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Ticket::class);
    }

    #[Computed]
    public function tickets()
    {
        $user = auth()->user();

        return Ticket::with(['category', 'customer'])
            ->when(! $user->isAgent(), fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->paginate(10);
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            'open' => 'blue',
            'in_progress' => 'amber',
            'resolved' => 'green',
            default => 'zinc',
        };
    }

    public function priorityColor(string $priority): string
    {
        return match ($priority) {
            'high' => 'red',
            'medium' => 'yellow',
            default => 'zinc',
        };
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">{{ __('Tickets') }}</flux:heading>
    <flux:subheading>
        {{ auth()->user()->isAgent() ? __('All tickets') : __('Your tickets') }}
    </flux:subheading>

    @if ($this->tickets->isEmpty())
        <flux:text class="mt-6">{{ __('No tickets yet.') }}</flux:text>
    @else
        <flux:table :paginate="$this->tickets" class="mt-6">
            <flux:table.columns>
                <flux:table.column>{{ __('Subject') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                @if (auth()->user()->isAgent())
                    <flux:table.column>{{ __('Customer') }}</flux:table.column>
                @endif
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Priority') }}</flux:table.column>
                <flux:table.column>{{ __('Created') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->tickets as $ticket)
                    <flux:table.row :key="$ticket->id">
                        <flux:table.cell>{{ $ticket->subject }}</flux:table.cell>
                        <flux:table.cell>{{ $ticket->category->name }}</flux:table.cell>
                        @if (auth()->user()->isAgent())
                            <flux:table.cell>{{ $ticket->customer->name }}</flux:table.cell>
                        @endif
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$this->statusColor($ticket->status)">
                                {{ str($ticket->status)->replace('_', ' ')->title() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$this->priorityColor($ticket->priority)">
                                {{ str($ticket->priority)->title() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $ticket->created_at->diffForHumans() }}</flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</section>