<?php

use App\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Ticket')] class extends Component {
    public Ticket $ticket;

    public string $body = '';

    public function mount(Ticket $ticket): void
    {
        $this->authorize('view', $ticket);

        $this->ticket = $ticket->load(['category', 'customer', 'assignedAgent']);
    }

    #[Computed]
    public function replies()
    {
        return $this->ticket->replies()->with('author')->oldest()->get();
    }

    public function sendReply(): void
    {
        $this->authorize('reply', $this->ticket);

        $validated = $this->validate([
            'body' => ['required', 'string', 'min:2'],
        ]);

        $this->ticket->replies()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        $this->reset('body');
        unset($this->replies);
    }

    public function assignToMe(): void
    {
        $this->authorize('update', $this->ticket);

        $this->ticket->update(['assigned_to' => auth()->id()]);
        $this->ticket->load('assignedAgent');

        Flux::toast(variant: 'success', text: __('Ticket assigned to you.'));
    }

    public function changeStatus(string $status): void
    {
        $this->authorize('update', $this->ticket);

        if (! $this->ticket->canMoveTo($status)) {
            Flux::toast(variant: 'danger', text: __('That status change is not allowed.'));

            return;
        }

        $this->ticket->update(['status' => $status]);

        Flux::toast(variant: 'success', text: __('Status updated.'));
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

<section class="w-full max-w-3xl">
    <flux:button variant="subtle" size="sm" icon="arrow-left" :href="route('tickets.index')" wire:navigate>
        {{ __('Back to tickets') }}
    </flux:button>

    <flux:heading size="xl" class="mt-4">{{ $ticket->subject }}</flux:heading>

    <div class="mt-2 flex flex-wrap items-center gap-2">
        <flux:badge size="sm" :color="$this->statusColor($ticket->status)">
            {{ str($ticket->status)->replace('_', ' ')->title() }}
        </flux:badge>
        <flux:badge size="sm" :color="$this->priorityColor($ticket->priority)">
            {{ str($ticket->priority)->title() }}
        </flux:badge>
        <flux:text>{{ $ticket->category->name }}</flux:text>
    </div>

    <flux:text class="mt-2">
        {{ __('Opened by :name :time', ['name' => $ticket->customer->name, 'time' => $ticket->created_at->diffForHumans()]) }}
        &middot;
        {{ $ticket->assignedAgent ? __('Assigned to :name', ['name' => $ticket->assignedAgent->name]) : __('Unassigned') }}
    </flux:text>

    @can('update', $ticket)
        <div class="mt-4 flex flex-wrap items-center gap-2">
            @if ($ticket->assigned_to !== auth()->id())
                <flux:button size="sm" wire:click="assignToMe">{{ __('Assign to me') }}</flux:button>
            @endif

            @foreach ($ticket->allowedNextStatuses() as $next)
                <flux:button size="sm" wire:key="status-{{ $next }}" wire:click="changeStatus('{{ $next }}')">
                    {{ __('Mark as :status', ['status' => str($next)->replace('_', ' ')->title()]) }}
                </flux:button>
            @endforeach
        </div>
    @endcan

    <div class="mt-6 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:text class="whitespace-pre-line">{{ $ticket->description }}</flux:text>
    </div>

    <flux:heading class="mt-8">{{ __('Replies') }}</flux:heading>

    @forelse ($this->replies as $reply)
        <div wire:key="reply-{{ $reply->id }}" class="mt-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <flux:heading size="sm">{{ $reply->author->name }}</flux:heading>
                    @if ($reply->author->isAgent())
                        <flux:badge size="sm" color="purple">{{ __('Agent') }}</flux:badge>
                    @endif
                </div>
                <flux:text size="sm">{{ $reply->created_at->diffForHumans() }}</flux:text>
            </div>
            <flux:text class="mt-2 whitespace-pre-line">{{ $reply->body }}</flux:text>
        </div>
    @empty
        <flux:text class="mt-3">{{ __('No replies yet.') }}</flux:text>
    @endforelse

    @can('reply', $ticket)
        <form wire:submit="sendReply" class="mt-6 space-y-4">
            <flux:textarea wire:model="body" :label="__('Your reply')" rows="4" />
            <flux:button variant="primary" type="submit">{{ __('Send reply') }}</flux:button>
        </form>
    @else
        <flux:text class="mt-6">{{ __('This ticket is closed, so new replies are turned off.') }}</flux:text>
    @endcan
</section>