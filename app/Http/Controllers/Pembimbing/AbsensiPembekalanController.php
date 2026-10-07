<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\PembekalanAktivasi;
use App\Models\PembekalanAttendance;
use App\Models\Pembimbing;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AbsensiPembekalanController extends Controller
{
    private function getPembimbing(?User $user): ?Pembimbing
    {
        if (! $user) {
            return null;
        }

        $pembimbing = $user->pembimbing;
        if (! $pembimbing) {
            $pembimbing = Pembimbing::query()->where('nama_guru', $user->name)->first();
            if ($pembimbing && ! $pembimbing->user_id) {
                $pembimbing->update(['user_id' => $user->id]);
            }
        }

        return $pembimbing;
    }

    private function getBimbinganSiswas(int $pembimbingId): \Illuminate\Support\Collection
    {
        return Siswa::query()
            ->with(['kelas:id,nama_kelas', 'penempatan.bengkel:id,nama_bengkel'])
            ->whereHas('penempatan', fn ($q) => $q->where('pembimbing_id', $pembimbingId))
            ->orderBy('nama')
            ->get()
            ->map(function (Siswa $siswa) {
                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                    'bengkel' => $siswa->penempatan?->bengkel?->nama_bengkel ?? '-',
                ];
            });
    }

    /**
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user();
        $pembimbing = $this->getPembimbing($user);

        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Akun Anda belum terhubung dengan profil guru pembimbing.');
        }

        $aktivasiAktif = PembekalanAktivasi::query()->where('is_active', true)
            ->orderBy('tanggal', 'desc')
            ->get();

        $selectedTanggal = (string) $request->input('tanggal', '');
        $tanggal = $selectedTanggal !== '' ? $selectedTanggal : ($aktivasiAktif->first()?->tanggal?->toDateString());

        $siswas = collect();
        $existingAttendances = collect();

        if (! empty($tanggal)) {
            $siswas = $this->getBimbinganSiswas($pembimbing->id);

            $existingAttendances = PembekalanAttendance::query()
                ->where('tanggal', $tanggal)
                ->whereIn('siswa_id', $siswas->pluck('id'))
                ->get()
                ->keyBy('siswa_id');
        }

        $aktivasiList = $aktivasiAktif->map(function (PembekalanAktivasi $a) {
            return [
                'id' => $a->id,
                'tanggal' => $a->tanggal->toDateString(),
                'tanggal_formatted' => $a->tanggal->locale('id')->isoFormat('D MMMM Y'),
            ];
        })->values()->all();

        return Inertia::render('Pembimbing/AbsensiPembekalan', [
            'aktivasiAktif' => $aktivasiList,
            'siswas' => $siswas,
            'tanggal' => $tanggal,
            'existingAttendances' => $existingAttendances,
            'pembimbing' => [
                'id' => $pembimbing->id,
                'nama_guru' => $pembimbing->nama_guru,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $pembimbing = $this->getPembimbing($user);

        if (! $pembimbing) {
            return back()->with('error', 'Akun Anda belum terhubung dengan profil guru pembimbing.');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.siswa_id' => 'required|exists:siswas,id',
            'attendances.*.status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        $aktivasi = PembekalanAktivasi::query()->where('tanggal', $validated['tanggal'])
            ->where('is_active', true)
            ->exists();

        if (! $aktivasi) {
            return back()->with('error', 'Tanggal yang dipilih tidak aktif. Silakan hubungi admin.');
        }

        $pembimbingId = $pembimbing->id;
        $siswaIds = collect($validated['attendances'])->pluck('siswa_id');

        $validSiswaIds = Siswa::query()->whereHas('penempatan', fn($q) => $q->where('pembimbing_id', $pembimbingId))
            ->whereIn('id', $siswaIds)
            ->pluck('id')
            ->toArray();

        $tanggal = $validated['tanggal'];

        foreach ($validated['attendances'] as $attendance) {
            if (! in_array($attendance['siswa_id'], $validSiswaIds)) {
                continue;
            }

            $data = [
                'siswa_id' => $attendance['siswa_id'],
                'tanggal' => $tanggal,
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
            ];

            $data[$attendance['status']] = 1;

            PembekalanAttendance::updateOrCreate(
                ['siswa_id' => $attendance['siswa_id'], 'tanggal' => $tanggal],
                $data,
            );
        }

        return redirect()->route('pembimbing.absensi-pembekalan.index', ['tanggal' => $tanggal])
            ->with('success', 'Absensi pembekalan berhasil disimpan.');
    }
}
