<?php

namespace Tests\Feature;

use App\Support\AdxonAdminData;
use App\Support\AdxonContent;
use Mockery;
use Tests\TestCase;

class WebsiteLeadsTest extends TestCase
{
    public function test_contact_submission_creates_lead_and_count_clears_only_when_marked_read(): void
    {
        $data = (new AdxonAdminData)->all();
        $data['leads'] = [];
        $service = Mockery::mock(AdxonAdminData::class)->makePartial();
        $service->shouldReceive('all')->andReturnUsing(function () use (&$data) { return $data; });
        $service->shouldReceive('save')->twice()->andReturnUsing(function (array $value) use (&$data) { $data = $value; });
        $this->app->instance(AdxonAdminData::class, $service);
        $content = (new AdxonContent)->all();
        $content['sections']['contact'] = true;
        $cms = Mockery::mock(AdxonContent::class)->makePartial();
        $cms->shouldReceive('all')->andReturn($content);
        $cms->shouldReceive('appendEnquiry')->once()->withArgs(fn ($row) => $row['phone'] === '+91 98765 43210');
        $this->app->instance(AdxonContent::class, $cms);

        $this->post('/enquiry', ['name' => 'Website visitor', 'email' => 'visitor@example.test',
            'phone' => '+91 98765 43210', 'budget' => 'Rs 30,000', 'message' => 'Help with ads'])
            ->assertRedirect(route('home', ['sent' => 1]).'#contact')->assertSessionHasNoErrors();
        $this->assertCount(1, $data['leads']);
        $lead = $data['leads'][0];
        $this->assertSame('Website', $lead['source']);
        $this->assertSame('New Lead', $lead['stage']);
        $this->assertSame('+91 98765 43210', $lead['phone']);
        $this->assertSame('Rs 30,000', $lead['budget']);
        $this->assertSame('Help with ads', $lead['notes']);
        $this->assertTrue($lead['website_unread']);

        $this->withSession(['adxon_admin' => true, 'adxon_user' => ['permissions' => ['*']]])
            ->get('/admin')->assertOk()->assertSee('1 new website leads');
        $this->get('/admin/leads/list')->assertOk()->assertSee('Website visitor')
            ->assertSee('Rs 30,000')->assertSee('Help with ads')->assertSee('1 new website leads');
        $this->get(route('admin.leads.notifications'))->assertOk()->assertJson(['count' => 1]);
        $this->post(route('admin.leads.notifications.read'))->assertRedirect(route('admin.leads.list'));
        $this->get(route('admin.leads.notifications'))->assertOk()->assertJson(['count' => 0]);
        $this->assertFalse($data['leads'][0]['website_unread']);
        $this->get('/admin')->assertOk()->assertDontSee('1 new website leads');
    }

    public function test_invalid_contact_does_not_create_lead(): void
    {
        $this->mock(AdxonContent::class)->shouldReceive('all')->once()->andReturn(['sections' => ['contact' => true]])
            ->getMock()->shouldNotReceive('appendEnquiry');
        $this->mock(AdxonAdminData::class)->shouldNotReceive('addLead');
        $this->post('/enquiry', ['name' => 'Visitor', 'email' => 'invalid', 'message' => 'Hello'])
            ->assertSessionHasErrors('email');
    }

    public function test_unauthorized_user_cannot_clear_lead_notifications(): void
    {
        $this->mock(AdxonAdminData::class)->shouldNotReceive('markWebsiteLeadsRead');
        $this->get('/admin/leads/list')->assertRedirect(route('admin.login'));
        $this->post(route('admin.leads.notifications.read'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.leads.notifications'))->assertRedirect(route('admin.login'));
    }
}
