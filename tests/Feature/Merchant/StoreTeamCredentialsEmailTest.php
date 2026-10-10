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
        'name' => "Credentials Store {$suffix}",
        'slug' => "credentials-store-{$suffix}-".uniqid(),
        'status' => 'active',
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
        'name' => 'New Member',
        'email' => 'member.credentials@example.com',
        'password' => $password,
        'store_role' => StoreRoleEnum::STAFF->value,
        'is_active' => true,
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
    Mail::swap(new class($realManager) extends MailFake
    {
        public function send($view = null, array $data = [], $callback = null)
        {
            throw new \RuntimeException('Simulated mail transport failure');
        }
    });

    $password = 'Super-Secret-1234';

    $member = app(StoreTeamService::class)->addMember($store, [
        'name' => 'Resilient Member',
        'email' => 'member.resilient@example.com',
        'password' => $password,
        'store_role' => StoreRoleEnum::STAFF->value,
        'is_active' => true,
    ]);

    expect($member)->toBeInstanceOf(StoreMembership::class)
        ->and($member->user->email)->toBe('member.resilient@example.com')
        ->and(Hash::check($password, $member->user->password))->toBeTrue()
        ->and(StoreMembership::whereKey($member->id)->exists())->toBeTrue();
});

it('refuses to issue a password over an existing account (S-02 takeover closed)', function () {
    Mail::fake();

    $owner = roleUser('merchant');
    $store = credentialsStore($owner, 'Takeover');
    credentialsActAs($owner, $store);

    // The forced password is irrelevant here: the account already exists.
    $existing = User::factory()->create([
        'name' => 'Existing Account',
        'email' => 'existing.takeover@example.com',
        'password' => Hash::make('original-pass-1234'),
    ]);
    $original = $existing->password;

    expect(fn () => app(StoreTeamService::class)->addMember($store, [
        'name' => 'Imposter Name',
        'email' => $existing->email,
        'password' => 'new-hijack-pass-99',
        'store_role' => StoreRoleEnum::STAFF->value,
        'is_active' => true,
    ]))->toThrow(\Exception::class, __('teams.cannot_set_password_on_existing_user'));

    // The existing account's credentials and name stay intact, and no
    // membership exists (the transaction was rolled back).
    $existing->refresh();
    expect($existing->password)->toBe($original)
        ->and($existing->name)->toBe('Existing Account')
        ->and(StoreMembership::where('store_id', $store->id)->count())->toBe(0);
});

it('only sends the credentials email for a freshly-created account, never an existing one (S-02)', function () {
    Mail::fake();

    $owner = roleUser('merchant');
    $store = credentialsStore($owner, 'MailGate');
    credentialsActAs($owner, $store);

    $existing = User::factory()->create([
        'name' => 'Existing Account',
        'email' => 'existing.mailgate@example.com',
        'password' => Hash::make('original-pass-1234'),
    ]);

    // Existing account without a password in the payload: must join, no mail.
    $member = app(StoreTeamService::class)->addMember($store, [
        'name' => 'Existing Account',
        'email' => $existing->email,
        'store_role' => StoreRoleEnum::STAFF->value,
        'is_active' => true,
    ]);

    expect($member)->toBeInstanceOf(StoreMembership::class)
        ->and($existing->fresh()->password)->toBe($existing->password);

    Mail::assertNothingSent(StoreMembershipCredentialsMail::class);
});

it('refuses to change a member password without re-authenticating the actor (S-02)', function () {
    Mail::fake();

    $owner = roleUser('merchant');
    $owner->password = Hash::make('owner-current-pass');
    $owner->save();

    $store = credentialsStore($owner, 'Reauth');
    credentialsActAs($owner, $store);

    $staffUser = roleUser('merchant');
    $staffUser->password = Hash::make('staff-old-pass');
    $staffUser->save();

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $staffUser->id,
        'invited_by' => $owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::STAFF->value,
    ]);

    // No re-authentication provided → password must stay untouched.
    expect(fn () => app(StoreTeamService::class)->updateMember($store, $membership, [
        'name' => $staffUser->name,
        'email' => $staffUser->email,
        'password' => 'new-hijack-pass-99',
    ]))->toThrow(\Exception::class, __('teams.current_password_required'));

    expect(Hash::check('staff-old-pass', $staffUser->fresh()->password))->toBeTrue();

    // Wrong current password → still refused.
    expect(fn () => app(StoreTeamService::class)->updateMember($store, $membership, [
        'name' => $staffUser->name,
        'email' => $staffUser->email,
        'password' => 'new-hijack-pass-99',
        'current_password' => 'wrong-current-pass',
    ]))->toThrow(\Exception::class, __('teams.current_password_required'));

    expect(Hash::check('staff-old-pass', $staffUser->fresh()->password))->toBeTrue();

    // Correct re-authentication → password rotates cleanly.
    app(StoreTeamService::class)->updateMember($store, $membership, [
        'name' => $staffUser->name,
        'email' => $staffUser->email,
        'password' => 'new-legit-pass-99',
        'current_password' => 'owner-current-pass',
    ]);

    expect(Hash::check('new-legit-pass-99', $staffUser->fresh()->password))->toBeTrue();
});
