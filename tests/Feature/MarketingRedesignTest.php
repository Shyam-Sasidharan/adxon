<?php

namespace Tests\Feature;

use App\Support\AdxonContent;
use Mockery;
use Tests\TestCase;

class MarketingRedesignTest extends TestCase
{
    public function test_homepage_renders_sections_and_local_assets(): void
    {
        $response = $this->get('/')->assertOk();
        foreach (['top', 'about', 'services', 'solutions', 'work', 'process', 'pricing', 'blog', 'contact'] as $section) {
            $response->assertSee('id="'.$section.'"', false);
        }
        foreach (['studio.jpg', 'interiors.jpg', 'architecture.jpg', 'food.jpg'] as $image) {
            $this->assertNotFalse(getimagesize(public_path('assets/'.$image)));
        }
        $response->assertSee('aria-controls="panel-content"', false);
    }

    public function test_case_study_and_article_routes_use_cms_content(): void
    {
        $content = (new AdxonContent)->all();
        $content['portfolio'] = [['title' => 'Test case study', 'category' => 'Retail', 'summary' => 'Project summary', 'metric' => 'Measured result', 'challenge' => 'A clear challenge', 'strategy' => 'A focused strategy']];
        $content['blogs'] = [['title' => 'Test article', 'category' => 'SEO', 'excerpt' => 'Opening paragraph', 'body' => 'Full article content']];
        $this->contentService($content);
        $this->get(route('site.detail', ['kind' => 'portfolio', 'index' => 0]))->assertOk()->assertSee('A focused strategy');
        $this->get(route('site.detail', ['kind' => 'blogs', 'index' => 0]))->assertOk()->assertSee('Full article content');
        $this->get('/insights/portfolio/99999')->assertNotFound();
        $this->get('/insights/enquiries/0')->assertNotFound();
    }

    public function test_typography_save_preserves_other_content(): void
    {
        $data = ['settings' => ['brand' => 'Existing brand'], 'seo' => ['title' => 'Existing title'], 'hero' => ['headline' => 'Existing headline']];
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldReceive('all')->once()->andReturn($data);
        $service->shouldReceive('save')->once()->withArgs(fn (array $saved) => $saved['settings']['font_family'] === 'georgia'
            && $saved['settings']['font_size'] === '20'
            && $saved['settings']['font_style'] === 'italic'
            && $saved['settings']['brand'] === 'Existing brand'
            && $saved['seo'] === $data['seo']
            && $saved['hero'] === $data['hero']
        );
        $this->app->instance(AdxonContent::class, $service);
        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
            ->post(route('admin.settings.save'), ['font_family' => 'georgia', 'font_size' => '20', 'font_style' => 'italic'])
            ->assertRedirect(route('admin.settings'))->assertSessionHasNoErrors();
    }

    public function test_invalid_typography_and_links_cannot_be_saved(): void
    {
        $this->partialMock(AdxonContent::class)->shouldNotReceive('save');
        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
            ->post(route('admin.settings.save'), ['font_family' => '</style>', 'font_size' => 100, 'font_style' => 'unknown', 'instagram_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors(['font_family', 'font_size', 'font_style', 'instagram_url']);
    }

    public function test_saved_fonts_render_and_untrusted_content_is_escaped(): void
    {
        $content = (new AdxonContent)->all();
        $content['settings']['font_family'] = 'georgia';
        $content['settings']['font_size'] = 20;
        $content['settings']['font_style'] = 'italic';
        $content['hero']['headline'] = '<script>bad()</script>';
        $this->contentService($content);
        $this->get('/')->assertOk()->assertSee('font-family: Georgia, "Times New Roman", serif;', false)
            ->assertSee('font-size: 20px;', false)->assertSee('font-style: italic;', false)
            ->assertDontSee('<script>bad()</script>', false);
    }

    public function test_new_cms_collections_render(): void
    {
        foreach (['client_logos', 'results', 'process', 'stats', 'portfolio', 'blogs'] as $collection) {
            $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
                ->get(route('admin.collection', $collection))->assertOk();
        }
    }

    public function test_consultation_preserves_the_existing_enquiry_flow(): void
    {
        $this->mock(AdxonContent::class)->shouldReceive('appendEnquiry')->once()->withArgs(fn ($enquiry) => $enquiry['name'] === 'Test client' && $enquiry['message'] === 'A new campaign');
        $this->post(route('enquiry.store'), ['name' => 'Test client', 'email' => 'client@example.test', 'message' => 'A new campaign'])->assertRedirect(route('home', ['sent' => 1]).'#contact');
    }

    private function contentService(array $data): void
    {
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldReceive('all')->andReturn($data);
        $this->app->instance(AdxonContent::class, $service);
    }
}
