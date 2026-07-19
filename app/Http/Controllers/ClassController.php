<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ClassModel;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil semua kelas yang diikuti user (sebagai mahasiswa ataupun dosen)
        // Karena saat create class dosen otomatis masuk ke pivot class_user
        $classes = $user->classes()->withCount('members')->with('admin')->get();

        return view('classes.index', compact('classes', 'user'));
    }

    /** Proses membuat kelas baru (Khusus Dosen) */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'mata_kuliah' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'ruangan' => 'nullable|string|max:100',
            'semester' => 'nullable|string|max:50',
            'tahun_ajaran' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_private' => 'nullable|boolean',
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
        ]);

        $color = $request->color ?: '#'.str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);

        $class = ClassModel::create([
            'nama_kelas' => $request->nama_kelas,
            'kode_unik' => strtoupper(Str::random(6)),
            'deskripsi' => $request->deskripsi,
            'mata_kuliah' => $request->mata_kuliah,
            'ruangan' => $request->ruangan,
            'semester' => $request->semester,
            'tahun_ajaran' => $request->tahun_ajaran,
            'color' => $color,
            'admin_id' => Auth::id(),
            'is_private' => $request->boolean('is_private'),
        ]);

        // Auto-join creator to class_user as admin
        $class->members()->attach(Auth::id(), ['role' => 'admin']);

        // Log Activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create_class',
            'model_type' => ClassModel::class,
            'model_id' => $class->id,
            'new_values' => $class->toArray(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('classes.show', $class->id)
            ->with('success', 'Kelas "'.$class->nama_kelas.'" berhasil dibuat!');
    }

    /** Detail Halaman Kelas */
    public function show(ClassModel $class)
    {
        $user = Auth::user();

        // Pastikan user adalah anggota kelas atau admin pembuat kelas
        if ($class->admin_id !== $user->id && ! $class->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('classes.index')->with('error', 'Anda bukan anggota kelas ini.');
        }

        // Load relasi-relasi yang dibutuhkan
        $class->load(['admin', 'members', 'tasks' => function ($q) {
            $q->orderBy('deadline', 'asc');
        }, 'schedules' => function ($q) {
            $q->orderBy('hari', 'asc')->orderBy('waktu_mulai', 'asc');
        }, 'discussions' => function ($q) {
            $q->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc');
        }]);

        return view('classes.show', compact('class', 'user'));
    }

    /** Proses Update Kelas (Khusus Admin Kelas) */
    public function update(Request $request, ClassModel $class)
    {
        $user = Auth::user();
        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya admin kelas yang dapat mengubah informasi kelas.');
        }

        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'mata_kuliah' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'ruangan' => 'nullable|string|max:100',
            'semester' => 'nullable|string|max:50',
            'tahun_ajaran' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_private' => 'nullable|boolean',
        ], [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
        ]);

        $class->update([
            'nama_kelas' => $request->nama_kelas,
            'deskripsi' => $request->deskripsi,
            'mata_kuliah' => $request->mata_kuliah,
            'ruangan' => $request->ruangan,
            'semester' => $request->semester,
            'tahun_ajaran' => $request->tahun_ajaran,
            'color' => $request->color ?: $class->color,
            'is_private' => $request->boolean('is_private'),
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'update_class',
            'model_type' => ClassModel::class,
            'model_id' => $class->id,
            'new_values' => $class->fresh()->toArray(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('classes.show', $class->id)
            ->with('success', 'Informasi kelas berhasil diperbarui!');
    }

    /** Proses Mahasiswa Bergabung ke Kelas via Kode Unik */
    public function join(Request $request)
    {
        $request->validate([
            'kode_unik' => 'required|string|size:6',
        ], [
            'kode_unik.required' => 'Kode kelas wajib diisi.',
            'kode_unik.size' => 'Kode kelas harus terdiri dari 6 karakter.',
        ]);

        $kode = strtoupper($request->kode_unik);
        $class = ClassModel::where('kode_unik', $kode)->first();

        if (! $class) {
            return back()->with('error', 'Kelas dengan kode tersebut tidak ditemukan.');
        }

        if ($class->is_private) {
            return back()->with('error', 'Kelas ini bersifat privat dan tidak dapat diikuti oleh anggota lain.');
        }

        $user = Auth::user();

        // Cek jika sudah bergabung
        if ($class->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('classes.show', $class->id)->with('error', 'Anda sudah bergabung di kelas ini.');
        }

        // Attach user to class
        $class->members()->attach($user->id, ['role' => 'member']);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'join_class',
            'model_type' => ClassModel::class,
            'model_id' => $class->id,
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke dosen pembuat kelas
        Notification::create([
            'user_id' => $class->admin_id,
            'class_id' => $class->id,
            'judul' => 'Mahasiswa Baru Bergabung',
            'pesan' => $user->name.' telah bergabung ke kelas '.$class->nama_kelas,
            'tipe' => 'kelas',
            'link' => route('classes.show', $class->id),
        ]);

        return redirect()->route('classes.show', $class->id)
            ->with('success', 'Berhasil bergabung dengan kelas "'.$class->nama_kelas.'"!');
    }

    /** Proses Mahasiswa Bergabung ke Kelas via Link Undangan */
    public function joinLink($kode)
    {
        $class = ClassModel::where('kode_unik', $kode)->first();

        if (! $class) {
            // Jika belum login, redirect ke login dulu
            if (! Auth::check()) {
                return redirect()->route('login')->with('info', 'Silakan login terlebih dahulu untuk bergabung ke kelas.');
            }
            return redirect()->route('classes.index')->with('error', 'Link undangan tidak valid atau kelas tidak ditemukan.');
        }

        // Jika belum login, simpan URL tujuan & arahkan ke login
        if (! Auth::check()) {
            session(['url.intended' => url()->current()]);
            return redirect()->route('login')->with('info', 'Silakan login terlebih dahulu untuk bergabung ke kelas.');
        }

        $user = Auth::user();

        // Cek jika user sudah menjadi member atau admin
        if ($class->members()->where('user_id', $user->id)->exists() || $class->admin_id === $user->id) {
            return redirect()->route('classes.show', $class->id)
                ->with('info', 'Anda sudah berada di dalam kelas ini.');
        }

        // Tambahkan ke pivot table dengan role 'member'
        $class->members()->attach($user->id, ['role' => 'member']);

        // Buat notifikasi
        Notification::create([
            'user_id' => $user->id,
            'class_id' => $class->id,
            'judul' => 'Berhasil Bergabung',
            'pesan' => 'Anda telah berhasil bergabung ke kelas '.$class->nama_kelas,
            'tipe' => 'sistem',
            'link' => route('classes.show', $class->id),
        ]);

        return redirect()->route('classes.show', $class->id)
            ->with('success', 'Berhasil bergabung dengan kelas via link undangan!');
    }


    /** Proses Keluar dari Kelas (Leave) */
    public function leave(ClassModel $class, Request $request)
    {
        $user = Auth::user();

        if ($class->admin_id === $user->id) {
            return back()->with('error', 'Sebagai admin utama, Anda tidak bisa meninggalkan kelas. Anda dapat menghapus kelas jika tidak dibutuhkan.');
        }

        // Detach member
        $class->members()->detach($user->id);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'leave_class',
            'model_type' => ClassModel::class,
            'model_id' => $class->id,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Anda telah berhasil keluar dari kelas '.$class->nama_kelas);
    }

    /** Hapus Kelas (Khusus Admin Utama / Pembuat Kelas) */
    public function destroy(ClassModel $class, Request $request)
    {
        $user = Auth::user();

        if ($class->admin_id !== $user->id) {
            return back()->with('error', 'Hanya pembuat kelas yang dapat menghapus kelas ini.');
        }

        // Simpan log sebelum menghapus
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'delete_class',
            'model_type' => ClassModel::class,
            'model_id' => $class->id,
            'old_values' => $class->toArray(),
            'ip_address' => $request->ip(),
        ]);

        $class->delete();

        return redirect()->route('classes.index')
            ->with('success', 'Kelas berhasil dihapus permanen.');
    }

    /** Tambah Admin Lain ke Kelas (Multi-Admin) */
    public function addAdmin(ClassModel $class, Request $request)
    {
        $user = Auth::user();

        // Hanya admin kelas yang bisa menambah admin baru
        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya admin kelas yang dapat menambah pengajar/admin baru.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email admin wajib diisi.',
            'email.exists' => 'Email tidak terdaftar di sistem.',
        ]);

        $newAdmin = User::where('email', $request->email)->first();

        // Mencegah dosen menambahkan dirinya sendiri
        if ($newAdmin->id === $user->id) {
            return back()->with('error', 'Anda tidak perlu menambahkan email Anda sendiri.');
        }

        if ($newAdmin->role === 'mahasiswa') {
            return back()->with('error', 'Mahasiswa tidak bisa dijadikan admin/pengajar kelas.');
        }

        // Cek jika sudah menjadi anggota
        $pivot = $class->members()->where('user_id', $newAdmin->id)->first();

        if ($pivot) {
            if ($pivot->pivot->role === 'admin') {
                return back()->with('error', 'User tersebut sudah menjadi admin kelas.');
            }
            // Update role to admin
            $class->members()->updateExistingPivot($newAdmin->id, ['role' => 'admin']);
        } else {
            // Attach new admin
            $class->members()->attach($newAdmin->id, ['role' => 'admin']);
        }

        return back()->with('success', $newAdmin->name.' berhasil dijadikan admin kelas.');
    }
}
