# Revisi Faktur — Bahasa Indonesia, Layout Identik

Merevisi BAGIAN FAKTUR saja di db-spec-faktur-laporan-final.md. Bagian Laporan TIDAK berubah.

ATURAN UTAMA: posisi, urutan, dan struktur layout faktur WAJIB SAMA PERSIS dengan referensi
invoice resmi Cimahi Techno Park. Yang berubah HANYA bahasanya (Inggris → Indonesia).
Jangan memindahkan blok, jangan mengubah urutan kolom, jangan menambah/menghapus elemen.

## 1. config/institusi.php (ganti nilai ke Bahasa Indonesia)

return [
    'nama' => 'PEMERINTAH KOTA CIMAHI',
    'departemen' => 'DINAS PERDAGANGAN, KOPERASI, USAHA KECIL DAN MENENGAH DAN PERINDUSTRIAN',
    'unit' => 'UNIT CIMAHI TECHNO PARK',
    'alamat' => 'Jalan Raya Baros No. 78, Utama, Cimahi Selatan, Cimahi, Jawa Barat 40533',
    'telepon' => '(022) 6631816',
    'fax' => '(022) 6631816',
    'website' => 'www.disdagkoperin.cimahikota.go.id',
    'email' => 'technopark@cimahikota.go.id',
    'bank_nama' => 'Bank BJB',
    'bank_no_rekening' => '011 874 5124 001',
    'bank_atas_nama' => 'UPTD. CIMAHI TECHNOPARK',
    'penandatangan_jabatan' => 'KEPALA UPTD. CIMAHI TECHNO PARK',
    'penandatangan_nama' => 'INDRA NUGRAHA, S.E., MIL',
    'penandatangan_nip' => '19831212 200604 1 006',
];

## 2. Peta terjemahan label (POSISI TETAP, hanya teks yang diganti)

Header tengah:
- "CIMAHI CITY GOVERNMENT" → "PEMERINTAH KOTA CIMAHI"
- "TRADE, COOPERATIVE, ... INDUSTRY SERVICE" → "DINAS PERDAGANGAN, KOPERASI, USAHA KECIL DAN MENENGAH DAN PERINDUSTRIAN"
- "CIMAHI TECHNO PARK UNIT" → "UNIT CIMAHI TECHNO PARK"
- Baris kontak: "Phone"→"Telepon", "Fax"→"Faks", "Website"→"Situs", "Email"→"Surel"

Judul tengah:
- "INVOICE" → "FAKTUR"

Kotak kanan atas (urutan tetap):
- "Invoice No." → "No. Faktur"
- "Date" → "Tanggal"
- "Time" → "Waktu"   (hanya muncul kalau jenis_sewa = Jam)

Blok kiri:
- "To" → "Kepada"
- "Address" → "Alamat"

Tabel item (urutan kolom TETAP):
- "No." → "No."
- "Description" → "Uraian"
- "Date" → "Tanggal"
- "Time" → "Waktu"          (kolom ini hanya ada kalau jenis_sewa = Jam)
- "Duration (Hours)" → "Durasi"   (isi: "5 Jam" / "3 Hari" / "6 Bulan" sesuai jenis_sewa)
- "Unit Rate (IDR)" → "Tarif Satuan (Rp)"
- "Amount (IDR)" → "Jumlah (Rp)"

Isi kolom Uraian: "Sewa {nama_fasilitas} {kategori_fasilitas}, Lantai {nomor_lantai}"
Contoh: "Sewa Kubikal M5 Co-Working Space, Lantai 3A"

Baris total:
- "TOTAL" tetap "TOTAL" (bold, rata kanan)
- Format angka: "Rp 200.000,-" (pemisah ribuan TITIK, bukan koma)

Baris terbilang:
- "Amount in Words" → "Terbilang"
- Isi: terbilang Bahasa Indonesia diakhiri "Rupiah" (contoh: "Dua Ratus Ribu Rupiah")

Kotak kiri bawah:
- "Payment Information" → "Informasi Pembayaran"
- "Payments can be made via the following account:" → "Pembayaran dapat dilakukan melalui rekening berikut:"
- "Bank Name" → "Nama Bank"
- "Account Number" → "Nomor Rekening"
- "Account Name" → "Atas Nama"
- "Other Payment Method:" → "Metode Pembayaran Lain:"
- "Cash payment can be made directly to the cashier at BITC." → "Pembayaran tunai dapat dilakukan langsung ke kasir di BITC."

Blok tanda tangan kanan bawah (posisi & urutan TETAP):
- Jabatan penandatangan (dari config)
- Ruang kosong untuk tanda tangan
- Nama penandatangan (bold/bergaris bawah)
- "NIP. {nip}"

## 3. Helper terbilang — TULIS ULANG untuk Bahasa Indonesia

Ganti app/Helpers/NumberToWords.php. HAPUS fungsi toEnglishWords, buat fungsi
`terbilang(int $angka): string` sesuai kaidah Bahasa Indonesia:

- 0 → "Nol"
- 1-9 → Satu, Dua, Tiga, Empat, Lima, Enam, Tujuh, Delapan, Sembilan
- 10 → "Sepuluh", 11 → "Sebelas", 12-19 → "{satuan} Belas"
- 20-99 → "{puluhan} Puluh {satuan}"
- 100-199 → "Seratus ..." (BUKAN "Satu Ratus")
- 200+ → "{satuan} Ratus ..."
- 1.000-1.999 → "Seribu ..." (BUKAN "Satu Ribu")
- 2.000+ → "{...} Ribu ..."
- Juta, Miliar, Triliun mengikuti pola yang sama
- Selalu diakhiri " Rupiah", Title Case

Contoh yang HARUS benar:
  200000  → "Dua Ratus Ribu Rupiah"
  1800000 → "Satu Juta Delapan Ratus Ribu Rupiah"
  1150000 → "Satu Juta Seratus Lima Puluh Ribu Rupiah"
  40000   → "Empat Puluh Ribu Rupiah"
  250000  → "Dua Ratus Lima Puluh Ribu Rupiah"

Jangan pakai package eksternal. Buat minimal 5 assertion/test dengan contoh angka di atas.

## 4. Format angka & tanggal

- Uang: "Rp 1.800.000,-" (pemisah ribuan TITIK)
- Tanggal: "11 Juni 2026" (nama bulan Bahasa Indonesia)
- Waktu (jenis_sewa Jam): "11.00 - 16.00 WIB"

## 5. Yang TIDAK berubah

- Posisi logo (kiri atas), garis horizontal tebal di bawah header, judul di tengah.
- Posisi kotak No. Faktur/Tanggal/Waktu di kanan atas.
- Posisi blok Kepada/Alamat di kiri.
- Struktur & urutan kolom tabel item.
- Posisi baris TOTAL dan Terbilang.
- Posisi kotak Informasi Pembayaran (kiri bawah) & blok tanda tangan (kanan bawah).
- Aturan multi-fasilitas dalam satu kode_reservasi: tetap 1 file PDF berisi beberapa baris
  item, sesuai revisi-batch-2.md bagian A.

## Tugas

1. Update config/institusi.php.
2. Update Blade template faktur dengan seluruh terjemahan di atas, TANPA memindahkan elemen
   apa pun dari posisi aslinya.
3. Tulis ulang helper terbilang Bahasa Indonesia + testnya.
4. Update format angka & tanggal ke kaidah Indonesia.
5. Generate ulang contoh Faktur PDF (satu kasus 1 fasilitas jenis Jam, satu kasus 2 fasilitas
   dalam satu kode_reservasi), tunjukkan path filenya untuk saya cek.