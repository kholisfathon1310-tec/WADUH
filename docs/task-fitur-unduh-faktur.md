# TASK: Fitur Unduh Faktur Reservasi di Halaman "Cek Status"

Bertindaklah sebagai Frontend Engineer untuk sistem WADUH (Wadah Akses Digital Unit Hunian BITC).

## Latar Belakang

Saat pemesan membuka halaman **Cek Status** reservasi, tambahkan kemampuan untuk **mengunduh faktur reservasi** dalam bentuk file (PDF).

Tujuan faktur ini: sebagai bukti bahwa pemesan benar-benar sudah melakukan reservasi, untuk ditunjukkan saat survei lapangan ke lokasi BITC.

## Ketentuan Umum

- Ini adalah **penambahan fitur baru**, bukan redesign. Jangan mengubah fitur, layout, atau flow yang sudah ada di halaman Cek Status — hanya menambahkan tombol/aksi unduh faktur beserta komponen faktur itu sendiri.
- Jangan mengubah struktur data reservasi yang sudah ada. Gunakan data reservasi yang sudah tersedia (kode reservasi, nama pemesan, ruangan/unit yang dipesan, tanggal & jam, harga, status) sebagai sumber isi faktur — jangan membuat field/data baru tanpa instruksi.
- Ikuti master data unit (kode ruangan, luas, harga) yang sudah didefinisikan sebelumnya. Jangan mengubah kode unit atau harga.

## Lokasi & Trigger

- Di halaman **Cek Status**, tambahkan tombol **"Unduh Faktur"** (atau "Unduh Bukti Reservasi") pada setiap reservasi yang ditampilkan.
- Tombol hanya aktif/muncul untuk reservasi yang statusnya valid (misalnya sudah dikonfirmasi/dibayar — sesuaikan dengan status yang sudah ada di sistem, jangan membuat status baru).
- Saat diklik, sistem men-generate dan mengunduh file faktur (format PDF).

## Isi Faktur

Faktur wajib memuat minimal:
- **Kode Reservasi** (tampilkan jelas, besar, mudah dibaca — ini bagian paling penting untuk verifikasi di lapangan)
- Nama pemesan
- Kode unit/ruangan yang dipesan (contoh: R1, K5, CH1) beserta jenis (Working Space / Coworking Space / Convention Hall) dan lantai
- Tanggal dan jam reservasi (mulai–selesai)
- Durasi/paket sewa (per jam/hari/bulan, sesuai yang dipilih)
- Total biaya
- Status reservasi
- Tanggal faktur diterbitkan (tanggal unduh/cetak)
- Nama sistem/instansi: BITC Cimahi Techno Park (Baros Information Technology Creative Centre)

## Desain Faktur

- Layout bersih, profesional, seperti invoice/struk resmi — bukan sekadar dump data.
- Konsisten dengan identitas visual sistem yang sudah ada (warna, font Inter/Poppins, dsb — ikuti yang sudah dipakai di sistem, jangan ciptakan skema warna baru).
- Sertakan header dengan nama sistem/logo (placeholder jika logo belum ada).
- Kode reservasi boleh ditampilkan juga dalam bentuk QR code atau barcode jika memungkinkan (opsional — tanyakan dulu jika ingin implementasi ini, jangan berasumsi library apa yang tersedia).
- Pastikan hasil PDF rapi saat dicetak (ukuran kertas A4 atau sesuai standar invoice, margin wajar).

## Teknis

- Karena project ini berbasis HTML/CSS/JS (Bootstrap 5) yang nantinya akan di-porting ke Laravel, buat implementasi unduh PDF di sisi frontend terlebih dahulu (misalnya dengan library JS untuk generate PDF dari elemen HTML), dengan struktur kode yang rapi dan mudah diadaptasi ke Laravel (Blade + library PDF seperti dompdf/mpdf) nanti.
- Pisahkan komponen tampilan faktur (template) dari logic pemicu unduh, supaya mudah dipakai ulang.
- Jika ada keterbatasan library di lingkungan saat ini, jelaskan dulu opsinya sebelum memilih salah satu — jangan langsung menebak.

## Yang Tidak Boleh Diubah

- Halaman/fitur Cek Status yang sudah ada (data yang ditampilkan, filter, status, dll).
- Struktur data reservasi dan master data unit.
- Alur reservasi dan pembayaran yang sudah berjalan.
- Warna identitas dan gaya visual sistem yang sudah ditetapkan sebelumnya.

## Jika Ada yang Kurang Jelas

Jika ada detail yang belum pasti (misalnya: library PDF yang tersedia, apakah perlu QR code, format nomor faktur, dsb), **tanyakan dulu** sebelum berasumsi dan mengeksekusi.