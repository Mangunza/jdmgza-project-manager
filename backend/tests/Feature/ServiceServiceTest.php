<?php

namespace Tests\Feature;

use App\Domains\Services\Services\ServiceService;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private ServiceService $serviceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceService = app(ServiceService::class);
    }

    public function test_can_create_service_with_uuid(): void
    {
        $service = $this->serviceService->create([
            'name' => 'Desenvolvimento Web',
            'description' => 'Desenvolvimento de aplicações web.',
            'default_cost' => 150000,
        ]);

        $this->assertNotNull($service->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f-]{36}$/i',
            $service->id
        );

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Desenvolvimento Web',
        ]);
    }

    public function test_new_service_is_active_by_default(): void
    {
        $service = $this->serviceService->create([
            'name' => 'Consultoria',
            'default_cost' => 75000,
        ]);

        $this->assertTrue($service->is_active);
    }

    public function test_can_create_inactive_service(): void
    {
        $service = $this->serviceService->create([
            'name' => 'Serviço Antigo',
            'default_cost' => 50000,
            'is_active' => false,
        ]);

        $this->assertFalse($service->is_active);
    }

    public function test_list_returns_only_active_services_by_default(): void
    {
        $active = Service::create([
            'name' => 'Desenvolvimento',
            'default_cost' => 100000,
            'is_active' => true,
        ]);

        Service::create([
            'name' => 'Serviço Descontinuado',
            'default_cost' => 50000,
            'is_active' => false,
        ]);

        $services = $this->serviceService->list();

        $this->assertCount(1, $services);
        $this->assertTrue($services->contains('id', $active->id));
    }

    public function test_list_can_include_inactive_services(): void
    {
        $active = Service::create([
            'name' => 'Desenvolvimento',
            'default_cost' => 100000,
            'is_active' => true,
        ]);

        $inactive = Service::create([
            'name' => 'Serviço Descontinuado',
            'default_cost' => 50000,
            'is_active' => false,
        ]);

        $services = $this->serviceService->list(true);

        $this->assertCount(2, $services);
        $this->assertTrue($services->contains('id', $active->id));
        $this->assertTrue($services->contains('id', $inactive->id));
    }

    public function test_can_update_service(): void
    {
        $service = Service::create([
            'name' => 'Desenvolvimento',
            'description' => 'Descrição inicial',
            'default_cost' => 100000,
            'is_active' => true,
        ]);

        $updatedService = $this->serviceService->update($service, [
            'name' => 'Desenvolvimento Web',
            'description' => 'Nova descrição',
            'default_cost' => 150000,
        ]);

        $this->assertSame('Desenvolvimento Web', $updatedService->name);
        $this->assertSame('Nova descrição', $updatedService->description);
        $this->assertEquals('150000.00', $updatedService->default_cost);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Desenvolvimento Web',
            'description' => 'Nova descrição',
            'default_cost' => 150000,
        ]);
    }

    public function test_can_activate_service(): void
    {
        $service = Service::create([
            'name' => 'Serviço',
            'default_cost' => 50000,
            'is_active' => false,
        ]);

        $activatedService = $this->serviceService->activate($service);

        $this->assertTrue($activatedService->is_active);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'is_active' => true,
        ]);
    }

    public function test_can_deactivate_service(): void
    {
        $service = Service::create([
            'name' => 'Serviço',
            'default_cost' => 50000,
            'is_active' => true,
        ]);

        $deactivatedService = $this->serviceService->deactivate($service);

        $this->assertFalse($deactivatedService->is_active);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'is_active' => false,
        ]);
    }
}
