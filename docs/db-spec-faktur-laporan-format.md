# Format Faktur & Laporan — VERSI FINAL

File ini MENGGANTIKAN SEPENUHNYA db-spec-faktur-laporan-format.md (termasuk revisi warna
Laporan yang sempat diminta terpisah). Kerjakan berdasarkan file ini saja untuk bagian
Faktur dan Laporan. Bagian lain di db-spec-stage3-admin.md (Dashboard, Monitoring, Data
Reservasi, approve/reject) TETAP berlaku tanpa perubahan.

CATATAN: data Fasilitas (jumlah, status ISI/Available per ruangan) akan direvisi menyusul
berdasarkan data spreadsheet keuangan terbaru — jangan jalankan ulang seeder fasilitas dulu.
File ini HANYA soal format cetak (Faktur & Laporan), bukan soal data fasilitas.

---

## 1. Faktur (PDF)

Replika layout invoice resmi Cimahi Techno Park. Gunakan Bahasa Inggris seperti dokumen
aslinya (bukan Bahasa Indonesia).

### Kop surat (letterhead)

Buat file config `config/institusi.php`:
```php
return [
    'nama' => 'CIMAHI CITY GOVERNMENT',
    'departemen' => 'TRADE, COOPERATIVE, SMALL AND MEDIUM ENTERPRISES AND INDUSTRY SERVICE',
    'unit' => 'CIMAHI TECHNO PARK UNIT',
    'alamat' => 'Jalan Raya Baros No. 78, Utama, South Cimahi, Cimahi, West Java 40533',
    'telepon' => '(022) 6631816',
    'fax' => '(022) 6631816',
    'website' => 'www.disdagkoperin.cimahikota.go.id',
    'email' => 'technopark@cimahikota.go.id',
    'bank_nama' => 'Bank BJB',
    'bank_no_rekening' => '011 874 5124 001',
    'bank_atas_nama' => 'UPTD. CIMAHI TECHNOPARK',
    'penandatangan_jabatan' => 'HEAD OF UPTD. CIMAHI TECHNO PARK',
    'penandatangan_nama' => 'INDRA NUGRAHA, S.E., MIL',
    'penandatangan_nip' => '19831212 200604 1 006',
];
```
Logo di `public/images/logo-cimahi.png` — kalau file asli belum ada, pakai kotak placeholder
bertulisan "LOGO" (jangan generate logo baru).

### Struktur invoice

Header: logo kiri, nama instansi + alamat + kontak di tengah, garis horizontal tebal,
judul "INVOICE" di tengah.

Kotak kanan atas: Invoice No. (= nomor_faktur), Date (= tanggal_faktur), Time (jam mulai–
selesai kalau jenis_sewa Jam; disembunyikan kalau bukan Jam).

Blok "To" / "Address": nama_lengkap dan alamat dari Pemesan pemilik Reservasi terkait.

Tabel item, ADAPTIF sesuai jenis_sewa dari Reservasi:
- Kolom selalu ada: No, Description, Unit Rate (IDR), Amount (IDR)
- Date: Jam = tanggal_mulai; Hari/Bulan = "tanggal_mulai – tanggal_selesai"
- Time: hanya muncul kalau jenis_sewa = Jam (jam_mulai – jam_selesai)
- Duration: Jam = "X Hours"; Hari = "X Days"; Bulan = "X Months"
- Description = "Rental of {nama_fasilitas} {kategori_fasilitas}, Lantai {nomor_lantai}"
- Unit Rate = harga_satuan; Amount = total_biaya

Baris TOTAL di bawah tabel, rata kanan, bold.

Baris "Amount in Words": total_biaya dikonversi ke terbilang Bahasa Inggris, diakhiri
" Indonesian Rupiah". Buat helper sendiri `app/Helpers/NumberToWords.php`
(`toEnglishWords(int $angka): string`) — jangan pakai package eksternal yang belum tentu
tersedia saat deploy.

Kotak "Payment Information" kiri bawah: Bank Name, Account Number, Account Name dari
config/institusi.php, plus baris "Other Payment Method: Cash payment can be made directly
to the cashier at BITC."

Blok tanda tangan kanan bawah: jabatan, spasi kosong tanda tangan, nama (bold/garis bawah),
NIP — semua dari config/institusi.php.

### Multi-faktur dalam satu kode_transaksi

Cetak-semua-faktur digabung jadi satu file PDF (page break antar faktur), masing-masing
tetap invoice lengkap terpisah dengan nomor_faktur sendiri.

---

## 2. Laporan (PDF) — rekap inventaris/okupansi statis

Ini menggantikan total desain "Laporan" versi rekap transaksi per rentang tanggal.

### Kolom (WAJIB persis, jangan diubah strukturnya)

Nomor | Uraian | Volume | Satuan | Harga Per/Bulan (Rp) | Keterangan | ISI | Available

Satu baris = satu Fasilitas (bukan grouping manual):
- Nomor: urut 1..n
- Uraian: "Lantai {nomor_lantai} ({kode_fasilitas})"
- Volume: luas Fasilitas, "-" kalau null
- Satuan: selalu 1
- Harga Per/Bulan: dari Tarif_Sewa Jenis_Sewa satuan='Bulan' untuk fasilitas itu; kalau
  fasilitas tidak punya tarif bulanan (misal Convention Hall), pakai tarif Harian x 30
  dengan catatan kecil "(estimasi dari tarif harian)" di bawah angka, font lebih kecil
  dan warna abu-abu (bukan warna latar, cuma warna teks catatan)
- Keterangan: "ISI" kalau status_aktif='Tidak Aktif' ATAU ada Reservasi aktif
  (Menunggu/Disetujui) yang mencakup tanggal laporan dicetak; "Available" kalau sebaliknya
- Kolom ISI: 1 kalau Keterangan=ISI, kosong kalau tidak
- Kolom Available: 1 kalau Keterangan=Available, kosong kalau tidak

### Styling — TANPA warna latar sama sekali (putih polos, dokumen formal hitam-putih)

- Seluruh sel tabel: background putih (`#FFFFFF`), tidak ada highlight hijau/kuning apa pun.
- Border: garis tipis solid abu-abu gelap (mis. `#333333`, tebal 0.5-1px) di SEMUA sisi
  tiap sel (bukan cuma garis bawah) — grid penuh seperti tabel laporan formal.
- Header tabel (baris judul kolom): background abu-abu muda (`#E5E5E5`), teks hitam bold,
  border sama seperti sel lain.
- Alignment per kolom:
  - Nomor: tengah, lebar sempit (~40px)
  - Uraian: kiri, lebar paling besar (kolom deskriptif)
  - Volume: kanan, format angka pakai pemisah ribuan + " m2" (mis. "44,94 m2")
  - Satuan: tengah, lebar sempit
  - Harga Per/Bulan: kanan, format "Rp 6.741.000,00" (pemisah ribuan, 2 desimal, prefix Rp)
  - Keterangan: tengah
  - ISI: tengah, lebar sempit
  - Available: tengah, lebar sempit
- Judul laporan "Data Fasilitas Gedung BITC per/{tanggal cetak, format 'd MMMM YYYY'
  Indonesia mis. '29 Juni 2026'}" di atas tabel: font lebih besar dari isi tabel, bold,
  margin-bottom cukup (beri jarak visual dari tabel, jangan mepet).
- Baris total di paling bawah tabel: kolom Uraian bertuliskan "TOTAL" (bold, rata kanan
  dalam sel gabungan/colspan sampai sebelum kolom ISI), lalu total angka kolom ISI dan
  Available (jumlah keseluruhan), sel ini juga tanpa warna latar, border tetap ada, teks
  bold.
- Footer halaman kecil (opsional tapi rapi): "Dicetak oleh {nama_admin} pada {tanggal+jam
  cetak}" font kecil abu-abu di kiri bawah tiap halaman PDF.

### Filter halaman web (sebelum ekspor PDF)

Form filter: lantai, kategori_fasilitas, status (ISI/Available/semua). Hasil filter inilah
yang di-generate ke PDF saat tombol Ekspor PDF ditekan.

### Simpan riwayat cetak

Insert satu baris ke tabel `Laporan` setiap kali Ekspor PDF ditekan (id_admin, tanggal =
now(), jenis_laporan = "Data Fasilitas Gedung BITC" + ringkasan filter yang dipakai).

---

## 3. Sebelum dipakai serius (checklist manual, bukan tugas coding)

- Ganti `public/images/logo-cimahi.png` placeholder dengan logo asli.
- Cek ulang nilai di `config/institusi.php` (rekening, nama penandatangan, NIP) sudah
  sesuai data resmi terbaru.

---

## Tugas akhir

Setelah seluruh bagian di atas selesai diimplementasikan, generate satu contoh Faktur PDF
dan satu contoh Laporan PDF dari data seeder yang ada sekarang, simpan di
storage/app/public, dan tunjukkan path filenya untuk saya cek. Jangan lakukan revisi
bertahap — kerjakan seluruh spesifikasi Faktur dan Laporan di file ini sekaligus dalam
satu proses, karena ini adalah versi final.