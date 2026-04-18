<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Notification;
use App\Models\Pengaduan;
use Illuminate\Support\Collection;

class DuplicateLifecycleService
{
    /**
     * Get active duplicate children of the given master ticket.
     *
     * @return \Illuminate\Support\Collection<int, Pengaduan>
     */
    public function getActiveDuplicateChildren(Pengaduan $master): Collection
    {
        return Pengaduan::query()
            ->where('duplicate_of_id', $master->id)
            ->whereIn('status', [
                Pengaduan::STATUS_PENDING,
                Pengaduan::STATUS_DIVERIFIKASI,
                Pengaduan::STATUS_DIPROSES,
            ])
            ->get();
    }

    /**
     * Auto-close active duplicate children when master is completed.
     *
     * @return int Number of children auto-closed.
     */
    public function handleMasterCompleted(Pengaduan $master, int $actorId, string $source): int
    {
        if ($master->duplicate_of_id !== null) {
            return 0;
        }

        $children = $this->getActiveDuplicateChildren($master);
        if ($children->isEmpty()) {
            return 0;
        }

        $count = 0;
        foreach ($children as $child) {
            $oldStatus = $child->status;
            $existingAdminNote = trim((string) $child->catatan_admin);
            $systemNote = "Auto-closed karena duplikat tiket #{$master->kode_pengaduan}";
            $newAdminNote = $existingAdminNote === '' ? $systemNote : "{$existingAdminNote}\n{$systemNote}";

            $child->update([
                'status' => Pengaduan::STATUS_SELESAI,
                'teknisi_id' => null,
                'completed_at' => now(),
                'auto_closed_by_duplicate' => true,
                'auto_closed_from_master_id' => $master->id,
                'auto_closed_at' => now(),
                'catatan_admin' => $newAdminNote,
            ]);

            Log::createLog(
                $child->id,
                $actorId,
                Log::ACTION_DUPLICATE_AUTO_CLOSED,
                "Tiket duplikat ditutup otomatis dari #{$master->kode_pengaduan} via {$source}",
                ['status' => $oldStatus],
                ['status' => Pengaduan::STATUS_SELESAI, 'master' => $master->kode_pengaduan]
            );

            Notification::send(
                $child->user_id,
                Notification::JENIS_STATUS_CHANGED,
                'Pengaduan Digabung & Ditutup',
                "Pengaduan #{$child->kode_pengaduan} ditutup otomatis karena duplikat dari tiket utama #{$master->kode_pengaduan} yang sudah selesai.",
                route('pengaduan.show', $master->kode_pengaduan)
            );

            $count++;
        }

        return $count;
    }
}
