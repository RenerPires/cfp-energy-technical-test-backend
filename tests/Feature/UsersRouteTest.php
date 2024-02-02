<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;
use \Symfony\Component\HttpFoundation\Response;

class UsersRouteTest extends TestCase
{

    public function testShouldRetrieveAListOfUsers(): void
    {
        User::factory()->count(5)->create();

        $response = $this->get('/users');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'username',
                    'phone_number',
                    'date_of_birth',
                    'email'
                ]
            ]
        ]);

        $response->assertJsonCount(5, 'data');
    }

    public function testShouldRetrieveUser(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/users/{$user->id}");

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'username',
                'phone_number',
                'date_of_birth',
                'email'
            ]
        ]);
    }

    public function testShouldCreateUser(): void
    {
        $user = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'john_doe',
            'phone_number' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'email' => 'john.doe@cfpenergy.co',
            'password' => 'password'
        ];

        $response = $this->post('/users', $user);

        $response->assertStatus(Response::HTTP_CREATED);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'username',
                'phone_number',
                'date_of_birth',
                'email'
            ]
        ]);

        $response->assertHeader('Location', $response->json('data')['id']);
    }

    public function testShouldUpdateUser(): void
    {
        $user = User::factory()->create();
        $newUser = array_merge($user->toArray(), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'john_doe',
            'phone_number' => '1234567890',
            'date_of_birth' => '1990-01-01',
            'email' => 'john.doe@cfpenergy.co',
        ]);

        $response = $this->put("/users/{$user->id}", $newUser);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'username',
                'phone_number',
                'date_of_birth',
                'email'
            ]
        ]);

        $response->assertJson([
            'data' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'john_doe',
                'phone_number' => '1234567890',
                'date_of_birth' => '1990-01-01',
                'email' => 'john.doe@cfpenergy.co'
            ]
        ]);

    }

    public function testShouldDeleteUser(): void
    {
        $user = User::factory()->create();

        $response = $this->delete("/users/{$user->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(User::find($user->id));
    }
}
