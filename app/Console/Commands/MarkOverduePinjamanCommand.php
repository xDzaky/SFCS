<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Pinjaman;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MarkOverduePinjamanCommand extends Command
{
    protected $signature = 'pinjaman:mark-overdue';

    protected $description = 'Tandai pinjaman aktif yang melewati jatuh tempo menjadi terlambat dan kirim notifikasi.';

    public function handle(): int
    {
        $hourFromNow = now()->copy()->addHour();
        $dayFromNow = now()->copy()->addDay();

        $reminderCandidates = Pinjaman::query()
            ->whereIn('status', [Pinjaman::STATUS_DISETUJUI, Pinjaman::STATUS_DIPINJAM])
            ->whereBetween('tgl_jatuh_tempo', [now(), $dayFromNow])
            ->get();

        foreach ($reminderCandidates as $pinjaman) {
            $isLastHour = $pinjaman->tgl_jatuh_tempo <= $hourFromNow;
            $cacheKey = 'pinjaman_reminder:'.$pinjaman->id.':'.($isLastHour ? 'h1' : 'd1');
            if (!Cache::add($cacheKey, true, now()->addHours(12))) {
                continue;
            }

            Notification::send(
                $pinjaman->user_id,
                Notification::JENIS_PINJAMAN_STATUS,
                $isLastHour ? 'Pengingat H-1 Jam' : 'Pengingat H-1 Hari',
                $isLastHour
                    ? "Pinjaman {$pinjaman->kode_pinjaman} jatuh tempo dalam <= 1 jam."
                    : "Pinjaman {$pinjaman->kode_pinjaman} jatuh tempo dalam <= 1 hari.",
                route('pinjaman.show', $pinjaman)
            );
        }

        $candidates = Pinjaman::query()
            ->whereIn('status', [Pinjaman::STATUS_DISETUJUI, Pinjaman::STATUS_DIPINJAM])
            ->where('tgl_jatuh_tempo', '<', now())
            ->get();

        foreach ($candidates as $pinjaman) {
            $pinjaman->update([
                'status' => Pinjaman::STATUS_TERLAMBAT,
                'marked_late_at' => now(),
            ]);

            Notification::send(
                $pinjaman->user_id,
                Notification::JENIS_PINJAMAN_STATUS,
                'Pinjaman Terlambat',
                "Pinjaman {$pinjaman->kode_pinjaman} telah melewati jatuh tempo. Mohon segera mengembalikan barang.",
                route('pinjaman.show', $pinjaman)
            );
        }

        $this->info("Updated {$candidates->count()} pinjaman menjadi terlambat.");

        return self::SUCCESS;
    }
}
