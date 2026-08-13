<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityApiPhaseNineTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->customer = User::where('email', 'customer@example.com')->first();
    }

    public function test_api_token_authentication_flow()
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $loginResponse->assertJsonStructure(['access_token', 'token_type']);

        $token = $loginResponse->json('access_token');

        $userResponse = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/auth/user');
        $userResponse->assertStatus(200);
        $userResponse->assertJson(['email' => 'customer@example.com']);
    }

    public function test_protected_cart_api_operations()
    {
        $token = $this->customer->createToken('test-token')->plainTextToken;
        $product = Product::first();

        // Add to cart
        $addResponse = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $addResponse->assertStatus(200);

        // Fetch cart
        $cartResponse = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/cart');
        $cartResponse->assertStatus(200);
        $cartResponse->assertJsonStructure(['items', 'subtotal']);
    }

    public function test_public_categories_api()
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $response->assertJsonStructure(['categories']);
    }
}
