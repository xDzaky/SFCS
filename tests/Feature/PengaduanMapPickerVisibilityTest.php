<?php

namespace Tests\Feature;

use App\Models\Gedung;
use App\Models\Kategori;
use App\Models\Pengaduan;
use App\Models\SchoolMap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PengaduanMapPickerVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_hides_map_picker_when_no_active_map_exists(): void
    {
        $user = $this->createUser('siswa');
        $this->createKategoriDanGedung();

        $response = $this->actingAs($user)->get(route('pengaduan.create'));

        $response->assertOk();
        $response->assertDontSee('Pilih Titik Lokasi di Denah');
        $response->assertDontSee('Belum ada denah aktif');
    }

    public function test_create_form_shows_map_picker_when_active_map_exists(): void
    {
        $user = $this->createUser('siswa');
        $this->createKategoriDanGedung();
        $this->createActiveMap();

        $response = $this->actingAs($user)->get(route('pengaduan.create'));

        $response->assertOk();
        $response->assertSee('Pilih Titik Lokasi di Denah');
    }

    public function test_edit_form_hides_map_picker_when_no_active_map_exists(): void
    {
        $user = $this->createUser('siswa');
        [$kategori, $gedung] = $this->createKategoriDanGedung();

        $pengaduan = Pengaduan::create([
            'kode_pengaduan' => 'PDN-MAP-'.Str::upper(Str::random(8)),
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'gedung_id' => $gedung->id,
            'lokasi_detail' => 'Dekat pintu masuk',
            'judul' => 'Lampu ruang kelas mati',
            'deskripsi' => 'Lampu di ruang kelas mati total dan mengganggu proses belajar.',
            'prioritas' => Pengaduan::PRIORITAS_SEDANG,
            'requested_prioritas' => Pengaduan::PRIORITAS_SEDANG,
            'status' => Pengaduan::STATUS_PENDING,
            'impact_safety_risk' => false,
            'impact_exam_related' => false,
            'impact_learning_blocked' => true,
            'impact_area_scope' => '1_kelas',
            'impact_utilities' => 'listrik',
            'lantai' => '1',
        ]);

        $response = $this->actingAs($user)->get(route('pengaduan.edit', $pengaduan));

        $response->assertOk();
        $response->assertDontSee('Pilih Titik Lokasi di Denah');
        $response->assertDontSee('Belum ada denah aktif');
    }

    private function createUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' '.Str::random(6),
            'email' => Str::lower(Str::random(8)).'@example.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
            'force_password_change' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return array{0:\App\Models\Kategori,1:\App\Models\Gedung}
     */
    private function createKategoriDanGedung(): array
    {
        $kategori = Kategori::create([
            'nama' => 'Kelistrikan',
            'deskripsi' => 'Kategori test',
            'is_active' => true,
        ]);

        $gedung = Gedung::create([
            'nama' => 'Gedung A',
            'kode' => 'GDA',
            'jumlah_lantai' => 3,
            'is_active' => true,
        ]);

        return [$kategori, $gedung];
    }

    private function createActiveMap(): SchoolMap
    {
        return SchoolMap::create([
            'nama' => 'Denah Utama',
            'slug' => 'denah-utama',
            'file_path' => 'school-maps/test-map/source.pdf',
            'file_type' => 'pdf',
            'page_count' => 1,
            'is_active' => true,
        ]);
    }
}
