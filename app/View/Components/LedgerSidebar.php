<?php

namespace App\View\Components;

use App\Models\Ledger;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class LedgerSidebar extends Component
{
    public Collection $accounts;
    public Collection $groupedAccounts;
    public ?int $activeAccountId = null;
    public ?int $activeLedgerId = null;
    public ?string $activeCategoryName = null;

    public function __construct(?int $activeAccountId = null, ?int $activeLedgerId = null)
    {
        $this->activeAccountId = $activeAccountId;
        $this->activeLedgerId = $activeLedgerId;

        $this->resolveActiveItems();

        $this->accounts = LedgerAccount::with(['Ledgers' => function ($q) {
            $q->orderBy('name', 'asc');
        }])
        ->orderBy('name', 'asc')
        ->get();

        // Group accounts by category type
        $grouped = $this->accounts->groupBy(function ($account) {
            $cat = trim((string) $account->type_category);
            return $cat !== '' ? $cat : 'Uncategorized';
        });

        // Sort categories alphabetically with Uncategorized at the end
        $this->groupedAccounts = $grouped->sortBy(function ($items, $key) {
            return $key === 'Uncategorized' ? 'zzzzzz' : strtolower($key);
        });

        // Determine active category name if an account is active
        if ($this->activeAccountId !== null) {
            $activeAccount = $this->accounts->firstWhere('id', $this->activeAccountId);
            if ($activeAccount) {
                $cat = trim((string) $activeAccount->type_category);
                $this->activeCategoryName = $cat !== '' ? $cat : 'Uncategorized';
            }
        }
    }

    protected function resolveActiveItems(): void
    {
        if ($this->activeAccountId !== null || $this->activeLedgerId !== null) {
            return;
        }

        $path = request()->path();

        if (preg_match('#^ledger/account/(\d+)#', $path, $matches)) {
            $this->activeAccountId = (int) $matches[1];
        } elseif (preg_match('#^ledger/entry/(\d+)#', $path, $matches)) {
            $entry = LedgerEntry::with('Ledger')->find($matches[1]);
            if ($entry && $entry->Ledger) {
                $this->activeLedgerId = $entry->Ledger->id;
                $this->activeAccountId = $entry->Ledger->ledger_account_id;
            }
        } elseif (preg_match('#^ledger/(\d+)#', $path, $matches)) {
            $ledger = Ledger::find($matches[1]);
            if ($ledger) {
                $this->activeLedgerId = $ledger->id;
                $this->activeAccountId = $ledger->ledger_account_id;
            }
        }
    }

    public function render(): View
    {
        return view('components.ledger-sidebar');
    }
}
