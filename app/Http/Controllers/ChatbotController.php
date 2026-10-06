<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AiContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected AiContextController $aiContextController;

    public function __construct()
    {
        $this->aiContextController = new AiContextController();
    }

    /**
     * Endpoint chat: Menerima pesan dari web UI.
     * Jika AI_SERVICE_URL diatur di .env, pesan akan diteruskan ke AI server teman beserta role context.
     * Jika belum diatur, menampilkan respon pembaruan sistem (data lama telah dibersihkan).
     */
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $message = trim($request->input('message'));
        $user    = Auth::user();

        $role     = $user ? $user->role : 'siswa';
        $userId   = $user ? $user->id : null;
        $userName = $user ? $user->name : 'User';

        $aiServiceUrl = env('AI_SERVICE_URL');

        // Jika developer AI sudah memasang URL service AI-nya di .env
        if (!empty($aiServiceUrl)) {
            try {
                // Ambil context database yang sudah difilter ketat sesuai hak akses role user saat ini
                $context = $this->aiContextController->buildContextByRole($role, $userId, $userName);

                $payload = [
                    'message' => $message,
                    'user'    => [
                        'id'   => $userId,
                        'name' => $userName,
                        'role' => $role,
                    ],
                    'context' => $context,
                ];

                // Forward request ke service AI teman (timeout 15 detik)
                $response = Http::timeout(15)
                    ->withHeaders([
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($aiServiceUrl, $payload);

                if ($response->successful()) {
                    $resData = $response->json();
                    return response()->json([
                        'reply'   => $resData['reply'] ?? $resData['response'] ?? $resData['message'] ?? 'Tidak ada jawaban dari AI.',
                        'type'    => $resData['type'] ?? 'text',
                        'actions' => $resData['actions'] ?? [],
                    ]);
                } else {
                    Log::warning('AI Service returned non-200 status', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Gagal terhubung ke AI Service: ' . $e->getMessage());
            }
        }

        // Response default saat dalam tahap integrasi & data lama dinonaktifkan
        $roleDisplay = ucfirst($role);
        $reply = "Halo **{$userName}**! 👋\n\n"
            . "Data chatbot lama SFCS telah dibersihkan dan dinonaktifkan.\n\n"
            . "Saat ini sistem asisten virtual SFCS sedang dalam tahap pengembangan & integrasi model AI baru oleh Tim AI kami agar jawaban lebih cerdas, kontekstual, dan tidak melenceng.\n\n"
            . "🔗 **Status Integrasi:**\n"
            . "• Role kamu saat ini: **{$roleDisplay}**\n"
            . "• Endpoint API database per role sudah aktif & siap dihubungkan ke backend AI temanmu.\n"
            . "• Endpoint Context: `/api/v1/ai/context`\n\n"
            . "Segera setelah temanmu mengaktifkan server AI dan mengatur `AI_SERVICE_URL`, asisten ini akan langsung otomatis aktif melayanimu!";

        return response()->json([
            'reply'   => $reply,
            'type'    => 'text',
            'actions' => [
                ['label' => '📝 Buat Pengaduan', 'url' => route('pengaduan.create')],
                ['label' => '📦 Pinjam Barang',   'url' => route('pinjaman.create')],
            ],
        ]);
    }
}
