<?php

namespace App\Http\Requests;

use App\Enums\SatuanSewa;
use App\Services\CartService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Alur publik, tanpa auth.
    }

    public function rules(): array
    {
        return [
            // Data diri Pemesan (diisi sekali untuk semua item keranjang).
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'alamat'       => ['required', 'string', 'max:500'],
            'usia'         => ['required', 'integer', 'min:17', 'max:120'],
            'pekerjaan'    => ['required', 'string', 'max:100'],
            'no_telepon'   => ['required', 'string', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'email'        => ['required', 'email', 'max:150'],

            // File dokumen (kalau ada): pdf/jpg/png, maks 5 MB per file.
            'dokumen'     => ['array'],
            'dokumen.*'   => ['array'],
            'dokumen.*.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var CartService $cart */
            $cart = app(CartService::class);

            // Untuk tiap item Bulan di keranjang, wajib minimal 1 dokumen terupload.
            foreach ($cart->items() as $index => $item) {
                if (($item['satuan'] ?? null) !== SatuanSewa::Bulan->value) {
                    continue;
                }
                $files = $this->file("dokumen.$index", []);
                $files = array_filter(is_array($files) ? $files : [$files]);
                if ($files === []) {
                    $v->errors()->add(
                        "dokumen.$index",
                        "Fasilitas bulanan \"{$item['nama_fasilitas']}\" wajib melampirkan minimal 1 dokumen persyaratan (Company Profile / legalitas / KTP penanggung jawab)."
                    );
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'nama_lengkap' => 'nama lengkap',
            'alamat'       => 'alamat',
            'usia'         => 'usia',
            'pekerjaan'    => 'pekerjaan',
            'no_telepon'   => 'nomor telepon',
            'email'        => 'email',
            'dokumen.*.*'  => 'dokumen',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi sesuai identitas.',
            'alamat.required'       => 'Alamat wajib diisi.',
            'usia.required'         => 'Usia wajib diisi.',
            'usia.min'              => 'Pemesan minimal berusia 17 tahun.',
            'usia.max'              => 'Usia tidak valid.',
            'pekerjaan.required'    => 'Pekerjaan wajib diisi.',
            'no_telepon.required'   => 'Nomor telepon wajib diisi agar admin bisa menghubungi Anda.',
            'no_telepon.regex'      => 'Format nomor telepon tidak valid — gunakan angka, mis. 0812xxxxxxx.',
            'email.required'        => 'Email wajib diisi — kode reservasi terhubung ke email ini.',
            'email.email'           => 'Format email tidak valid (contoh: nama@email.com).',
            'dokumen.*.*.mimes'     => 'Dokumen harus berformat PDF, JPG, atau PNG.',
            'dokumen.*.*.max'       => 'Ukuran tiap dokumen maksimal 5 MB.',
        ];
    }
}
