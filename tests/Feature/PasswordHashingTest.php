<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordHashingTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_double_hashed_when_using_hash_make_with_hashed_cast()
    {
        // 1. Create user with Hash::make()
        $password = 'secret';
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make($password),
        ]);

        // 2. Check if we can login (Hash::check should fail if double hashed)
        // Retrieve fresh user to ensure we get what's in DB
        $freshUser = User::find($user->id);

        // If double hashed, the stored password is the HASH of the hash.
        // So checking 'secret' against stored password will fail.
        $this->assertTrue(Hash::check($password, $freshUser->password), 'Password check failed - likely double hashed');
    }

    public function test_password_is_correct_when_using_plain_text_with_hashed_cast()
    {
        // 1. Create user with plain text
        $password = 'secret';
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => $password,
        ]);

        // 2. Check if we can login
        $freshUser = User::find($user->id);

        $this->assertTrue(Hash::check($password, $freshUser->password), 'Password check failed for plain text');
    }
}
