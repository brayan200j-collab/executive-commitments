<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FilamentPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/meetings')->assertRedirect('/admin/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertSuccessful();
    }

    public function test_authenticated_user_can_view_dashboard_with_widgets(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertSuccessful();
    }

    public function test_dashboard_renders_with_seeded_data(): void
    {
        $user = User::factory()->create();
        \App\Models\Commitment::factory()->count(3)->create();
        \App\Models\Risk::factory()->critical()->create();

        $this->actingAs($user)->get('/admin')->assertSuccessful();
    }

    #[DataProvider('resourceIndexPages')]
    public function test_authenticated_user_can_view_resource_index(string $path): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get($path)->assertSuccessful();
    }

    #[DataProvider('resourceIndexPages')]
    public function test_authenticated_user_can_view_resource_create_page(string $path): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get($path.'/create')->assertSuccessful();
    }

    public static function resourceIndexPages(): array
    {
        return [
            'reuniones' => ['/admin/meetings'],
            'compromisos' => ['/admin/commitments'],
            'riesgos' => ['/admin/risks'],
        ];
    }
}
