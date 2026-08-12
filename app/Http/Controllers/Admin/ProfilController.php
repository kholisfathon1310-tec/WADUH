<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(): View
    {
        return view('admin.profil.index', ['me' => Auth::guard('admin')->user()]);
    }

    /** Ubah nama & email — konfirmasi cukup lewat pop up di form, tanpa perlu masukkan kata sandi. */
    public function update(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'nama_admin'  => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', Rule::unique('admin', 'email')->ignore($admin->id_admin, 'id_admin')],
            'no_whatsapp' => ['nullable', 'string', 'max:20'],
            'alamat'      => ['nullable', 'string', 'max:500'],
        ], [
            'nama_admin.required' => 'Nama wajib diisi.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email belum benar.',
            'email.unique'        => 'Email ini sudah dipakai akun admin lain.',
            'no_whatsapp.max'     => 'Nomor WhatsApp terlalu panjang.',
            'alamat.max'          => 'Alamat terlalu panjang, maksimal 500 karakter.',
        ]);

        $admin->nama_admin = $data['nama_admin'];
        $admin->email = $data['email'];
        $admin->no_whatsapp = $data['no_whatsapp'] ?? null;
        $admin->alamat = $data['alamat'] ?? null;
        $admin->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /** Ganti foto profil — hanya PNG/JPG, tidak perlu verifikasi kata sandi (bukan data sensitif). */
    public function foto(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'foto' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image'    => 'File harus berupa gambar.',
            'foto.mimes'    => 'Foto harus berformat PNG atau JPG.',
            'foto.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        $lama = $admin->foto;
        $admin->foto = $request->file('foto')->store('admin', 'public');
        $admin->save();

        if ($lama) {
            Storage::disk('public')->delete($lama);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    /** Ganti kata sandi — form terpisah, verifikasi lewat kata sandi lama itu sendiri. */
    public function password(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_lama.required' => 'Masukkan kata sandi lama Anda.',
            'password_baru.required' => 'Kata sandi baru wajib diisi.',
            'password_baru.min'      => 'Kata sandi baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (! Hash::check($data['password_lama'], $admin->password)) {
            return back()->with('error', 'Kata sandi lama salah, kata sandi tidak diubah.');
        }

        $admin->password = $data['password_baru'];
        $admin->save();

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
