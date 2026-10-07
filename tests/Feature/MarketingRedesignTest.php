<?php

namespace Tests\Feature;

use App\Support\AdxonContent;
use Mockery;
use Tests\TestCase;

class MarketingRedesignTest extends TestCase
{
    public function test_inactive_sections_hide_content_links_and_detail_pages(): void
    {
        $content = (new AdxonContent)->all();
        $content['sections'] = array_fill_keys(array_keys(AdxonContent::SECTIONS), false);
        $this->contentService($content);
        $response = $this->get('/')->assertOk();
        foreach (['top', 'about', 'services', 'solutions', 'work', 'pricing', 'blog', 'contact'] as $section) {
            $response->assertDontSee('id="'.$section.'"', false);
            $response->assertDontSee('#'.$section.'"', false);
        }
        $this->get('/insights/portfolio/0')->assertNotFound();
        $this->get('/insights/blogs/0')->assertNotFound();
        $this->post('/enquiry', [])->assertNotFound();
    }

    public function test_settings_dropdown_displays_saved_inactive_status(): void
    {
        $content = (new AdxonContent)->all();
        $content['sections']['about'] = false;
        $content['sections']['services'] = true;
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldReceive('all')->andReturn($content);
        $this->app->instance(AdxonContent::class, $service);
        $response = $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
            ->get(route('admin.settings'))->assertOk();
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/name="sections\[about\]"[^>]*>\s*<option value="1"\s*>Active<\/option>\s*<option value="0" selected>Inactive<\/option>/', $html);
        $this->assertMatchesRegularExpression('/name="sections\[services\]"[^>]*>\s*<option value="1" selected>Active<\/option>/', $html);
    }

    public function test_banner_upload_is_saved_and_rendered(): void
    {
        $data = (new AdxonContent)->all();
        $saved = null;
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldReceive('all')->andReturnUsing(function () use (&$saved, $data) { return $saved ?? $data; });
        $service->shouldReceive('save')->once()->andReturnUsing(function (array $value) use (&$saved) { $saved = $value; });
        $this->app->instance(AdxonContent::class, $service);
        try {
            $image = \Illuminate\Http\UploadedFile::fake()->createWithContent('banner.jpg', file_get_contents(public_path('assets/studio.jpg')));
            $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
                ->post(route('admin.settings.save'), ['hero_image' => $image])
                ->assertRedirect(route('admin.settings'))->assertSessionHasNoErrors();
            $this->assertFileExists(public_path($saved['hero']['image_path']));
            $this->assertSame($data['sections'], $saved['sections']);
            $saved['sections']['top'] = true;
            $this->get('/')->assertOk()->assertSee(asset($saved['hero']['image_path']), false);
        } finally {
            if ($saved && isset($saved['hero']['image_path'])) {
                \Illuminate\Support\Facades\File::delete(public_path($saved['hero']['image_path']));
            }
        }
    }

    public function test_invalid_banner_upload_is_rejected(): void
    {
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldNotReceive('save');
        $this->app->instance(AdxonContent::class, $service);
        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
            ->post(route('admin.settings.save'), ['hero_image' => \Illuminate\Http\UploadedFile::fake()->createWithContent('bad.jpg', 'not an image')])
            ->assertSessionHasErrors('hero_image');
    }

    public function test_banner_can_be_reset_without_changing_other_hero_content(): void
    {
        $data = (new AdxonContent)->all();
        $data['hero']['image_path'] = 'uploads/banners/123.jpg';
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldReceive('all')->once()->andReturn($data);
        $service->shouldReceive('save')->once()->withArgs(fn (array $saved) => $saved['hero']['image_path'] === ''
            && $saved['hero']['headline'] === $data['hero']['headline']);
        $this->app->instance(AdxonContent::class, $service);
        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
            ->post(route('admin.settings.save'), ['hero_reset_image' => '1'])->assertSessionHasNoErrors();
        $this->assertSame(asset('assets/studio.jpg'), AdxonContent::bannerUrl(['hero' => ['image_path' => '']]));
    }

    public function test_section_status_save_preserves_content(): void
    {
        $data = (new AdxonContent)->all();
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldReceive('all')->once()->andReturn($data);
        $service->shouldReceive('save')->once()->withArgs(fn (array $saved) =>
            $saved['sections']['about'] === false && $saved['sections']['top'] === true
            && $saved['services'] === $data['services'] && $saved['hero'] === $data['hero']
        );
        $this->app->instance(AdxonContent::class, $service);
        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['CMS']]])
            ->post(route('admin.settings.save'), ['sections' => ['about' => '0', 'top' => '1']])
            ->assertRedirect(route('admin.settings'))->assertSessionHasNoErrors();
    }

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
        $this->mock(AdxonContent::class)->shouldReceive('all')->once()->andReturn(['sections' => ['contact' => true]])->getMock()->shouldReceive('appendEnquiry')->once()->withArgs(fn ($enquiry) => $enquiry['name'] === 'Test client' && $enquiry['message'] === 'A new campaign');
        $this->post(route('enquiry.store'), ['name' => 'Test client', 'email' => 'client@example.test', 'message' => 'A new campaign'])->assertRedirect(route('home', ['sent' => 1]).'#contact');
    }

    private function contentService(array $data): void
    {
        $service = Mockery::mock(AdxonContent::class)->makePartial();
        $service->shouldReceive('all')->andReturn($data);
        $this->app->instance(AdxonContent::class, $service);
    }
}
