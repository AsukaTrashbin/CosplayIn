<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\AvailabilityBlockRequest;
use App\Models\AvailabilityBlock;
use App\Models\Costume;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function index(Costume $costume): View
    {
        $this->authorize('update', $costume);

        $costume->load('sizes');

        $blocks = $costume->availabilityBlocks()
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->orderBy('size')
            ->get();

        return view('tenant.kostum.jadwal', compact('costume', 'blocks'));
    }

    public function store(AvailabilityBlockRequest $request, Costume $costume): RedirectResponse
    {
        $this->authorize('update', $costume);

        $data = $request->validated();

        $exists = $costume->availabilityBlocks()
            ->where('size', $data['ukuran'])
            ->whereDate('date', $data['tanggal'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'tanggal' => 'Tanggal tersebut sudah diblokir atau sudah ada booking untuk ukuran ini.',
            ]);
        }

        $costume->availabilityBlocks()->create([
            'size' => $data['ukuran'],
            'date' => $data['tanggal'],
            'type' => AvailabilityBlock::TYPE_MAINTENANCE,
            'notes' => $data['catatan'] ?? null,
            'booking_id' => null,
        ]);

        return back()->with('success', 'Jadwal maintenance berhasil diblokir untuk ukuran '.$data['ukuran'].' pada '.$data['tanggal'].'.');
    }

    public function destroy(Costume $costume, AvailabilityBlock $block): RedirectResponse
    {
        $this->authorize('update', $costume);

        abort_unless($block->costume_id === $costume->id, 404);
        abort_unless($block->type === AvailabilityBlock::TYPE_MAINTENANCE, 403, 'Blokir booking tidak dapat dihapus manual.');

        $block->delete();

        return back()->with('success', 'Blokir jadwal maintenance berhasil dihapus.');
    }
}
