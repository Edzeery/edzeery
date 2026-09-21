<?php

use App\Enums\Store\StoreRoleEnum;
use App\Mail\StoreMembershipCredentialsMail;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\StoreTeamService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
});

function credentialsStore(User $owner, string $suffix): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name'    => "Credentials Store {$suffix}",
        'slug'    => "credentials-store-{$suffix}-".uniqid(),
        'status'  => 'active',
    ]);
}

function credentialsActAs(User $user, Store $store): void
{
    test()->actingAs($user);
    test()->withSession(['current_store_id' => $store->id]);
    app(\App\Support\StoreContext::class)->clear();
}

it('sends exactly one credentials email to the new member containing the plaintext password', function () {
    Mail::fake();

    $owner = roleUser('merchant');
    $store = credentialsStore($owner, 'A');
    credentialsActAs($owner, $store);

    $password = 'Super-Secret-1234';

    $member = app(StoreTeamService::class)->addMember($store, [
        'name'       => 'New Member',
        'email'      => 'member.credentials@example.com',
        'password'   => $password,
        'store_role' => StoreRoleEnum::STAFF->value,
        'is_active'  => true,
    ]);

    expect($member)->toBeInstanceOf(StoreMembership::class);

    Mail::assertSent(
        StoreMembershipCredentialsMail::class,
        fn (StoreMembershipCredentialsMail $mail) => $mail
            ->hasTo('member.credentials@example.com')
            && $mail->storeName === $store->name
            && $mail->inviterName === $owner->name
            && $mail->memberName === 'New Member'
            && $mail->memberEmail === 'member.credentials@example.com'
            && $mail->password === $password
            && $mail->loginUrl === route('login')
    );

    Mail::assertSentTimes(StoreMembershipCredentialsMail::class, 1);
});

it('still creates the member when sending the credentials email fails', function () {
    $owner = roleUser('merchant');
    $store = credentialsStore($owner, 'B');
    credentialsActAs($owner, $store);

    $realManager = app(\Illuminate\Mail\MailManager::class);
    Mail::swap(new class($realManager) extends MailFake {
        public function send($view = null, array $data = [], $callback = null)
        {
            throw new \RuntimeException('Simulated mail transport failure');
        }
    });

    $password = 'Super-Secret-1234';

    $member = app(StoreTeamService::class)->addMember($store, [
        'name'       => 'Resilient Member',
        'email'      => 'member.resilient@example.com',
        'password'   => $password,
        'store_role' => StoreRoleEnum::STAFF->value,
        'is_active'  => true,
    ]);

    expect($member)->toBeInstanceOf(StoreMembership::class)
        ->and($member->user->email)->toBe('member.resilient@example.com')
        ->and(Hash::check($password, $member->user->password))->toBeTrue()
        ->and(StoreMembership::whereKey($member->id)->exists())->toBeTrue();
});