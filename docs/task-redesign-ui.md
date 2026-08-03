# TASK: Full Visual Overhaul — Seluruh Sistem (Bukan Sekadar Refinement)

Bertindaklah sebagai Senior UI/UX Designer dan Frontend Engineer.

## Konteks

Refinement visual yang sebelumnya dikerjakan **belum terasa cukup berubah** — tampilan masih terasa mirip dengan versi lama. Sekarang lakukan **perubahan visual yang jauh lebih signifikan dan berani**, sampai benar-benar terasa seperti aplikasi modern buatan tahun 2026, bukan sekadar rapi-rapi spacing.

## Yang TIDAK BOLEH Diubah (tetap berlaku, tidak berubah dari instruksi sebelumnya)

- Semua fitur, halaman, menu, dan nama menu.
- Semua teks dan Bahasa Indonesia yang ditampilkan.
- Semua data yang ditampilkan (isi tabel, statistik, dsb).
- Semua button beserta fungsinya, semua input form, semua navigasi.
- Semua route, struktur HTML dasar, dan alur penggunaan (user flow).
- Master data unit/ruangan (kode, luas, harga) — tidak berubah.
- Jangan menambah atau menghapus halaman/fitur.

Singkatnya: **fungsi dan isi 100% sama persis seperti sekarang.** Yang berubah HANYA tampilan visualnya.

## Yang SEKARANG Boleh Diubah Lebih Berani (dibanding instruksi sebelumnya)

Instruksi sebelumnya membatasi pada "polesan" (spacing, padding, shadow, dsb). Sekarang boleh lebih jauh, selama tidak menyentuh poin "tidak boleh diubah" di atas:

- **Skema warna** boleh diperkaya (gradasi, warna aksen tambahan, dark mode elemen) selama warna identitas utama sistem tetap dominan dan bisa dikenali.
- **Gaya komponen** boleh diganti total gayanya (misalnya dari flat table menjadi card-based list, dari form standar menjadi form dengan step/section yang lebih visual) — asal data dan fungsinya identik.
- **Ilustrasi/ikon** boleh diperbarui dengan set ikon yang lebih modern dan konsisten (misalnya Lucide/Phosphor/Heroicons) menggantikan ikon lama yang terasa generik.
- **Layout grid dan proporsi** boleh dirombak (misalnya dashboard dari layout kaku menjadi bento-grid modern) selama semua elemen/informasi yang sama tetap ada, hanya disusun ulang secara visual.
- **Micro-interaction**: animasi masuk/keluar elemen, skeleton loading, transisi antar state, hover/active state yang lebih hidup.
- **Empty state, loading state, error state** boleh didesain ulang agar terlihat premium (bukan sekadar teks polos), selama pesan/informasinya sama.
- Card, tombol, badge, modal, sidebar, navbar — semua boleh didesain ulang gayanya secara signifikan (bentuk, shadow, border, elevation), bukan cuma dipoles.

## Target Rasa Visual

- Terlihat seperti produk SaaS premium (contoh acuan: Linear, Notion, Stripe Dashboard, Vercel) — bukan template admin generik.
- Ada karakter/identitas visual yang jelas, bukan default Bootstrap/komponen bawaan tanpa kustomisasi.
- Perbedaan sebelum-sesudah harus langsung terlihat jelas oleh orang awam, bukan hanya oleh developer yang teliti melihat spacing.
- Tetap konsisten di seluruh halaman (semua halaman Admin dan Pemesan memakai bahasa visual yang sama).

## Prioritas Pengerjaan

1. Dashboard (Admin & Pemesan) — paling terlihat, jadikan showcase utama perubahan.
2. Halaman Denah Reservasi Ruangan.
3. Halaman Cek Status & Faktur.
4. Halaman-halaman lain (form reservasi, manajemen data admin, dsb).

## Proses Kerja

- Kerjakan bertahap per halaman/grup halaman, jangan sekaligus semua dalam satu commit besar — supaya bisa direview satu per satu.
- Setelah satu halaman selesai, jelaskan singkat apa yang diubah secara visual (tanpa mengubah fungsi) sebelum lanjut ke halaman berikutnya.
- Jika ragu apakah suatu perubahan menyentuh fungsi/data, tanyakan dulu, jangan diasumsikan aman.