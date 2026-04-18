<?php

namespace App\Http\Controllers\Teknisi;

use App\Http\Controllers\Controller;
use App\Models\SchoolMap;
use App\Services\SchoolMapService;
use Illuminate\View\View;

class TeknisiSchoolMapController extends Controller
{
    public function __construct(private readonly SchoolMapService $schoolMapService)
    {
    }

    public function index(): View
    {
        $map = SchoolMap::query()->where('is_active', true)->first();
        $payload = $this->schoolMapService->buildViewerPayload($map);

        return view('teknisi.peta-digital.index', array_merge($payload, [
            'fileUrl' => $map ? $this->schoolMapService->fileUrl($map) : null,
        ]));
    }
}
