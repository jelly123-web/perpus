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

    public function test_chatbot_returns_books_for_borrower_book_request(): void
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

        Book::query()->create([
            'title' => 'Matematika Dasar',
            'author' => 'Ibu Sari',
            'stock_total' => 3,
            'stock_available' => 3,
        ]);

        $this->actingAs($borrower)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->postJson(route('chatbot.respond', [], false), [
                'message' => 'Saya mau pinjam buku matematika',
                'history' => [],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('source', 'borrower_books')
            ->assertJsonPath('book_results.0.title', 'Matematika Dasar');
    }

    public function test_chatbot_handles_bindo_alias_and_extra_text(): void
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

        Book::query()->create([
            'title' => 'Bahasa Indonesia',
            'author' => 'Dra. Yustinah, M.Pd',
            'stock_total' => 8,
            'stock_available' => 8,
        ]);

        $this->actingAs($borrower)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->postJson(route('chatbot.respond', [], false), [
                'message' => 'Saya mau pinjam buku bindo, kira kira buku nya yg mana?',
                'history' => [],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('source', 'borrower_books')
            ->assertJsonPath('book_results.0.title', 'Bahasa Indonesia');
    }

    public function test_chatbot_does_not_show_all_books_for_unmatched_keyword(): void
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

        Book::query()->create([
            'title' => 'Bahasa Indonesia',
            'author' => 'Dra. Yustinah, M.Pd',
            'stock_total' => 8,
            'stock_available' => 8,
        ]);

        Book::query()->create([
            'title' => 'Matematika Dasar',
            'author' => 'Atang Supriadi',
            'stock_total' => 3,
            'stock_available' => 3,
        ]);

        $response = $this->actingAs($borrower)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->postJson(route('chatbot.respond', [], false), [
                'message' => 'saya mau pinjam buku astronomi',
                'history' => [],
            ])
            ->assertOk();

        $payload = $response->json();

        $this->assertSame('borrower_books', $payload['source'] ?? null);
        $this->assertSame([], $payload['book_results'] ?? []);
        $this->assertStringContainsString('belum ada buku yang cocok', mb_strtolower((string) ($payload['reply'] ?? '')));
    }

    public function test_chatbot_shows_all_books_only_when_user_asks_for_all_books(): void
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

        Book::query()->create([
            'title' => 'Bahasa Indonesia',
            'author' => 'Dra. Yustinah, M.Pd',
            'stock_total' => 8,
            'stock_available' => 8,
        ]);

        Book::query()->create([
            'title' => 'Matematika Dasar',
            'author' => 'Atang Supriadi',
            'stock_total' => 3,
            'stock_available' => 3,
        ]);

        $this->actingAs($borrower)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->postJson(route('chatbot.respond', [], false), [
                'message' => 'tampilkan semua buku',
                'history' => [],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('source', 'borrower_books')
            ->assertJsonCount(2, 'book_results');
    }

    public function test_chatbot_strips_common_question_suffixes_from_book_query(): void
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

        Book::query()->create([
            'title' => 'Bahaya Narkoba',
            'author' => 'Setyawati',
            'stock_total' => 10,
            'stock_available' => 10,
        ]);

        $response = $this->actingAs($borrower)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->withHeader('Accept', 'application/json')
            ->postJson(route('chatbot.respond', [], false), [
                'message' => 'buku bahaya narkoba yg man?',
                'history' => [],
            ])
            ->assertOk();

        $payload = $response->json();

        $this->assertSame('borrower_books', $payload['source'] ?? null);
        $this->assertSame('Bahaya Narkoba', $payload['book_results'][0]['title'] ?? null);
        $this->assertStringNotContainsString('tidak ditemukan', mb_strtolower((string) ($payload['reply'] ?? '')));
    }
}
