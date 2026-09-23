<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\HelpCenterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HelpCenterTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function help_center_index_is_accessible()
    {
        $user = User::factory()->make(['first_name' => 'John', 'last_name' => 'Doe']);

        $response = $this->actingAs($user)->get(route('help.index'));

        $response->assertStatus(200);
        $response->assertSee(__('ui.user_documentation_hub') ?? 'User Documentation Hub');
    }

    #[Test]
    public function help_center_service_parses_markdown_articles()
    {
        $service = app(HelpCenterService::class);
        $articles = $service->getArticles('en');

        $this->assertNotEmpty($articles);
        $this->assertArrayHasKey('title', $articles[0]);
        $this->assertArrayHasKey('category', $articles[0]);
    }

    #[Test]
    public function contextual_help_api_returns_article_for_route()
    {
        $user = User::factory()->make(['first_name' => 'John', 'last_name' => 'Doe']);

        $response = $this->actingAs($user)->get(route('help.api.contextual', ['route_name' => 'queue.index']));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('article.category', 'Queue Management');
    }

    #[Test]
    public function help_search_api_returns_results()
    {
        $user = User::factory()->make(['first_name' => 'John', 'last_name' => 'Doe']);

        $response = $this->actingAs($user)->get(route('help.api.search', ['q' => 'ticket']));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}
