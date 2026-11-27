<?php

namespace Tests\Feature\Pelanggan;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAndAddressTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelangganA;
    protected User $pelangganB;
    protected User $admin;
    protected User $petugas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pelangganA = User::factory()->create([
            'name' => 'Anindya Putri',
            'email' => 'anindya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        Customer::create([
            'user_id' => $this->pelangganA->id,
            'phone' => '081345678901',
            'address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
        ]);

        $this->pelangganB = User::factory()->create([
            'name' => 'Maya Lestari',
            'email' => 'maya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);
    }

    public function test_pelanggan_can_view_profile_and_address_list_with_storefront_layout(): void
    {
        CustomerAddress::create([
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Anindya Putri',
            'phone' => '081345678901',
            'address' => 'Jl. Dago Pakar Barat No. 42',
            'district' => 'Cimenyan',
            'city' => 'Kabupaten Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40198',
            'is_primary' => true,
        ]);

        CustomerAddress::create([
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Kantor Anindya',
            'phone' => '081200001111',
            'address' => 'Jl. Asia Afrika No. 10',
            'district' => 'Sumur Bandung',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($this->pelangganA)->get(route('profile.index'));

        $response->assertStatus(200);
        // Layout storefront MODEVA
        $response->assertSee('MODEVA');
        $response->assertSee('Beranda');
        $response->assertSee('Belanja');
        $response->assertSee('Kategori');
        $response->assertSee('Lacak Pesanan');
        $response->assertSee('halo@modeva.id');

        // Bagian Profil & Alamat
        $response->assertSee('Profil Saya');
        $response->assertSee('Informasi Profil');
        $response->assertSee('Alamat Pengiriman');
        $response->assertSee('Anindya Putri');
        $response->assertSee('anindya@modeva.test');
        $response->assertSee('081345678901');
        $response->assertSee('Jl. Dago Pakar Barat No. 42');
        $response->assertSee('Cimenyan');
        $response->assertSee('Kabupaten Bandung');
        $response->assertSee('Jawa Barat');
        $response->assertSee('40198');
        $response->assertSee('Alamat Utama');
        $response->assertSee('Kantor Anindya');
        $response->assertSee('Tambah Alamat');
        $response->assertSee('Edit');
        $response->assertSee('Hapus');
        $response->assertSee('Jadikan Alamat Utama');
    }

    public function test_pelanggan_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->pelangganA)->put(route('profile.update'), [
            'name' => 'Anindya Putri Baru',
            'email' => 'anindya.baru@modeva.test',
            'phone' => '081999888777',
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->pelangganA->id,
            'name' => 'Anindya Putri Baru',
            'email' => 'anindya.baru@modeva.test',
        ]);

        $this->assertDatabaseHas('customers', [
            'user_id' => $this->pelangganA->id,
            'phone' => '081999888777',
        ]);
    }

    public function test_update_profile_validates_duplicate_email_and_phone(): void
    {
        $response = $this->actingAs($this->pelangganA)->put(route('profile.update'), [
            'name' => '',
            'email' => 'maya@modeva.test',
            'phone' => '123',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone']);
    }

    public function test_pelanggan_can_add_address_and_first_address_becomes_primary(): void
    {
        $this->actingAs($this->pelangganA)
            ->get(route('profile.addresses.create'))
            ->assertStatus(200)
            ->assertSee('Tambah Alamat Pengiriman');

        $response = $this->actingAs($this->pelangganA)->post(route('profile.addresses.store'), [
            'recipient_name' => 'Anindya Rumah',
            'phone' => '081345678901',
            'address' => 'Jl. Setiabudi No. 55',
            'district' => 'Sukasari',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40154',
            'is_primary' => '0', // pertama otomatis utama
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Anindya Rumah',
            'district' => 'Sukasari',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40154',
            'is_primary' => true,
        ]);
    }

    public function test_pelanggan_can_update_address_and_switch_primary_address(): void
    {
        $addr1 = CustomerAddress::create([
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Alamat Satu',
            'phone' => '081111111111',
            'address' => 'Jl. Pertama No. 1',
            'district' => 'Coblong',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40132',
            'is_primary' => true,
        ]);

        $addr2 = CustomerAddress::create([
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Alamat Dua',
            'phone' => '082222222222',
            'address' => 'Jl. Kedua No. 2',
            'district' => 'Andir',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40181',
            'is_primary' => false,
        ]);

        // Edit alamat 2
        $this->actingAs($this->pelangganA)
            ->get(route('profile.addresses.edit', $addr2))
            ->assertStatus(200)
            ->assertSee('Alamat Dua');

        $this->actingAs($this->pelangganA)->put(route('profile.addresses.update', $addr2), [
            'recipient_name' => 'Alamat Dua Revisi',
            'phone' => '082233445566',
            'address' => 'Jl. Kedua Baru No. 20',
            'district' => 'Cicendo',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40171',
            'is_primary' => '0',
        ])->assertRedirect(route('profile.index'));

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $addr2->id,
            'recipient_name' => 'Alamat Dua Revisi',
            'district' => 'Cicendo',
            'postal_code' => '40171',
        ]);

        // Jadikan alamat 2 sebagai Alamat Utama
        $this->actingAs($this->pelangganA)
            ->patch(route('profile.addresses.primary', $addr2))
            ->assertRedirect(route('profile.index'));

        $this->assertFalse($addr1->fresh()->is_primary);
        $this->assertTrue($addr2->fresh()->is_primary);
    }

    public function test_deleting_primary_address_promotes_remaining_address(): void
    {
        $addr1 = CustomerAddress::create([
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Utama Lama',
            'phone' => '081111111111',
            'address' => 'Jl. Pertama No. 1',
            'district' => 'Coblong',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40132',
            'is_primary' => true,
        ]);

        $addr2 = CustomerAddress::create([
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Cadangan',
            'phone' => '082222222222',
            'address' => 'Jl. Kedua No. 2',
            'district' => 'Andir',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40181',
            'is_primary' => false,
        ]);

        $this->actingAs($this->pelangganA)
            ->delete(route('profile.addresses.destroy', $addr1))
            ->assertRedirect(route('profile.index'));

        $this->assertDatabaseMissing('customer_addresses', ['id' => $addr1->id]);
        $this->assertTrue($addr2->fresh()->is_primary);
    }

    public function test_pelanggan_cannot_access_or_modify_another_customers_address(): void
    {
        $addrA = CustomerAddress::create([
            'user_id' => $this->pelangganA->id,
            'recipient_name' => 'Alamat Rahasia A',
            'phone' => '081111111111',
            'address' => 'Jl. Rahasia No. 99',
            'district' => 'Coblong',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40132',
            'is_primary' => true,
        ]);

        // Pelanggan B tidak melihat alamat Pelanggan A di halaman profilnya
        $this->actingAs($this->pelangganB)
            ->get(route('profile.index'))
            ->assertStatus(200)
            ->assertDontSee('Alamat Rahasia A');

        // Pelanggan B ditolak (403) pada seluruh aksi modifikasi alamat milik Pelanggan A
        $this->actingAs($this->pelangganB)->get(route('profile.addresses.edit', $addrA))->assertStatus(403);
        $this->actingAs($this->pelangganB)->put(route('profile.addresses.update', $addrA), [
            'recipient_name' => 'Hack',
            'phone' => '081234567890',
            'address' => 'Hack',
            'district' => 'Hack',
            'city' => 'Hack',
            'province' => 'Hack',
            'postal_code' => '12345',
        ])->assertStatus(403);
        $this->actingAs($this->pelangganB)->patch(route('profile.addresses.primary', $addrA))->assertStatus(403);
        $this->actingAs($this->pelangganB)->delete(route('profile.addresses.destroy', $addrA))->assertStatus(403);

        $this->assertDatabaseHas('customer_addresses', ['id' => $addrA->id, 'recipient_name' => 'Alamat Rahasia A']);
    }

    public function test_non_pelanggan_cannot_access_profile_pages(): void
    {
        $this->actingAs($this->admin)->get(route('profile.index'))->assertStatus(403);
        $this->actingAs($this->petugas)->get(route('profile.index'))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_profile_pages(): void
    {
        $this->get(route('profile.index'))->assertRedirect(route('login'));
        $this->get(route('profile.addresses.create'))->assertRedirect(route('login'));
    }
}
