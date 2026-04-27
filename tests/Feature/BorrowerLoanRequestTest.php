<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowerLoanRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_borrower_can_request_an_available_book(): void
    {
        $dashboardPermission = Permission::query()->updateOrCreate([
            'name' => 'access_dashboard',
        ], [
            'label' => 'Dashboard',
        ]);

        $historyPermission = Permission::query()->updateOrCreate([
            'name' => 'view_borrower_history',
        ], [
            'label' => 'Riwayat Peminjaman',
        ]);

        $role = Role::query()->updateOrCreate([
            'name' => 'siswa',
        ], [
            'label' => 'Siswa',
        ]);

        $role->permissions()->sync([$dashboardPermission->id, $historyPermission->id]);

        $borrower = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $book = Book::query()->create([
            'title' => 'Bahasa Indonesia',
            'author' => 'Guru Bahasa',
            'stock_total' => 2,
            'stock_available' => 2,
        ]);

        $this->actingAs($borrower)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->post(route('loan-requests.store', [], false), [
                'book_id' => $book->id,
                'borrowed_at' => '2026-04-27',
                'due_at' => '2026-04-28',
                'notes' => 'Pinjam dari dashboard peminjam.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('loans', [
            'book_id' => $book->id,
            'member_id' => $borrower->id,
            'status' => 'requested',
        ]);
    }
}
