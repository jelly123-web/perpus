<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanStockSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_stock_decreases_when_borrowed_blocks_when_empty_and_returns_when_book_is_returned(): void
    {
        $permission = Permission::query()->updateOrCreate([
            'name' => 'manage_loans',
        ], [
            'label' => 'Peminjaman Buku',
        ]);

        $role = Role::query()->updateOrCreate([
            'name' => 'petugas',
        ], [
            'label' => 'Petugas',
        ]);

        $role->permissions()->sync([$permission->id]);

        $officer = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $memberA = User::factory()->create();
        $memberB = User::factory()->create();
        $memberC = User::factory()->create();

        $book = Book::query()->create([
            'title' => 'Bahasa Indonesia',
            'author' => 'Guru Bahasa',
            'stock_total' => 2,
            'stock_available' => 2,
        ]);

        $loanA = Loan::query()->create([
            'book_id' => $book->id,
            'member_id' => $memberA->id,
            'processed_by' => $officer->id,
            'borrowed_at' => '2026-04-27',
            'due_at' => '2026-04-28',
            'status' => 'requested',
        ]);

        $this->actingAs($officer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->put(route('admin.loans.update', $loanA, false), [
                'status' => 'borrowed',
            ])
            ->assertOk();

        $book->refresh();
        $this->assertSame(1, (int) $book->stock_available);

        $loanB = Loan::query()->create([
            'book_id' => $book->id,
            'member_id' => $memberB->id,
            'processed_by' => $officer->id,
            'borrowed_at' => '2026-04-27',
            'due_at' => '2026-04-28',
            'status' => 'requested',
        ]);

        $this->actingAs($officer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->put(route('admin.loans.update', $loanB, false), [
                'status' => 'borrowed',
            ])
            ->assertOk();

        $book->refresh();
        $this->assertSame(0, (int) $book->stock_available);

        $loanC = Loan::query()->create([
            'book_id' => $book->id,
            'member_id' => $memberC->id,
            'processed_by' => $officer->id,
            'borrowed_at' => '2026-04-27',
            'due_at' => '2026-04-28',
            'status' => 'requested',
        ]);

        $this->actingAs($officer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->put(route('admin.loans.update', $loanC, false), [
                'status' => 'borrowed',
            ])
            ->assertStatus(422);

        $book->refresh();
        $this->assertSame(0, (int) $book->stock_available);

        $this->actingAs($officer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->put(route('admin.loans.update', $loanA, false), [
                'status' => 'returned',
            ])
            ->assertOk();

        $book->refresh();
        $this->assertSame(1, (int) $book->stock_available);

        $this->actingAs($officer)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->put(route('admin.loans.update', $loanB, false), [
                'status' => 'returned',
            ])
            ->assertOk();

        $book->refresh();
        $this->assertSame(2, (int) $book->stock_available);
    }
}
