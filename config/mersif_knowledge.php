<?php

/*
|--------------------------------------------------------------------------
| Knowledge Base Internal MersifLab (sumber data untuk RAG)
|--------------------------------------------------------------------------
|
| File ini adalah SATU-SATUNYA sumber kebenaran untuk fakta statis yang boleh
| dipakai Mersy AI Assistant saat menjawab. KnowledgeBaseService memilih entri
| yang relevan dengan pertanyaan user, lalu menyisipkannya ke system prompt.
|
| Cara menambah pengetahuan baru: tambahkan satu entri pada array 'documents'.
|   - id       : identifier unik (untuk logging/debug).
|   - category : profile | fitur | harga | silabus | faq | kebijakan
|   - title    : judul singkat, ikut dikirim ke LLM.
|   - keywords : kata kunci pemicu (lowercase). Dipakai untuk retrieval.
|   - content  : isi fakta. Tulis ringkas, faktual, tanpa markdown.
|
| Data dinamis (daftar kursus & kelas gratis dari database) TIDAK ditulis di
| sini - diambil langsung dari DB oleh KnowledgeBaseService.
|
*/

return [

    // Selalu disertakan pada setiap prompt (identitas dasar).
    'always_include' => ['profil-mersiflab'],

    // Jumlah maksimum dokumen hasil retrieval yang disisipkan ke prompt.
    'max_documents' => 6,

    // Maksimum karakter konteks yang dikirim ke LLM (jaga-jaga biar hemat token).
    'max_context_chars' => 9000,

    'documents' => [

        [
            'id' => 'profil-mersiflab',
            'category' => 'profile',
            'title' => 'Profil MersifLab',
            'keywords' => ['mersiflab', 'mersif', 'tentang', 'apa itu', 'platform', 'profil'],
            'content' => <<<TXT
            MersifLab adalah platform Learning Management System (LMS) yang fokus pada pembelajaran teknologi terapan.
            Bidang utama yang diajarkan: Internet of Things (IoT) sebagai keunggulan utama, Virtual Reality (VR) dan Augmented Reality (AR), Artificial Intelligence (AI) dan Machine Learning, Web Development (frontend, backend, full stack), serta Mobile App Development.
            Materi disusun oleh instruktur yang lolos proses verifikasi tim MersifLab.
            Asisten AI resmi platform ini bernama Mersy.
            TXT,
        ],

        [
            'id' => 'fitur-lms',
            'category' => 'fitur',
            'title' => 'Fitur LMS MersifLab',
            'keywords' => ['fitur', 'layanan', 'fasilitas', 'lms', 'bisa apa', 'dashboard', 'sertifikat', 'certificate', 'progress'],
            'content' => <<<TXT
            Fitur yang SUDAH tersedia di LMS MersifLab:
            - Katalog kursus berbayar dengan berbagai kategori teknologi.
            - Kelas gratis (Free Class) berisi video dan modul PDF yang bisa diakses tanpa membeli kursus.
            - Video pembelajaran dan materi terstruktur dalam bentuk chapter dan modul.
            - Dashboard siswa untuk memantau progress belajar dan menandai modul selesai.
            - Sertifikat digital setelah menyelesaikan seluruh modul sebuah kursus.
            - Akses materi 24/7 dari berbagai perangkat.
            - Mersy AI Assistant untuk membantu belajar.
            - Program Become a Teacher untuk pengguna yang ingin membuat dan menjual kursus.
            - Keranjang belanja dan checkout kursus, serta invoice pembelian.
            - Pencarian kursus, kategori, dan instruktur di seluruh situs.
            - Notifikasi dan pesan antara siswa, guru, dan admin.

            Fitur yang MASIH DALAM PENGEMBANGAN (belum tersedia):
            - Forum diskusi dan komunitas.
            - Kuis dan evaluasi otomatis.
            - Sistem mentor langsung (satu lawan satu).
            - Live class dan webinar.
            TXT,
        ],

        [
            'id' => 'harga-subscription',
            'category' => 'harga',
            'title' => 'Harga Paket Subscription',
            'keywords' => ['harga', 'biaya', 'tarif', 'paket', 'langganan', 'subscription', 'berlangganan', 'premium', 'standard', 'bayar', 'price', 'rp'],
            'content' => <<<TXT
            MersifLab menyediakan dua paket subscription bulanan:

            Paket Standard - Rp 50.000 per bulan:
            - Akses ke seluruh kursus kategori standard.
            - Mersy AI Assistant tanpa batas pertanyaan harian.

            Paket Premium - Rp 150.000 per bulan:
            - Akses ke seluruh kursus kategori standard maupun premium.
            - Mersy AI Assistant tanpa batas pertanyaan harian.
            - Bisa melampirkan file (gambar, PDF, DOC, DOCX, TXT) saat bertanya ke Mersy.

            Selain subscription, kursus juga bisa dibeli satuan sesuai harga yang tertera pada halaman kursus.
            Halaman paket ada di menu Subscription. Pembayaran dikonfirmasi oleh admin, dan invoice dikirim melalui email.
            TXT,
        ],

        [
            'id' => 'kuota-ai-assistant',
            'category' => 'fitur',
            'title' => 'Kuota Pemakaian Mersy AI Assistant',
            'keywords' => ['kuota', 'batas', 'limit', 'berapa kali', 'gratis', 'mersy', 'ai assistant', 'chatbot', 'upload file'],
            'content' => <<<TXT
            Aturan kuota Mersy AI Assistant:
            - Pengunjung yang belum login: 3 pertanyaan gratis, setelah itu perlu daftar atau login untuk melanjutkan.
            - Pengguna login tanpa subscription dan belum punya kursus: 5 pertanyaan per hari.
            - Pengguna login tanpa subscription tapi sudah punya kursus: 15 pertanyaan per hari.
            - Pengguna dengan subscription aktif (Standard atau Premium): tanpa batas harian.
            - Melampirkan file saat bertanya hanya tersedia untuk subscription Premium.
            Riwayat chat yang dibuat saat masih tamu otomatis dipindahkan ke akun ketika pengguna mendaftar atau login di perangkat yang sama.
            TXT,
        ],

        [
            'id' => 'silabus-iot',
            'category' => 'silabus',
            'title' => 'Silabus Jalur Internet of Things (IoT)',
            'keywords' => ['iot', 'internet of things', 'sensor', 'mikrokontroler', 'esp32', 'arduino', 'silabus iot', 'embedded'],
            'content' => <<<TXT
            Garis besar silabus jalur belajar IoT di MersifLab:
            - Dasar elektronika: tegangan, arus, resistor, LED, penggunaan breadboard, dan pembacaan skematik.
            - Pengenalan mikrokontroler: arsitektur board, GPIO, serta pemrograman Arduino atau ESP32.
            - Sensor dan aktuator: sensor suhu, kelembaban, jarak, gerak; kontrol relay, motor, dan servo.
            - Konektivitas: WiFi, HTTP, dan protokol MQTT untuk mengirim data perangkat.
            - IoT cloud dan dashboard: pengiriman data ke server, penyimpanan, dan visualisasi real time.
            - Proyek akhir: merancang sistem monitoring atau otomasi dari hulu ke hilir.
            Judul kursus IoT yang aktif dapat berbeda-beda; daftar terbaru selalu ada di halaman Courses.
            TXT,
        ],

        [
            'id' => 'silabus-vr-ar',
            'category' => 'silabus',
            'title' => 'Silabus Jalur VR dan AR',
            'keywords' => ['vr', 'ar', 'virtual reality', 'augmented reality', 'unity', 'metaverse', '3d', 'silabus vr'],
            'content' => <<<TXT
            Garis besar silabus jalur belajar VR dan AR di MersifLab:
            - Konsep dasar realitas virtual dan augmented, termasuk jenis perangkat dan use case industrinya.
            - Pengenalan game engine (Unity): scene, object, component, dan alur build aplikasi.
            - Aset 3D: import model, material, pencahayaan, dan optimasi performa.
            - Interaksi VR: kontroler, teleportasi, grab object, dan desain UI di ruang 3D.
            - Pengembangan AR: marker based dan markerless tracking, penempatan objek di dunia nyata.
            - Proyek akhir: membangun satu aplikasi VR atau AR sederhana yang bisa dijalankan di perangkat.
            TXT,
        ],

        [
            'id' => 'silabus-ai',
            'category' => 'silabus',
            'title' => 'Silabus Jalur AI dan Machine Learning',
            'keywords' => ['ai', 'artificial intelligence', 'machine learning', 'ml', 'data', 'python', 'model', 'deep learning', 'silabus ai'],
            'content' => <<<TXT
            Garis besar silabus jalur belajar AI dan Machine Learning di MersifLab:
            - Dasar Python untuk data: struktur data, numpy, dan pandas.
            - Statistika dan pengolahan data: pembersihan data, eksplorasi, dan visualisasi.
            - Machine learning klasik: regresi, klasifikasi, clustering, serta evaluasi model.
            - Deep learning: neural network, convolutional neural network untuk citra, dan dasar NLP.
            - Penerapan model: menyimpan model dan menyajikannya lewat API sederhana.
            - Proyek akhir: menyelesaikan satu kasus nyata mulai dari data mentah sampai model yang bisa dipakai.
            TXT,
        ],

        [
            'id' => 'silabus-web',
            'category' => 'silabus',
            'title' => 'Silabus Jalur Web Development',
            'keywords' => ['web', 'website', 'frontend', 'backend', 'fullstack', 'full stack', 'html', 'css', 'javascript', 'laravel', 'php', 'database', 'silabus web'],
            'content' => <<<TXT
            Garis besar silabus jalur belajar Web Development di MersifLab:
            - Frontend dasar: HTML semantik, CSS layout responsif, dan JavaScript dasar.
            - Frontend lanjutan: manipulasi DOM, konsumsi API, serta pengenalan framework modern.
            - Backend: konsep server, routing, autentikasi, dan pola MVC.
            - Database: perancangan tabel, relasi, dan query dasar.
            - Integrasi full stack: menghubungkan frontend dengan API backend.
            - Deployment: menyiapkan environment, konfigurasi, dan menerbitkan aplikasi ke server.
            TXT,
        ],

        [
            'id' => 'silabus-mobile',
            'category' => 'silabus',
            'title' => 'Silabus Jalur Mobile App Development',
            'keywords' => ['mobile', 'android', 'ios', 'aplikasi', 'app', 'flutter', 'silabus mobile'],
            'content' => <<<TXT
            Garis besar silabus jalur belajar Mobile App Development di MersifLab:
            - Dasar pengembangan aplikasi mobile dan siklus hidup aplikasi.
            - Membangun antarmuka: layout, komponen, navigasi antar halaman, dan desain responsif.
            - State dan data: penyimpanan lokal serta konsumsi REST API.
            - Fitur perangkat: kamera, lokasi, dan notifikasi.
            - Rilis: proses build, penandatanganan aplikasi, dan persiapan publikasi ke store.
            TXT,
        ],

        [
            'id' => 'faq-akun',
            'category' => 'faq',
            'title' => 'FAQ Akun dan Pendaftaran',
            'keywords' => ['daftar', 'register', 'login', 'akun', 'password', 'lupa', 'email', 'verifikasi', 'google', 'masuk'],
            'content' => <<<TXT
            Pendaftaran akun dilakukan lewat menu Register dengan mengisi nama, email, dan password.
            Pendaftaran dan login juga bisa dilakukan dengan akun Google melalui tombol Sign in with Google.
            Setelah mendaftar, sistem mengirim email verifikasi ke alamat yang didaftarkan. Email verifikasi bisa dikirim ulang dari halaman verifikasi bila belum diterima.
            Akun yang dinonaktifkan atau di-ban tidak dapat login; hubungi admin MersifLab untuk bantuan.
            TXT,
        ],

        [
            'id' => 'faq-pembelian',
            'category' => 'faq',
            'title' => 'FAQ Pembelian Kursus dan Pembayaran',
            'keywords' => ['beli', 'pembelian', 'checkout', 'keranjang', 'cart', 'invoice', 'pembayaran', 'transfer', 'refund', 'bayar'],
            'content' => <<<TXT
            Alur membeli kursus di MersifLab:
            - Buka halaman Courses lalu pilih kursus yang diinginkan.
            - Tambahkan kursus ke keranjang atau langsung lanjut ke checkout.
            - Selesaikan pembayaran sesuai instruksi pada halaman checkout.
            - Invoice pembelian dikirim ke email pembeli.
            - Setelah pembayaran dikonfirmasi admin, kursus otomatis muncul di dashboard dan materi bisa langsung diakses.
            Pembelian subscription mengikuti alur yang sama melalui halaman Subscription.
            Untuk kendala pembayaran atau permintaan refund, hubungi tim support MersifLab lewat halaman kontak.
            TXT,
        ],

        [
            'id' => 'faq-belajar',
            'category' => 'faq',
            'title' => 'FAQ Proses Belajar dan Sertifikat',
            'keywords' => ['sertifikat', 'certificate', 'progress', 'modul', 'chapter', 'selesai', 'dashboard', 'akses materi', 'belajar'],
            'content' => <<<TXT
            Materi kursus tersusun dalam chapter, dan setiap chapter berisi modul berupa video atau dokumen.
            Progress belajar tercatat otomatis saat modul ditandai selesai, dan bisa dipantau di dashboard siswa.
            Sertifikat digital diterbitkan setelah seluruh modul dalam sebuah kursus diselesaikan, lalu dapat diunduh dari halaman sertifikat.
            Materi kursus yang sudah dibeli dapat diakses kapan saja selama akun aktif.
            TXT,
        ],

        [
            'id' => 'faq-menjadi-guru',
            'category' => 'faq',
            'title' => 'FAQ Program Become a Teacher',
            'keywords' => ['guru', 'teacher', 'mengajar', 'instruktur', 'jadi guru', 'become a teacher', 'buat kursus', 'pengajar'],
            'content' => <<<TXT
            Cara menjadi guru di MersifLab:
            - Login ke akun MersifLab.
            - Buka halaman Profil dan pilih opsi Want to become a teacher.
            - Isi formulir aplikasi beserta data diri dan pengalaman.
            - Unggah berkas pendukung seperti CV, sertifikat, atau portofolio.
            - Kirim aplikasi dan tunggu proses review tim admin.
            - Hasil review dikirim sebagai notifikasi, baik diterima maupun ditolak.
            - Setelah disetujui, akun mendapat akses membuat kursus, chapter, modul, dan mengelola konten.

            Persyaratan umum: menguasai bidang teknologi tertentu, punya pengalaman atau sertifikasi relevan, mampu mengajar dengan komunikatif, dan bersedia mengikuti standar kualitas MersifLab.
            Guru memperoleh potensi penghasilan dari kursus yang terjual, dan saldo pendapatan dapat diajukan penarikannya lewat menu withdrawal.
            TXT,
        ],

        [
            'id' => 'faq-kelas-gratis',
            'category' => 'faq',
            'title' => 'FAQ Kelas Gratis (Free Class)',
            'keywords' => ['gratis', 'free', 'free class', 'kelas gratis', 'trial', 'coba'],
            'content' => <<<TXT
            MersifLab menyediakan Free Class yang bisa diakses tanpa biaya.
            Setiap Free Class berisi video pembelajaran dan modul PDF yang bisa diunduh.
            Free Class cocok dipakai untuk mengenal gaya pengajaran MersifLab sebelum membeli kursus berbayar atau berlangganan.
            TXT,
        ],

        [
            'id' => 'kontak-dukungan',
            'category' => 'kebijakan',
            'title' => 'Kontak dan Dukungan',
            'keywords' => ['kontak', 'hubungi', 'support', 'bantuan', 'admin', 'cs', 'komplain', 'privasi', 'syarat', 'ketentuan'],
            'content' => <<<TXT
            Pertanyaan yang tidak bisa dijawab lewat Mersy AI Assistant dapat diteruskan ke tim MersifLab melalui halaman kontak atau fitur pesan di dalam LMS.
            Ketentuan layanan dan kebijakan privasi tersedia pada halaman legal di footer situs.
            TXT,
        ],
    ],
];
