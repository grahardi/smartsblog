<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Services\WikimediaImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    private User $author;

    /** @var array<string, Category> */
    private array $categories = [];

    public function run(): void
    {
        $this->author = User::where('role', 'admin')->first() ?? User::first();

        if (! $this->author) {
            $this->command?->warn('Tidak ada user sama sekali — jalankan AdminUserSeeder dulu.');

            return;
        }

        foreach (['matematika', 'fisika', 'biologi', 'kimia'] as $slug) {
            $category = Category::where('slug', $slug)->first();
            if (! $category) {
                $this->command?->warn("Kategori '{$slug}' tidak ditemukan — jalankan CategorySeeder dulu.");

                return;
            }
            $this->categories[$slug] = $category;
        }

        $this->command?->info('Membuat artikel utama (flagship)...');
        $this->seedFlagshipArticles();

        $this->command?->info('Melengkapi artikel tambahan sampai 50 per subkategori...');
        foreach (['matematika', 'fisika', 'biologi', 'kimia'] as $slug) {
            $this->fillUpToFifty($slug);
        }

        $this->command?->info('Selesai.');
    }

    /**
     * 23 artikel utama: ditulis lengkap, orisinal, dengan gambar dari Wikimedia.
     * Termasuk 7 Millennium Prize Problems untuk Matematika, dan 1 contoh
     * artikel multi-kategori (Biokimia -> Biologi + Kimia sekaligus).
     */
    private function seedFlagshipArticles(): void
    {
        $items = array_merge(
            $this->matematikaMillenniumPrizeProblems(),
            $this->fisikaArtikelUtama(),
            $this->biologiArtikelUtama(),
            $this->kimiaArtikelUtama(),
            $this->multiKategoriContoh(),
        );

        foreach ($items as $item) {
            $this->createArticle($item);
        }
    }

    private function createArticle(array $item): Article
    {
        $existing = Article::where('title', $item['title'])->first();
        if ($existing) {
            return $existing;
        }

        $image = WikimediaImage::fetchAndStoreLocal(
            $item['wiki_title'] ?? $item['title'],
            $item['title']
        );

        $article = Article::create([
            'user_id' => $this->author->id,
            'title' => $item['title'],
            'slug' => Str::slug($item['title']).'-'.Str::random(5),
            'excerpt' => $item['excerpt'],
            'content' => $item['content'],
            'featured_image' => $image,
            'status' => 'published',
            'published_at' => now()->subDays(random_int(0, 120)),
            'is_featured' => $item['featured'] ?? false,
        ]);

        $categorySlugs = $item['categories'];
        $categoryIds = array_map(fn ($slug) => $this->categories[$slug]->id, $categorySlugs);
        $article->categories()->sync($categoryIds);

        return $article;
    }

    // =====================================================================
    // MATEMATIKA — 7 Millennium Prize Problems
    // =====================================================================
    private function matematikaMillenniumPrizeProblems(): array
    {
        return [
            [
                'title' => 'Hipotesis Riemann: Misteri Bilangan Prima yang Belum Terpecahkan',
                'wiki_title' => 'Riemann hypothesis',
                'featured' => true,
                'categories' => ['matematika'],
                'excerpt' => 'Salah satu soal terbuka paling terkenal dalam matematika, menyangkut pola tersembunyi di balik distribusi bilangan prima.',
                'content' => <<<HTML
<p>Hipotesis Riemann, dicetuskan oleh Bernhard Riemann pada 1859, adalah salah satu dari tujuh soal Millennium Prize Problems yang diajukan oleh Clay Mathematics Institute pada tahun 2000, masing-masing berhadiah satu juta dolar AS bagi siapa pun yang berhasil membuktikannya. Hingga kini, hipotesis ini tetap belum terpecahkan dan dianggap sebagai salah satu misteri terbesar dalam matematika murni.</p>

<h3>Apa yang Sebenarnya Dinyatakan Hipotesis Ini?</h3>
<p>Inti dari hipotesis ini berkaitan dengan fungsi zeta Riemann, sebuah fungsi kompleks yang didefinisikan sebagai jumlah tak hingga dari pangkat bilangan bulat. Riemann menemukan bahwa fungsi ini memiliki titik-titik tertentu, disebut "nol nontrivial", yang berkaitan erat dengan pola sebaran bilangan prima. Hipotesisnya menyatakan bahwa seluruh nol nontrivial tersebut terletak tepat pada satu garis lurus di bidang kompleks, yang dikenal sebagai "garis kritis" dengan bagian real sama dengan 1/2.</p>

<h3>Mengapa Ini Begitu Penting?</h3>
<p>Bilangan prima tampak tersebar secara acak di antara bilangan bulat, namun matematikawan telah lama mencurigai adanya keteraturan tersembunyi. Jika Hipotesis Riemann terbukti benar, ia akan memberikan perkiraan yang jauh lebih presisi tentang seberapa sering bilangan prima muncul, dengan dampak luas pada teori bilangan, kriptografi, dan bahkan keamanan sistem komputer modern yang bergantung pada sifat bilangan prima besar.</p>

<h3>Sejauh Mana Sudah Diverifikasi?</h3>
<p>Dengan bantuan komputer, para peneliti telah memverifikasi lebih dari sepuluh triliun nol nontrivial pertama dan semuanya konsisten dengan hipotesis ini. Namun, verifikasi numerik untuk sejumlah besar kasus bukanlah bukti matematis yang sah — dibutuhkan bukti umum yang berlaku untuk semua kemungkinan nol, tanpa kecuali, agar hipotesis ini benar-benar dianggap terbukti.</p>

<h3>Upaya dan Pendekatan</h3>
<p>Banyak matematikawan besar, termasuk David Hilbert yang memasukkannya dalam daftar 23 soal pentingnya pada 1900, telah mencoba berbagai pendekatan: dari analisis kompleks klasik, teori spektral, hingga kaitannya dengan fisika kuantum melalui konjektur Hilbert-Pólya yang menduga nol-nol tersebut berhubungan dengan nilai eigen suatu operator fisis. Meski begitu, bukti lengkap masih menjadi misteri yang menantang setiap generasi matematikawan.</p>
HTML,
            ],
            [
                'title' => 'P vs NP: Soal yang Bisa Mengubah Dunia Komputasi',
                'wiki_title' => 'P versus NP problem',
                'categories' => ['matematika'],
                'excerpt' => 'Apakah setiap masalah yang solusinya mudah diverifikasi juga mudah dipecahkan? Jawabannya bisa mengguncang dasar ilmu komputer.',
                'content' => <<<HTML
<p>P vs NP adalah salah satu pertanyaan paling fundamental dalam ilmu komputer teoretis dan matematika, sekaligus salah satu dari tujuh Millennium Prize Problems. Pertanyaannya sederhana untuk diucapkan namun luar biasa sulit dijawab: apakah setiap masalah yang jawabannya mudah diperiksa kebenarannya, juga mudah dipecahkan dari awal?</p>

<h3>Memahami Kelas P dan NP</h3>
<p>Kelas P (polynomial time) berisi kumpulan masalah yang dapat diselesaikan oleh komputer dalam waktu yang wajar, tumbuh secara polinomial seiring bertambahnya ukuran masukan. Kelas NP (nondeterministic polynomial time) berisi masalah yang solusinya, begitu ditemukan, dapat diverifikasi dengan cepat — meski menemukan solusi itu sendiri bisa jadi sangat sulit.</p>

<h3>Contoh Sehari-hari</h3>
<p>Bayangkan menyusun potongan puzzle Sudoku yang sangat besar. Menyusunnya dari nol bisa memakan waktu sangat lama, tetapi jika seseorang memberi Anda jawaban yang sudah jadi, memeriksa apakah jawaban itu benar jauh lebih cepat. Pertanyaan P vs NP pada dasarnya bertanya: apakah selalu ada cara cepat untuk menyusun puzzle itu dari awal, sama cepatnya dengan memeriksa jawabannya?</p>

<h3>Konsekuensi Jika P = NP</h3>
<p>Jika suatu hari terbukti bahwa P sama dengan NP, dampaknya akan sangat besar. Banyak sistem keamanan digital, termasuk enkripsi yang melindungi transaksi perbankan online, mengandalkan asumsi bahwa memecahkan masalah tertentu (seperti memfaktorkan bilangan besar) itu sulit meski memverifikasi jawabannya mudah. Jika P = NP, sebagian besar sistem kriptografi modern berpotensi menjadi rentan.</p>

<h3>Konsensus Saat Ini</h3>
<p>Mayoritas matematikawan dan ilmuwan komputer meyakini bahwa P tidak sama dengan NP — bahwa memang ada masalah yang solusinya mudah diverifikasi tetapi secara fundamental sulit ditemukan. Namun, keyakinan ini belum pernah dibuktikan secara matematis, dan hingga kini soal ini tetap menjadi salah satu misteri terbuka terbesar dalam ilmu komputasi.</p>
HTML,
            ],
            [
                'title' => 'Konjektur Hodge: Menjembatani Aljabar dan Geometri',
                'wiki_title' => 'Hodge conjecture',
                'categories' => ['matematika'],
                'excerpt' => 'Soal Millennium Prize yang menghubungkan dunia geometri aljabar dengan topologi melalui objek matematis abstrak bernama siklus Hodge.',
                'content' => <<<HTML
<p>Konjektur Hodge, dirumuskan oleh matematikawan Skotlandia William Vallance Douglas Hodge pada pertengahan abad ke-20, adalah salah satu soal Millennium Prize Problems yang paling abstrak dan sulit dijelaskan kepada orang awam, karena berada di persimpangan antara geometri aljabar, topologi, dan analisis kompleks.</p>

<h3>Bentuk dan Ruang yang Rumit</h3>
<p>Dalam matematika, kita bisa mempelajari bentuk-bentuk rumit yang disebut "varietas aljabar" — ruang berdimensi tinggi yang didefinisikan oleh persamaan polinomial. Konjektur Hodge menduga bahwa bentuk-bentuk topologis tertentu di dalam ruang ini, yang disebut "kelas kohomologi", dapat dinyatakan sebagai kombinasi dari objek-objek geometris aljabar yang lebih sederhana, disebut "siklus aljabar".</p>

<h3>Mengapa Ini Sulit?</h3>
<p>Tantangannya adalah membuktikan bahwa hubungan ini berlaku secara umum, untuk semua varietas aljabar kompleks, bukan hanya kasus-kasus khusus. Matematikawan telah membuktikan konjektur ini untuk beberapa kategori khusus, namun bukti umum untuk kasus dimensi tinggi tetap menjadi misteri.</p>

<h3>Relevansi dengan Bidang Lain</h3>
<p>Meski terdengar sangat abstrak, konjektur ini memiliki keterkaitan mendalam dengan teori string dalam fisika teoretis, di mana geometri ruang berdimensi tinggi memainkan peran penting dalam memahami struktur fundamental alam semesta. Pemahaman lebih dalam tentang siklus aljabar juga berdampak pada kriptografi berbasis kurva eliptik yang digunakan secara luas dalam keamanan digital modern.</p>

<h3>Status Terkini</h3>
<p>Hingga kini, Konjektur Hodge tetap menjadi salah satu dari enam Millennium Prize Problems yang belum terpecahkan (satu-satunya yang sudah terpecahkan adalah Konjektur Poincaré). Kompleksitas teknisnya membuat kemajuan menuju bukti lengkap berjalan sangat lambat, meski riset di bidang geometri aljabar terus berkembang setiap tahunnya.</p>
HTML,
            ],
            [
                'title' => 'Eksistensi dan Kesenjangan Massa Yang-Mills',
                'wiki_title' => 'Yang–Mills existence and mass gap',
                'categories' => ['matematika', 'fisika'],
                'excerpt' => 'Soal yang menghubungkan matematika murni dengan fisika partikel, menyangkut dasar teoretis dari gaya nuklir kuat.',
                'content' => <<<HTML
<p>Berbeda dari kebanyakan Millennium Prize Problems lainnya yang murni matematis, soal Yang-Mills lahir langsung dari fisika partikel. Teori Yang-Mills, dikembangkan oleh Chen-Ning Yang dan Robert Mills pada 1954, menjadi fondasi matematis di balik Model Standar fisika partikel — kerangka teori yang menjelaskan gaya nuklir kuat dan lemah.</p>

<h3>Apa yang Perlu Dibuktikan?</h3>
<p>Soal ini menuntut dua hal: pertama, membuktikan secara matematis rigor bahwa teori medan kuantum Yang-Mills benar-benar ada sebagai teori yang konsisten (well-defined) dalam ruang empat dimensi. Kedua, membuktikan adanya "kesenjangan massa" (mass gap) — yaitu bahwa partikel-partikel terkecil yang diprediksi teori ini memiliki massa minimum positif, bukan nol.</p>

<h3>Mengapa Kesenjangan Massa Penting?</h3>
<p>Kesenjangan massa menjelaskan mengapa gaya nuklir kuat, yang mengikat quark menjadi proton dan neutron, bekerja hanya pada jarak sangat pendek dalam inti atom, alih-alih menyebar jauh seperti gaya elektromagnetik. Fisikawan telah mengamati fenomena ini secara eksperimental dan mensimulasikannya dengan komputer, namun belum ada yang berhasil membuktikannya secara matematis dari prinsip dasar.</p>

<h3>Kesenjangan Antara Fisika dan Matematika</h3>
<p>Yang menarik dari soal ini adalah fisikawan sudah "menggunakan" teori Yang-Mills setiap hari dalam eksperimen akselerator partikel seperti Large Hadron Collider, dan prediksinya terbukti sangat akurat. Namun secara matematis, fondasi teoretisnya belum pernah dibuktikan secara ketat — sebuah kesenjangan unik antara fisika terapan yang berhasil dan matematika murni yang belum tuntas.</p>

<h3>Tantangan ke Depan</h3>
<p>Menyelesaikan soal ini membutuhkan pengembangan alat matematika baru di bidang teori medan kuantum aksiomatik, sebuah cabang yang masih dalam tahap perkembangan awal. Banyak fisikawan dan matematikawan meyakini bahwa penyelesaian soal ini akan membuka pemahaman yang jauh lebih dalam tentang struktur fundamental materi.</p>
HTML,
            ],
            [
                'title' => 'Eksistensi dan Kehalusan Navier-Stokes',
                'wiki_title' => 'Navier–Stokes existence and smoothness',
                'categories' => ['matematika', 'fisika'],
                'excerpt' => 'Persamaan yang menggambarkan aliran fluida di sekitar kita, namun keteraturan solusinya masih menjadi teka-teki matematis.',
                'content' => <<<HTML
<p>Persamaan Navier-Stokes menggambarkan bagaimana fluida — air, udara, minyak, dan cairan lainnya — bergerak dan mengalir. Persamaan ini digunakan setiap hari oleh insinyur untuk merancang pesawat terbang, memprediksi cuaca, dan mensimulasikan aliran darah dalam tubuh manusia. Namun secara matematis, ada pertanyaan mendasar tentang persamaan ini yang belum terjawab hingga sekarang.</p>

<h3>Apa yang Dipertanyakan?</h3>
<p>Soal Millennium Prize ini menanyakan apakah solusi dari persamaan Navier-Stokes dalam ruang tiga dimensi selalu ada (eksis) dan selalu "halus" (smooth) — artinya tidak muncul keanehan matematis seperti kecepatan fluida yang tiba-tiba menjadi tak hingga di suatu titik. Alternatifnya, peserta bisa membuktikan sebaliknya: menemukan contoh di mana solusi tersebut gagal ada atau menjadi tidak halus.</p>

<h3>Mengapa Ini Penting untuk Dunia Nyata</h3>
<p>Meski insinyur menggunakan persamaan ini setiap hari dengan hasil yang sangat akurat melalui simulasi numerik, ketidakpastian matematis tentang apakah solusi selalu berperilaku baik menjadi masalah teoretis serius. Jika ternyata solusi bisa "meledak" (blow up) dalam waktu terbatas, ini akan mengungkap batasan mendasar dari model matematika yang selama ini dipercaya menggambarkan aliran fluida dengan sempurna.</p>

<h3>Turbulensi: Tantangan Klasik</h3>
<p>Salah satu alasan soal ini sangat sulit adalah fenomena turbulensi — aliran fluida yang kacau dan tak terprediksi, seperti riak air yang bergolak atau asap yang bergulung. Turbulensi melibatkan interaksi pada berbagai skala ruang dan waktu secara bersamaan, membuatnya sangat sulit dianalisis dengan alat matematika standar.</p>

<h3>Perkembangan Riset</h3>
<p>Matematikawan telah membuktikan eksistensi solusi "lemah" (weak solutions) yang memenuhi persamaan dalam pengertian yang lebih longgar, tetapi bukti untuk solusi klasik yang halus dan unik dalam tiga dimensi masih belum ditemukan. Riset di bidang analisis persamaan diferensial parsial terus berupaya mendekati jawaban dari berbagai sudut pendekatan.</p>
HTML,
            ],
            [
                'title' => 'Konjektur Birch dan Swinnerton-Dyer',
                'wiki_title' => 'Birch and Swinnerton-Dyer conjecture',
                'categories' => ['matematika'],
                'excerpt' => 'Soal tentang kurva eliptik yang menjadi jantung dari kriptografi modern dan teori bilangan kontemporer.',
                'content' => <<<HTML
<p>Dirumuskan oleh matematikawan Inggris Bryan Birch dan Peter Swinnerton-Dyer pada 1960-an melalui eksperimen komputer di University of Cambridge, konjektur ini menyangkut objek matematis yang disebut kurva eliptik — kurva yang didefinisikan oleh persamaan tertentu dan memiliki struktur aljabar yang kaya.</p>

<h3>Apa Itu Kurva Eliptik?</h3>
<p>Kurva eliptik bukanlah elips seperti bentuk lonjong yang biasa dibayangkan, melainkan kurva yang memenuhi persamaan berbentuk tertentu. Titik-titik rasional pada kurva ini (titik dengan koordinat berupa pecahan) dapat "dijumlahkan" satu sama lain mengikuti aturan geometris tertentu, membentuk struktur aljabar yang disebut grup.</p>

<h3>Inti dari Konjektur</h3>
<p>Konjektur ini menghubungkan dua sisi yang tampak tidak berkaitan: perilaku suatu fungsi kompleks yang disebut fungsi-L kurva eliptik pada titik tertentu, dengan jumlah titik rasional pada kurva tersebut (yang bisa jadi tak terhingga banyaknya). Secara sederhana, konjektur ini menduga bahwa perilaku fungsi-L dapat memprediksi apakah suatu kurva eliptik memiliki tak hingga banyak solusi rasional atau hanya sejumlah terbatas.</p>

<h3>Mengapa Penting untuk Keamanan Digital</h3>
<p>Kurva eliptik menjadi tulang punggung kriptografi modern, digunakan dalam algoritma keamanan seperti ECDSA yang melindungi transaksi cryptocurrency dan komunikasi terenkripsi di internet. Memahami struktur titik rasional pada kurva eliptik secara lebih dalam berpotensi memiliki dampak langsung terhadap keamanan sistem digital yang kita gunakan sehari-hari.</p>

<h3>Bukti Parsial</h3>
<p>Pada 1980-an dan seterusnya, matematikawan berhasil membuktikan konjektur ini untuk kasus-kasus khusus, terutama untuk kurva eliptik dengan "peringkat analitik" rendah. Namun bukti umum untuk semua kurva eliptik, dengan peringkat berapa pun, masih belum ditemukan hingga hari ini.</p>
HTML,
            ],
            [
                'title' => 'Konjektur Poincaré: Satu-satunya Millennium Prize yang Terpecahkan',
                'wiki_title' => 'Poincaré conjecture',
                'featured' => true,
                'categories' => ['matematika'],
                'excerpt' => 'Kisah unik matematikawan nyentrik Grigori Perelman yang memecahkan salah satu soal tersulit di dunia, lalu menolak hadiah satu juta dolar.',
                'content' => <<<HTML
<p>Dari tujuh Millennium Prize Problems, Konjektur Poincaré adalah satu-satunya yang berhasil dipecahkan hingga saat ini. Dirumuskan oleh matematikawan Prancis Henri Poincaré pada 1904, konjektur ini menyangkut topologi — cabang matematika yang mempelajari bentuk ruang tanpa memedulikan ukuran atau bentuk detailnya, hanya sifat-sifat yang tetap sama meski ruang tersebut diregangkan atau dibengkokkan.</p>

<h3>Pertanyaan Poincaré</h3>
<p>Secara sederhana, konjektur ini bertanya: jika suatu ruang berdimensi tiga memiliki sifat bahwa setiap loop (kalungan) di dalamnya bisa dikerutkan menjadi satu titik tanpa robek atau keluar dari ruang tersebut, apakah ruang itu pasti setara secara topologis dengan bola tiga dimensi biasa? Bayangkan analoginya dalam dua dimensi: permukaan bola bisa "diperciut", tetapi permukaan donat (torus) tidak bisa, karena ada lubang yang mencegah loop tertentu dikerutkan.</p>

<h3>Grigori Perelman dan Bukti yang Mengejutkan Dunia</h3>
<p>Pada 2002-2003, matematikawan Rusia Grigori Perelman mengunggah serangkaian makalah ke internet yang berisi bukti Konjektur Poincaré, menggunakan teknik yang disebut "aliran Ricci dengan pembedahan" (Ricci flow with surgery) — sebuah metode yang awalnya dikembangkan oleh Richard Hamilton. Butuh waktu bertahun-tahun bagi komunitas matematika dunia untuk memverifikasi bukti yang sangat kompleks ini.</p>

<h3>Penolakan Hadiah yang Mengejutkan</h3>
<p>Pada 2010, Clay Mathematics Institute resmi mengonfirmasi bukti Perelman dan menawarkan hadiah satu juta dolar AS kepadanya. Namun secara mengejutkan, Perelman menolak hadiah tersebut, sama seperti ia sebelumnya menolak Fields Medal — penghargaan matematika paling bergengsi di dunia — pada 2006. Ia menyatakan tidak tertarik pada uang atau ketenaran, dan memilih menjauh dari komunitas matematika akademis setelahnya.</p>

<h3>Warisan bagi Matematika</h3>
<p>Meski konjektur khususnya sudah terpecahkan, teknik aliran Ricci yang digunakan Perelman membuka cabang riset baru dalam geometri diferensial dan topologi, dan terus digunakan matematikawan hingga kini untuk mempelajari bentuk-bentuk ruang berdimensi tinggi lainnya.</p>
HTML,
            ],
        ];
    }

    // =====================================================================
    // FISIKA
    // =====================================================================
    private function fisikaArtikelUtama(): array
    {
        return [
            [
                'title' => 'Teori Relativitas Einstein: Ruang, Waktu, dan Gravitasi',
                'wiki_title' => 'Theory of relativity',
                'featured' => true,
                'categories' => ['fisika'],
                'excerpt' => 'Bagaimana Albert Einstein mengubah total pemahaman manusia tentang ruang, waktu, dan gravitasi.',
                'content' => <<<HTML
<p>Pada 1905 dan 1915, Albert Einstein mempublikasikan dua teori yang akan mengubah selamanya cara manusia memahami alam semesta: Relativitas Khusus dan Relativitas Umum. Keduanya menantang gagasan Newton tentang ruang dan waktu yang absolut, menggantinya dengan pandangan yang jauh lebih dinamis dan saling terkait.</p>

<h3>Relativitas Khusus: Ruang dan Waktu yang Relatif</h3>
<p>Relativitas Khusus dibangun di atas dua prinsip: hukum fisika sama di semua kerangka acuan inersial, dan kecepatan cahaya dalam ruang hampa selalu konstan bagi semua pengamat, tidak peduli seberapa cepat mereka bergerak. Konsekuensinya luar biasa: waktu bisa melambat (dilatasi waktu) bagi objek yang bergerak sangat cepat, dan persamaan paling terkenal dalam sejarah sains, E=mc², menunjukkan bahwa massa dan energi sebenarnya adalah bentuk yang setara.</p>

<h3>Relativitas Umum: Gravitasi sebagai Kelengkungan Ruang-Waktu</h3>
<p>Sepuluh tahun kemudian, Einstein memperluas teorinya untuk mencakup gravitasi. Alih-alih memandang gravitasi sebagai gaya tarik-menarik seperti dalam fisika Newton, Relativitas Umum menggambarkannya sebagai kelengkungan ruang-waktu akibat keberadaan massa dan energi. Objek masif seperti matahari melengkungkan ruang di sekitarnya, dan objek lain yang bergerak melewati kelengkungan itu tampak seolah "tertarik" ke arahnya.</p>

<h3>Bukti dan Konfirmasi</h3>
<p>Prediksi teori ini telah dikonfirmasi berulang kali: pembelokan cahaya bintang oleh gravitasi matahari yang diamati saat gerhana 1919, presisi jam GPS yang harus dikoreksi karena efek relativistik, hingga deteksi langsung gelombang gravitasi oleh observatorium LIGO pada 2015 — seabad setelah Einstein pertama kali memprediksinya.</p>

<h3>Dampak bagi Kehidupan Sehari-hari</h3>
<p>Meski terdengar sangat abstrak, Relativitas memiliki aplikasi nyata: sistem navigasi GPS pada ponsel Anda harus memperhitungkan perbedaan laju waktu antara satelit di orbit dan permukaan bumi akibat efek relativistik, karena tanpa koreksi ini, posisi GPS akan meleset beberapa kilometer setiap hari.</p>
HTML,
            ],
            [
                'title' => 'Mekanika Kuantum: Dunia Aneh di Skala Terkecil',
                'wiki_title' => 'Quantum mechanics',
                'categories' => ['fisika'],
                'excerpt' => 'Memahami perilaku aneh partikel subatom yang menantang logika dan intuisi sehari-hari manusia.',
                'content' => <<<HTML
<p>Mekanika kuantum adalah teori fisika yang menggambarkan perilaku materi dan energi pada skala atom dan subatom. Dikembangkan pada awal abad ke-20 oleh fisikawan seperti Max Planck, Niels Bohr, Werner Heisenberg, dan Erwin Schrödinger, teori ini mengungkap bahwa dunia pada skala terkecil berperilaku sangat berbeda dari intuisi sehari-hari manusia.</p>

<h3>Dualitas Gelombang-Partikel</h3>
<p>Salah satu konsep paling mengejutkan dalam mekanika kuantum adalah bahwa partikel seperti elektron dan foton dapat berperilaku baik sebagai partikel maupun gelombang, tergantung bagaimana mereka diamati. Eksperimen celah ganda yang terkenal menunjukkan bahwa elektron tunggal, ketika tidak diamati, tampak "melewati" dua celah sekaligus dan berinterferensi dengan dirinya sendiri seperti gelombang.</p>

<h3>Prinsip Ketidakpastian Heisenberg</h3>
<p>Werner Heisenberg menunjukkan bahwa ada batasan fundamental terhadap seberapa presisi kita bisa mengetahui posisi dan momentum sebuah partikel secara bersamaan. Ini bukan keterbatasan alat ukur, melainkan sifat dasar alam semesta itu sendiri — semakin presisi kita mengukur satu besaran, semakin tidak pasti besaran pasangannya.</p>

<h3>Superposisi dan Keterjeratan Kuantum</h3>
<p>Partikel kuantum dapat berada dalam "superposisi" — kombinasi dari berbagai kemungkinan keadaan sekaligus, hingga diukur. Fenomena "keterjeratan kuantum" (quantum entanglement) menunjukkan bahwa dua partikel dapat menjadi terhubung sedemikian rupa sehingga mengukur satu partikel langsung memengaruhi keadaan partikel pasangannya, bahkan jika keduanya terpisah jarak yang sangat jauh.</p>

<h3>Aplikasi Modern</h3>
<p>Mekanika kuantum bukan hanya teori abstrak — ia menjadi fondasi teknologi modern seperti laser, transistor dalam perangkat elektronik, pencitraan MRI di dunia medis, dan menjadi dasar pengembangan komputer kuantum yang menjanjikan kemampuan komputasi yang jauh melampaui komputer klasik untuk masalah-masalah tertentu.</p>
HTML,
            ],
            [
                'title' => 'Hukum-Hukum Termodinamika: Energi, Panas, dan Entropi',
                'wiki_title' => 'Laws of thermodynamics',
                'categories' => ['fisika'],
                'excerpt' => 'Empat hukum fundamental yang mengatur bagaimana energi mengalir dan mengapa waktu tampak hanya berjalan satu arah.',
                'content' => <<<HTML
<p>Termodinamika adalah cabang fisika yang mempelajari hubungan antara panas, energi, dan kerja. Meski terdengar teknis, prinsip-prinsipnya menjelaskan fenomena mendasar dalam kehidupan sehari-hari, mulai dari cara kerja mesin mobil hingga alasan mengapa es batu selalu mencair di ruangan hangat, tidak pernah sebaliknya.</p>

<h3>Hukum Ke Nol: Kesetimbangan Termal</h3>
<p>Hukum ini menyatakan bahwa jika dua sistem masing-masing berada dalam kesetimbangan termal dengan sistem ketiga, maka keduanya juga berada dalam kesetimbangan termal satu sama lain. Prinsip sederhana ini menjadi dasar bagi konsep suhu itu sendiri dan cara kerja termometer.</p>

<h3>Hukum Pertama: Kekekalan Energi</h3>
<p>Energi tidak dapat diciptakan atau dimusnahkan, hanya dapat berubah bentuk. Ketika Anda menyalakan kompor, energi listrik atau kimia berubah menjadi energi panas. Prinsip ini adalah salah satu hukum paling fundamental di seluruh fisika, berlaku universal dari skala atom hingga skala kosmik.</p>

<h3>Hukum Kedua: Entropi Selalu Meningkat</h3>
<p>Mungkin hukum yang paling filosofis dampaknya: dalam sistem tertutup, entropi (ukuran ketidakteraturan) total akan selalu meningkat atau tetap sama seiring waktu, tidak pernah berkurang. Inilah alasan mengapa panas selalu mengalir dari benda panas ke benda dingin, bukan sebaliknya, dan mengapa banyak fisikawan menganggap hukum ini sebagai penjelasan mengapa waktu tampak mengalir hanya dalam satu arah — dari masa lalu ke masa depan.</p>

<h3>Hukum Ketiga: Batas Suhu Nol Mutlak</h3>
<p>Hukum ini menyatakan bahwa entropi suatu sistem mendekati nilai minimum tetap saat suhunya mendekati nol mutlak (-273,15°C). Namun, mencapai nol mutlak secara sempurna secara fisis tidak mungkin dilakukan, meski para ilmuwan telah berhasil mendekatinya hingga sepersekian derajat di laboratorium.</p>
HTML,
            ],
            [
                'title' => 'Model Standar: Peta Lengkap Partikel Fundamental',
                'wiki_title' => 'Standard Model',
                'categories' => ['fisika'],
                'excerpt' => 'Kerangka teori paling sukses dalam sejarah fisika yang menjelaskan blok-blok bangunan dasar alam semesta.',
                'content' => <<<HTML
<p>Model Standar fisika partikel adalah teori yang menjelaskan partikel-partikel fundamental penyusun alam semesta dan tiga dari empat gaya fundamental yang mengatur interaksi antar partikel tersebut: elektromagnetik, nuklir lemah, dan nuklir kuat (gravitasi belum berhasil disatukan ke dalam kerangka ini).</p>

<h3>Blok-Blok Bangunan Materi</h3>
<p>Menurut Model Standar, semua materi tersusun dari dua jenis partikel fundamental: quark dan lepton. Ada enam jenis quark (up, down, charm, strange, top, bottom) yang berkelompok membentuk partikel seperti proton dan neutron, dan enam jenis lepton, termasuk elektron dan neutrino yang sangat sulit dideteksi karena hampir tidak berinteraksi dengan materi lain.</p>

<h3>Partikel Pembawa Gaya</h3>
<p>Selain partikel materi, ada partikel "pembawa gaya" atau boson: foton yang membawa gaya elektromagnetik, gluon yang mengikat quark bersama melalui gaya nuklir kuat, serta boson W dan Z yang bertanggung jawab atas gaya nuklir lemah yang mendasari peluruhan radioaktif.</p>

<h3>Boson Higgs: Kepingan Terakhir Teka-teki</h3>
<p>Selama puluhan tahun, satu partikel dalam Model Standar tetap bersifat teoretis: Boson Higgs, yang diprediksi menjelaskan mengapa partikel-partikel lain memiliki massa. Pada 2012, para ilmuwan di Large Hadron Collider CERN akhirnya berhasil mendeteksi partikel ini secara langsung, sebuah momen bersejarah yang mengonfirmasi kelengkapan Model Standar dan mengantarkan penghargaan Nobel Fisika 2013.</p>

<h3>Apa yang Belum Dijelaskan</h3>
<p>Meski sangat sukses, Model Standar belum menjelaskan beberapa misteri besar: mengapa gravitasi begitu lemah dibanding gaya lain, apa itu materi gelap dan energi gelap yang mendominasi alam semesta, atau mengapa terdapat lebih banyak materi daripada antimateri. Pencarian teori yang lebih lengkap terus berlangsung hingga hari ini.</p>
HTML,
            ],
            [
                'title' => 'Elektromagnetisme dan Persamaan Maxwell',
                'wiki_title' => "Maxwell's equations",
                'categories' => ['fisika'],
                'excerpt' => 'Empat persamaan elegan yang menyatukan listrik, magnet, dan cahaya menjadi satu teori yang mengubah dunia.',
                'content' => <<<HTML
<p>Pada 1860-an, fisikawan Skotlandia James Clerk Maxwell merumuskan seperangkat empat persamaan yang menyatukan fenomena listrik dan magnet, yang sebelumnya dianggap sebagai dua hal terpisah, menjadi satu teori tunggal: elektromagnetisme. Karya ini dianggap sebagai salah satu pencapaian terbesar dalam sejarah fisika.</p>

<h3>Empat Persamaan yang Mengubah Dunia</h3>
<p>Persamaan Maxwell menggambarkan bagaimana medan listrik dan medan magnet diciptakan oleh muatan listrik, arus listrik, dan bagaimana keduanya saling memengaruhi satu sama lain. Salah satu wawasan paling mendalam dari persamaan ini adalah bahwa medan listrik yang berubah menciptakan medan magnet, dan sebaliknya, medan magnet yang berubah menciptakan medan listrik — sebuah siklus yang saling menguatkan.</p>

<h3>Cahaya Sebagai Gelombang Elektromagnetik</h3>
<p>Dari persamaannya, Maxwell menyimpulkan bahwa perubahan medan listrik dan magnet ini dapat merambat sebagai gelombang, dan ketika ia menghitung kecepatan rambat gelombang tersebut, hasilnya sangat mendekati kecepatan cahaya yang telah diukur sebelumnya. Ini mengarah pada kesimpulan revolusioner: cahaya itu sendiri adalah gelombang elektromagnetik.</p>

<h3>Spektrum Elektromagnetik</h3>
<p>Wawasan ini membuka pemahaman bahwa cahaya tampak hanyalah sebagian kecil dari spektrum yang jauh lebih luas, mencakup gelombang radio, gelombang mikro, inframerah, ultraviolet, sinar-X, dan sinar gamma — semuanya adalah gelombang elektromagnetik dengan panjang gelombang berbeda, mengikuti persamaan fisika yang sama.</p>

<h3>Warisan bagi Teknologi Modern</h3>
<p>Persamaan Maxwell menjadi fondasi bagi hampir seluruh teknologi komunikasi modern: radio, televisi, telepon seluler, wifi, dan satelit komunikasi semuanya bekerja berdasarkan prinsip gelombang elektromagnetik yang pertama kali dirumuskan secara matematis oleh Maxwell lebih dari 150 tahun yang lalu.</p>
HTML,
            ],
        ];
    }

    // =====================================================================
    // BIOLOGI
    // =====================================================================
    private function biologiArtikelUtama(): array
    {
        return [
            [
                'title' => 'Teori Sel: Unit Dasar Kehidupan',
                'wiki_title' => 'Cell theory',
                'featured' => true,
                'categories' => ['biologi'],
                'excerpt' => 'Bagaimana penemuan sel mengubah total pemahaman manusia tentang apa itu makhluk hidup.',
                'content' => <<<HTML
<p>Teori sel adalah salah satu prinsip paling fundamental dalam biologi, menyatakan bahwa semua makhluk hidup tersusun dari satu atau lebih sel, sel adalah unit dasar struktur dan fungsi kehidupan, dan setiap sel baru muncul dari sel yang sudah ada sebelumnya melalui pembelahan.</p>

<h3>Penemuan yang Mengubah Sains</h3>
<p>Pada 1665, ilmuwan Inggris Robert Hooke pertama kali menggunakan mikroskop sederhana untuk mengamati irisan gabus dan melihat struktur berbentuk kotak-kotak kecil yang ia sebut "cellulae" (sel), terinspirasi dari bilik-bilik kecil di biara. Namun baru pada abad ke-19, ilmuwan Jerman Matthias Schleiden dan Theodor Schwann secara formal merumuskan teori sel sebagai prinsip biologi yang berlaku universal untuk tumbuhan dan hewan.</p>

<h3>Dua Jenis Sel Utama</h3>
<p>Sel dibagi menjadi dua kategori besar: sel prokariotik, yang lebih sederhana dan tidak memiliki inti sel dengan membran (ditemukan pada bakteri dan archaea), dan sel eukariotik yang lebih kompleks dengan inti sel bermembran serta berbagai organel khusus (ditemukan pada tumbuhan, hewan, jamur, dan protista).</p>

<h3>Organel: Pabrik-Pabrik Kecil di Dalam Sel</h3>
<p>Di dalam sel eukariotik terdapat berbagai organel dengan fungsi khusus: mitokondria yang menghasilkan energi melalui respirasi seluler, ribosom yang menyintesis protein, retikulum endoplasma yang memproses dan mengangkut molekul, serta nukleus yang menyimpan materi genetik sel dalam bentuk DNA.</p>

<h3>Dari Sel Tunggal Menuju Organisme Kompleks</h3>
<p>Manusia dewasa tersusun dari sekitar 37 triliun sel, semuanya berasal dari satu sel tunggal hasil pembuahan yang membelah dan terspesialisasi menjadi berbagai jenis sel dengan fungsi berbeda — sel saraf, sel otot, sel darah, dan ratusan jenis lainnya — melalui proses yang disebut diferensiasi sel.</p>
HTML,
            ],
            [
                'title' => 'DNA dan Kode Genetika Kehidupan',
                'wiki_title' => 'DNA',
                'categories' => ['biologi'],
                'excerpt' => 'Molekul heliks ganda yang menyimpan instruksi lengkap untuk membangun dan menjalankan setiap makhluk hidup.',
                'content' => <<<HTML
<p>DNA (asam deoksiribonukleat) adalah molekul yang menyimpan seluruh informasi genetik yang dibutuhkan untuk membangun dan menjalankan fungsi suatu organisme. Ditemukan strukturnya pada 1953 oleh James Watson dan Francis Crick, berdasarkan data kristalografi sinar-X dari Rosalind Franklin, penemuan struktur heliks ganda DNA menjadi salah satu tonggak terpenting dalam sejarah biologi.</p>

<h3>Struktur Heliks Ganda</h3>
<p>DNA berbentuk seperti tangga terpilin, dengan dua untai yang saling berpasangan melalui empat basa nitrogen: adenin (A), timin (T), guanin (G), dan sitosin (C). Basa-basa ini selalu berpasangan secara spesifik — A dengan T, dan G dengan C — membentuk kode yang dapat disalin dengan presisi tinggi setiap kali sel membelah diri.</p>

<h3>Dari Gen Menuju Protein</h3>
<p>Segmen DNA tertentu yang disebut gen berisi instruksi untuk membuat protein, yang menjalankan hampir semua fungsi dalam tubuh. Proses ini melibatkan dua tahap: transkripsi, di mana informasi dalam DNA disalin menjadi molekul RNA, dan translasi, di mana RNA tersebut diterjemahkan menjadi rangkaian asam amino yang membentuk protein.</p>

<h3>Pewarisan Sifat Antar Generasi</h3>
<p>DNA adalah alasan mengapa sifat-sifat diturunkan dari orang tua ke anak. Setiap manusia mewarisi separuh DNA dari ibu dan separuh dari ayah, dikombinasikan melalui proses reproduksi seksual, menghasilkan kombinasi genetik unik pada setiap individu (kecuali kembar identik).</p>

<h3>Revolusi Bioteknologi Modern</h3>
<p>Pemahaman tentang DNA telah melahirkan berbagai teknologi revolusioner: tes DNA forensik untuk mengidentifikasi pelaku kejahatan, terapi gen untuk mengobati penyakit genetik, teknologi pengeditan gen CRISPR yang memungkinkan modifikasi DNA dengan presisi tinggi, dan pemetaan genom manusia lengkap yang selesai pada 2003 melalui Human Genome Project.</p>
HTML,
            ],
            [
                'title' => 'Teori Evolusi Darwin: Seleksi Alam dan Asal-Usul Spesies',
                'wiki_title' => 'Evolution',
                'categories' => ['biologi'],
                'excerpt' => 'Bagaimana Charles Darwin menjelaskan keragaman kehidupan di Bumi melalui mekanisme seleksi alam.',
                'content' => <<<HTML
<p>Pada 1859, naturalis Inggris Charles Darwin menerbitkan buku "On the Origin of Species", yang memperkenalkan teori evolusi melalui seleksi alam — sebuah gagasan yang akan mengubah selamanya cara manusia memahami asal-usul dan keragaman kehidupan di Bumi.</p>

<h3>Perjalanan yang Mengubah Segalanya</h3>
<p>Darwin mengembangkan teorinya setelah perjalanan lima tahun dengan kapal HMS Beagle, terutama setelah mengamati variasi burung finch di Kepulauan Galapagos yang memiliki bentuk paruh berbeda-beda, tampaknya beradaptasi dengan sumber makanan yang tersedia di masing-masing pulau.</p>

<h3>Mekanisme Seleksi Alam</h3>
<p>Inti dari teori Darwin adalah gagasan sederhana namun mendalam: dalam setiap populasi, terdapat variasi alami di antara individu. Individu dengan sifat yang lebih menguntungkan untuk bertahan hidup dan bereproduksi di lingkungannya cenderung memiliki lebih banyak keturunan. Seiring generasi, sifat-sifat menguntungkan ini menjadi semakin umum dalam populasi — proses yang disebut "survival of the fittest" atau bertahannya yang paling sesuai dengan lingkungannya.</p>

<h3>Bukti dari Berbagai Bidang Ilmu</h3>
<p>Sejak zaman Darwin, teori evolusi telah didukung oleh bukti dari berbagai bidang: catatan fosil yang menunjukkan perubahan bentuk kehidupan sepanjang jutaan tahun, anatomi perbandingan yang mengungkap struktur serupa pada spesies berbeda, dan yang paling meyakinkan, bukti genetik modern yang menunjukkan kesamaan DNA antar spesies sesuai dengan kedekatan hubungan evolusionernya.</p>

<h3>Evolusi yang Masih Berlangsung</h3>
<p>Evolusi bukan hanya peristiwa masa lalu — ia terus berlangsung hingga kini dan dapat diamati langsung, misalnya pada resistensi bakteri terhadap antibiotik yang berkembang dalam hitungan tahun, atau perubahan warna ngengat di Inggris yang beradaptasi dengan polusi industri pada abad ke-19.</p>
HTML,
            ],
            [
                'title' => 'Fotosintesis: Cara Tumbuhan Mengubah Cahaya Menjadi Makanan',
                'wiki_title' => 'Photosynthesis',
                'categories' => ['biologi'],
                'excerpt' => 'Proses biokimia mendasar yang menopang hampir seluruh kehidupan di Bumi dengan mengubah energi matahari menjadi energi kimia.',
                'content' => <<<HTML
<p>Fotosintesis adalah proses yang digunakan tumbuhan, alga, dan beberapa jenis bakteri untuk mengubah energi cahaya matahari menjadi energi kimia dalam bentuk glukosa, sambil melepaskan oksigen sebagai produk sampingan. Proses ini menjadi fondasi bagi hampir seluruh rantai makanan di Bumi.</p>

<h3>Dua Tahap Utama</h3>
<p>Fotosintesis terjadi dalam dua tahap utama: reaksi terang, yang berlangsung di membran tilakoid dalam kloroplas dan menangkap energi cahaya untuk menghasilkan molekul pembawa energi (ATP dan NADPH); serta reaksi gelap atau siklus Calvin, yang menggunakan energi tersebut untuk mengubah karbon dioksida dari udara menjadi molekul gula.</p>

<h3>Peran Klorofil</h3>
<p>Warna hijau pada daun berasal dari klorofil, pigmen yang menyerap cahaya matahari terutama pada panjang gelombang merah dan biru, sambil memantulkan cahaya hijau — itulah sebabnya daun tampak hijau bagi mata manusia. Klorofil terletak di dalam kloroplas, organel khusus yang hanya ditemukan pada sel tumbuhan dan alga.</p>

<h3>Persamaan Sederhana dengan Dampak Luar Biasa</h3>
<p>Secara kimiawi, fotosintesis dapat diringkas dalam persamaan sederhana: enam molekul karbon dioksida ditambah enam molekul air, dengan bantuan energi cahaya, menghasilkan satu molekul glukosa dan enam molekul oksigen. Proses inilah yang menghasilkan hampir seluruh oksigen yang kita hirup, dan menjadi sumber energi utama bagi hampir semua rantai makanan di planet ini.</p>

<h3>Relevansi bagi Perubahan Iklim</h3>
<p>Karena fotosintesis menyerap karbon dioksida dari atmosfer, hutan dan lautan (melalui fitoplankton) berperan sebagai "penyerap karbon" alami yang membantu mengatur iklim Bumi. Inilah salah satu alasan mengapa deforestasi menjadi perhatian serius dalam diskusi perubahan iklim global.</p>
HTML,
            ],
            [
                'title' => 'Sistem Imun Manusia: Pertahanan Tak Kenal Lelah',
                'wiki_title' => 'Immune system',
                'categories' => ['biologi'],
                'excerpt' => 'Bagaimana tubuh manusia mendeteksi dan melawan miliaran ancaman mikroskopis setiap harinya.',
                'content' => <<<HTML
<p>Sistem imun adalah jaringan kompleks sel, jaringan, dan organ yang bekerja sama untuk melindungi tubuh dari serangan patogen seperti bakteri, virus, jamur, dan parasit. Setiap detik, sistem ini bekerja tanpa henti mendeteksi dan menetralkan ancaman, seringkali tanpa kita sadari.</p>

<h3>Dua Lapis Pertahanan</h3>
<p>Sistem imun bekerja melalui dua mekanisme utama: imunitas bawaan (innate immunity), yang merupakan pertahanan cepat dan umum seperti kulit sebagai penghalang fisik, sel darah putih yang menelan patogen, serta respons peradangan; dan imunitas adaptif (adaptive immunity), yang lebih lambat berkembang namun sangat spesifik dan memiliki kemampuan "mengingat" patogen yang pernah dihadapi sebelumnya.</p>

<h3>Sel-Sel Kunci dalam Pertahanan Tubuh</h3>
<p>Berbagai jenis sel darah putih memainkan peran khusus: sel T membantu mengoordinasikan respons imun dan langsung menyerang sel yang terinfeksi, sel B memproduksi antibodi yang menargetkan patogen spesifik, sedangkan sel makrofag dan neutrofil bertindak sebagai "pasukan pemakan" yang menelan dan menghancurkan mikroorganisme asing.</p>

<h3>Bagaimana Vaksin Bekerja</h3>
<p>Vaksin memanfaatkan kemampuan "mengingat" dari sistem imun adaptif. Dengan memperkenalkan versi lemah, tidak aktif, atau sebagian kecil dari suatu patogen, vaksin melatih sistem imun untuk mengenali ancaman tersebut tanpa menyebabkan penyakit, sehingga tubuh dapat merespons jauh lebih cepat dan efektif jika suatu saat terpapar patogen asli.</p>

<h3>Ketika Sistem Imun Salah Sasaran</h3>
<p>Terkadang sistem imun bisa keliru menyerang sel tubuh sendiri, menyebabkan penyakit autoimun seperti lupus, rheumatoid arthritis, atau diabetes tipe 1. Di sisi lain, sistem imun yang terlalu lemah (imunodefisiensi) membuat tubuh sangat rentan terhadap infeksi yang bagi orang sehat biasanya tidak berbahaya.</p>
HTML,
            ],
        ];
    }

    // =====================================================================
    // KIMIA
    // =====================================================================
    private function kimiaArtikelUtama(): array
    {
        return [
            [
                'title' => 'Tabel Periodik: Peta Seluruh Unsur di Alam Semesta',
                'wiki_title' => 'Periodic table',
                'featured' => true,
                'categories' => ['kimia'],
                'excerpt' => 'Bagaimana Dmitri Mendeleev menyusun sistem klasifikasi yang mampu memprediksi keberadaan unsur yang belum ditemukan.',
                'content' => <<<HTML
<p>Tabel periodik adalah salah satu pencapaian paling elegan dalam sejarah sains: sebuah sistem yang mengorganisasikan seluruh unsur kimia yang diketahui berdasarkan nomor atom, konfigurasi elektron, dan sifat kimia yang berulang secara periodik.</p>

<h3>Kejeniusan Mendeleev</h3>
<p>Pada 1869, kimiawan Rusia Dmitri Mendeleev menyusun unsur-unsur yang dikenal saat itu berdasarkan massa atom dan sifat kimianya, lalu menemukan pola berulang yang mencolok. Yang paling mengesankan, Mendeleev sengaja meninggalkan ruang kosong dalam tabelnya untuk unsur yang menurutnya pasti ada namun belum ditemukan, dan bahkan memprediksi sifat-sifat unsur tersebut dengan akurat — prediksi yang kemudian terbukti benar ketika unsur seperti germanium ditemukan.</p>

<h3>Struktur dan Organisasi</h3>
<p>Tabel periodik modern menyusun unsur dalam baris (periode) dan kolom (golongan). Unsur dalam golongan yang sama cenderung memiliki sifat kimia serupa karena memiliki jumlah elektron valensi (elektron terluar) yang sama, yang menentukan bagaimana suatu unsur bereaksi dengan unsur lain.</p>

<h3>Dari Hidrogen hingga Unsur Sintetis</h3>
<p>Tabel periodik saat ini berisi 118 unsur, dimulai dari hidrogen (unsur paling sederhana dengan satu proton) hingga unsur-unsur superberat yang hanya bisa dibuat secara sintetis di laboratorium dan hanya bertahan sepersekian detik sebelum meluruh. Unsur-unsur terbaru ini dinamai sesuai penemunya atau institusi penelitian, seperti oganesson yang dinamai dari fisikawan Rusia Yuri Oganessian.</p>

<h3>Alat Prediksi yang Masih Relevan</h3>
<p>Lebih dari 150 tahun setelah diciptakan, tabel periodik tetap menjadi alat fundamental bagi kimiawan modern untuk memprediksi bagaimana unsur akan bereaksi, merancang material baru, dan memahami struktur dasar dari seluruh materi di alam semesta.</p>
HTML,
            ],
            [
                'title' => 'Ikatan Kimia: Perekat yang Menyatukan Materi',
                'wiki_title' => 'Chemical bond',
                'categories' => ['kimia'],
                'excerpt' => 'Memahami gaya-gaya yang mengikat atom menjadi molekul, membentuk segala sesuatu di sekitar kita.',
                'content' => <<<HTML
<p>Ikatan kimia adalah gaya tarik-menarik yang menyatukan atom-atom menjadi molekul atau senyawa. Tanpa ikatan kimia, materi di alam semesta hanya akan berupa atom-atom individu yang mengambang bebas — tidak ada air, tidak ada protein, tidak ada kehidupan seperti yang kita kenal.</p>

<h3>Ikatan Ion: Tarik-Menarik Muatan Berlawanan</h3>
<p>Ikatan ion terbentuk ketika satu atom melepaskan elektron dan atom lain menerimanya, menciptakan ion bermuatan positif dan negatif yang saling tarik-menarik. Contoh klasiknya adalah garam dapur (natrium klorida), di mana atom natrium melepaskan elektron ke atom klorin, membentuk kristal ion yang stabil.</p>

<h3>Ikatan Kovalen: Berbagi Elektron</h3>
<p>Berbeda dengan ikatan ion, ikatan kovalen terbentuk ketika dua atom berbagi pasangan elektron. Molekul air (H2O), misalnya, terbentuk dari ikatan kovalen antara satu atom oksigen dan dua atom hidrogen. Ikatan kovalen bisa tunggal, rangkap dua, atau rangkap tiga, tergantung berapa banyak pasangan elektron yang dibagikan.</p>

<h3>Ikatan Logam dan Sifat Uniknya</h3>
<p>Dalam logam, elektron valensi tidak terikat pada atom tertentu, melainkan bergerak bebas membentuk "lautan elektron" yang mengelilingi ion-ion logam positif. Struktur unik inilah yang menjelaskan mengapa logam menghantarkan listrik dan panas dengan baik, serta bersifat lentur dan dapat ditempa.</p>

<h3>Gaya Antarmolekul: Ikatan yang Lebih Lemah namun Penting</h3>
<p>Selain ikatan kimia utama, ada juga gaya antarmolekul yang lebih lemah seperti ikatan hidrogen, yang meski lebih lemah dari ikatan kovalen atau ion, sangat penting dalam menentukan sifat fisik zat — ikatan hidrogen antar molekul air, misalnya, adalah alasan mengapa air memiliki titik didih yang relatif tinggi dan berperan penting dalam menjaga struktur DNA serta protein dalam tubuh makhluk hidup.</p>
HTML,
            ],
            [
                'title' => 'Kimia Organik: Ilmu di Balik Molekul Kehidupan',
                'wiki_title' => 'Organic chemistry',
                'categories' => ['kimia'],
                'excerpt' => 'Cabang kimia yang mempelajari senyawa karbon, dasar dari seluruh kehidupan dan industri modern.',
                'content' => <<<HTML
<p>Kimia organik adalah cabang kimia yang mempelajari senyawa berbasis karbon. Meski namanya "organik", cabang ilmu ini tidak hanya mencakup molekul yang berasal dari makhluk hidup, tetapi seluruh senyawa karbon, termasuk plastik sintetis dan obat-obatan buatan manusia.</p>

<h3>Mengapa Karbon Begitu Istimewa?</h3>
<p>Karbon memiliki kemampuan unik untuk membentuk empat ikatan kovalen sekaligus dan dapat berikatan dengan dirinya sendiri membentuk rantai panjang, cincin, dan struktur bercabang yang sangat kompleks. Sifat inilah yang memungkinkan keragaman molekul organik yang luar biasa besar — dari molekul sederhana seperti metana hingga protein raksasa dengan ribuan atom.</p>

<h3>Gugus Fungsi: Penentu Sifat Molekul</h3>
<p>Kimiawan organik mempelajari "gugus fungsi" — kelompok atom spesifik dalam suatu molekul yang menentukan bagaimana molekul tersebut bereaksi. Contohnya gugus hidroksil (-OH) yang menjadi ciri alkohol, atau gugus karboksil (-COOH) yang menjadi ciri asam organik seperti asam asetat dalam cuka.</p>

<h3>Molekul Kehidupan</h3>
<p>Hampir seluruh molekul yang menyusun makhluk hidup adalah senyawa organik: karbohidrat yang menyimpan energi, lipid yang membentuk membran sel, protein yang menjalankan hampir semua fungsi biologis, dan asam nukleat (DNA dan RNA) yang menyimpan informasi genetik.</p>

<h3>Dampak bagi Industri Modern</h3>
<p>Pemahaman kimia organik telah melahirkan industri farmasi modern, menciptakan obat-obatan yang menyelamatkan jutaan nyawa, plastik dan material sintetis yang digunakan dalam hampir setiap aspek kehidupan sehari-hari, serta bahan bakar dan pupuk yang menopang pertanian serta transportasi global.</p>
HTML,
            ],
            [
                'title' => 'Reaksi Asam-Basa: Kimia yang Mengatur Kehidupan',
                'wiki_title' => 'Acid–base reaction',
                'categories' => ['kimia'],
                'excerpt' => 'Dari cuka di dapur hingga keseimbangan pH darah manusia, memahami salah satu jenis reaksi kimia paling fundamental.',
                'content' => <<<HTML
<p>Reaksi asam-basa adalah salah satu jenis reaksi kimia paling mendasar dan paling sering dijumpai dalam kehidupan sehari-hari, mulai dari proses pencernaan dalam lambung, kerja pembersih rumah tangga, hingga menjaga kestabilan pH darah manusia agar tetap dalam rentang yang aman untuk kehidupan.</p>

<h3>Mendefinisikan Asam dan Basa</h3>
<p>Terdapat beberapa teori untuk mendefinisikan asam dan basa. Menurut teori Brønsted-Lowry yang paling umum digunakan, asam adalah zat yang dapat menyumbangkan proton (ion hidrogen, H+), sementara basa adalah zat yang dapat menerima proton tersebut. Ketika asam dan basa bereaksi, mereka saling menetralkan, menghasilkan air dan garam.</p>

<h3>Skala pH: Mengukur Keasaman</h3>
<p>Skala pH, berkisar dari 0 hingga 14, digunakan untuk mengukur tingkat keasaman atau kebasaan suatu larutan. Nilai pH 7 dianggap netral (seperti air murni), nilai di bawah 7 bersifat asam (semakin rendah semakin asam, seperti asam lambung dengan pH sekitar 1,5-3,5), dan nilai di atas 7 bersifat basa (seperti sabun dengan pH sekitar 9-10).</p>

<h3>Peran Vital dalam Tubuh Manusia</h3>
<p>Tubuh manusia sangat bergantung pada keseimbangan pH yang presisi. Darah manusia normalnya memiliki pH sekitar 7,35-7,45, dan penyimpangan kecil saja dari rentang ini dapat menyebabkan kondisi medis serius seperti asidosis (pH terlalu rendah) atau alkalosis (pH terlalu tinggi). Tubuh memiliki sistem penyangga (buffer) kimiawi yang canggih untuk mempertahankan keseimbangan ini.</p>

<h3>Aplikasi dalam Kehidupan Sehari-hari</h3>
<p>Reaksi asam-basa dimanfaatkan dalam berbagai konteks: soda kue (basa) yang bereaksi dengan cuka atau air jeruk nipis (asam) menghasilkan gas karbon dioksida yang membuat kue mengembang, tablet antasida yang menetralkan kelebihan asam lambung, hingga pengujian kualitas tanah pertanian untuk menentukan jenis tanaman yang cocok ditanam.</p>
HTML,
            ],
            [
                'title' => 'Termokimia: Energi dalam Setiap Reaksi Kimia',
                'wiki_title' => 'Thermochemistry',
                'categories' => ['kimia'],
                'excerpt' => 'Mempelajari bagaimana energi diserap dan dilepaskan setiap kali ikatan kimia terbentuk atau terputus.',
                'content' => <<<HTML
<p>Termokimia adalah cabang kimia yang mempelajari hubungan antara reaksi kimia dan perubahan energi, khususnya dalam bentuk panas. Setiap kali ikatan kimia terbentuk atau terputus, energi diserap atau dilepaskan — prinsip ini menjadi dasar bagi segala sesuatu mulai dari cara kerja baterai hingga proses pembakaran bahan bakar.</p>

<h3>Reaksi Eksotermik dan Endotermik</h3>
<p>Reaksi kimia dapat dikategorikan berdasarkan arah aliran energinya. Reaksi eksotermik melepaskan energi ke lingkungan, biasanya dalam bentuk panas, seperti pembakaran kayu atau bahan bakar. Sebaliknya, reaksi endotermik menyerap energi dari lingkungan, seperti proses fotosintesis pada tumbuhan yang menyerap energi cahaya matahari.</p>

<h3>Konsep Entalpi</h3>
<p>Entalpi adalah ukuran kandungan energi total suatu sistem kimia pada tekanan konstan. Perubahan entalpi (ΔH) selama reaksi kimia menunjukkan seberapa banyak energi diserap atau dilepaskan. Nilai ΔH negatif menandakan reaksi eksotermik, sedangkan nilai positif menandakan reaksi endotermik.</p>

<h3>Hukum Kekekalan Energi dalam Reaksi Kimia</h3>
<p>Termokimia berakar dari hukum pertama termodinamika: energi tidak dapat diciptakan atau dimusnahkan. Ketika bahan bakar terbakar, energi kimia yang tersimpan dalam ikatan molekulnya diubah menjadi energi panas dan cahaya, namun jumlah energi total sebelum dan sesudah reaksi tetap sama.</p>

<h3>Aplikasi Praktis dalam Industri dan Kehidupan</h3>
<p>Pemahaman termokimia sangat penting dalam berbagai bidang: menentukan nilai kalori makanan dan bahan bakar, merancang baterai yang lebih efisien untuk kendaraan listrik, mengembangkan proses industri yang hemat energi, hingga memahami bagaimana perubahan iklim dipengaruhi oleh energi yang dilepaskan dari pembakaran bahan bakar fosil dalam skala besar.</p>
HTML,
            ],
        ];
    }

    // =====================================================================
    // CONTOH MULTI-KATEGORI: Biologi + Kimia sekaligus
    // =====================================================================
    private function multiKategoriContoh(): array
    {
        return [
            [
                'title' => 'Biokimia: Jembatan Antara Biologi dan Kimia',
                'wiki_title' => 'Biochemistry',
                'featured' => true,
                'categories' => ['biologi', 'kimia'], // contoh artikel dengan 2 kategori sekaligus
                'excerpt' => 'Bagaimana reaksi kimia menjadi dasar dari setiap proses kehidupan, dari napas hingga pikiran.',
                'content' => <<<HTML
<p>Biokimia adalah bidang ilmu yang mempelajari proses kimia yang terjadi di dalam dan berkaitan dengan makhluk hidup. Sebagai persimpangan antara biologi dan kimia, bidang ini mengungkap bahwa kehidupan, pada dasarnya, adalah rangkaian reaksi kimia yang terorganisasi dengan sangat presisi.</p>

<h3>Molekul-Molekul Penyusun Kehidupan</h3>
<p>Biokimia mempelajari empat kelas utama molekul biologis: karbohidrat sebagai sumber energi utama, lipid yang membentuk membran sel dan menyimpan energi jangka panjang, protein yang menjalankan hampir seluruh fungsi seluler sebagai enzim dan struktur, serta asam nukleat (DNA dan RNA) yang menyimpan dan mentransmisikan informasi genetik.</p>

<h3>Enzim: Katalis Kehidupan</h3>
<p>Salah satu konsep sentral dalam biokimia adalah enzim — protein khusus yang mempercepat reaksi kimia dalam tubuh hingga jutaan kali lipat tanpa ikut bereaksi. Tanpa enzim, reaksi kimia yang dibutuhkan untuk mencerna makanan, menyalin DNA, atau menghasilkan energi seluler akan berjalan terlalu lambat untuk menopang kehidupan.</p>

<h3>Metabolisme: Jaringan Reaksi yang Tak Pernah Berhenti</h3>
<p>Di dalam setiap sel makhluk hidup, ribuan reaksi kimia berlangsung secara simultan dalam jaringan yang disebut metabolisme, terbagi menjadi katabolisme (pemecahan molekul untuk menghasilkan energi) dan anabolisme (penyusunan molekul kompleks menggunakan energi tersebut). Proses respirasi seluler, misalnya, mengubah glukosa dan oksigen menjadi energi dalam bentuk ATP, karbon dioksida, dan air — sebuah rangkaian reaksi kimia murni yang menjadi dasar bagi semua aktivitas kehidupan.</p>

<h3>Mengapa Biokimia Penting bagi Kedokteran Modern</h3>
<p>Hampir seluruh kemajuan kedokteran modern berakar dari pemahaman biokimia: obat-obatan dirancang untuk berinteraksi dengan protein dan enzim tertentu dalam tubuh, diagnosis penyakit sering melibatkan pengukuran kadar molekul biokimia dalam darah, dan terapi genetik terbaru bekerja langsung pada tingkat molekul DNA dan RNA. Bidang ini menjadi bukti nyata bahwa memahami kimia dengan mendalam berarti memahami kehidupan itu sendiri.</p>
HTML,
            ],
        ];
    }

    // =====================================================================
    // PELENGKAP: topik tambahan agar tiap subkategori mencapai 50 artikel.
    // Ini kerangka artikel terstruktur (Pendahuluan, Konsep Inti, Penerapan,
    // Kesimpulan) yang ditulis generik per-topik — BUKAN hasil salin dari
    // Wikipedia atau sumber lain. Cocok sebagai draft awal yang siap Anda
    // perkaya lebih lanjut lewat panel admin.
    // =====================================================================
    private function topikPelengkap(string $slug): array
    {
        return match ($slug) {
            'matematika' => [
                'Aljabar Linear dan Ruang Vektor', 'Kalkulus Diferensial', 'Kalkulus Integral',
                'Teori Graf dan Aplikasinya', 'Statistika Deskriptif', 'Teori Peluang',
                'Geometri Non-Euclidean', 'Bilangan Kompleks', 'Deret Fibonacci dan Rasio Emas',
                'Teori Himpunan', 'Logika Matematika', 'Trigonometri Dasar',
                'Fungsi dan Pemetaan', 'Matriks dan Determinan', 'Persamaan Diferensial',
                'Teori Bilangan Dasar', 'Kombinatorika', 'Topologi Dasar',
                'Aljabar Abstrak dan Grup', 'Analisis Real', 'Bilangan Irasional dan Transenden',
                'Konstanta Pi dan Sejarahnya', 'Konstanta Euler', 'Teorema Pythagoras',
                'Fraktal dan Geometri Rekursif', 'Teori Permainan', 'Optimisasi Matematis',
                'Metode Numerik', 'Aljabar Boolean', 'Teori Kode dan Kriptografi Matematis',
                'Barisan dan Deret Tak Hingga', 'Fungsi Eksponensial dan Logaritma',
                'Geometri Analitik', 'Vektor dalam Ruang Tiga Dimensi', 'Sistem Bilangan dan Basis',
                'Teorema Fermat Terakhir', 'Konjektur Goldbach', 'Bilangan Prima dan Distribusinya',
                'Matematika Diskrit', 'Teori Chaos dan Sistem Dinamis', 'Aljabar Polinomial',
                'Transformasi Geometri', 'Matematika dalam Kriptografi Modern', 'Model Matematika dalam Sains',
                'Sejarah Perkembangan Angka Nol', 'Sistem Bilangan Romawi vs Modern',
            ],
            'fisika' => [
                'Hukum Newton tentang Gerak', 'Gelombang dan Getaran', 'Optik dan Sifat Cahaya',
                'Fisika Nuklir Dasar', 'Radioaktivitas dan Peluruhan', 'Gaya Gravitasi Newton',
                'Fisika Zat Padat', 'Superkonduktivitas', 'Fisika Plasma',
                'Astrofisika dan Bintang', 'Lubang Hitam', 'Big Bang dan Kosmologi',
                'Fisika Atom', 'Spektroskopi', 'Termodinamika Statistik',
                'Mekanika Fluida', 'Akustik dan Gelombang Suara', 'Fisika Semikonduktor',
                'Energi Terbarukan dan Fisika Panel Surya', 'Fisika Medis dan Radiologi',
                'Teori Dawai (String Theory)', 'Materi Gelap dan Energi Gelap',
                'Fisika Kuantum Terapan', 'Laser dan Aplikasinya', 'Fisika Nanoteknologi',
                'Gaya Elektromagnetik', 'Hukum Coulomb', 'Rangkaian Listrik Dasar',
                'Fisika Atmosfer', 'Fisika Kelautan', 'Dinamika Rotasi',
                'Momentum dan Kekekalan Momentum', 'Fisika Suhu dan Kalor',
                'Efek Fotolistrik', 'Sinar Kosmik', 'Detektor Partikel',
                'Reaktor Nuklir dan Energi Fisi', 'Fusi Nuklir', 'Fisika Semesta Awal',
                'Prinsip Relativitas Galileo', 'Gerak Harmonik Sederhana',
                'Fisika Material Modern', 'Fisika Kriogenik', 'Percepatan Partikel',
                'Fisika Interstellar', 'Hukum Kekekalan Energi Mekanik',
            ],
            'biologi' => [
                'Anatomi Tubuh Manusia', 'Sistem Saraf dan Otak', 'Sistem Peredaran Darah',
                'Sistem Pernapasan', 'Sistem Pencernaan Manusia', 'Genetika Populasi',
                'Ekologi dan Ekosistem', 'Keanekaragaman Hayati', 'Taksonomi Makhluk Hidup',
                'Mikrobiologi Dasar', 'Virus dan Cara Kerjanya', 'Bakteri dan Perannya di Alam',
                'Jamur dan Kingdom Fungi', 'Botani: Ilmu Tumbuhan', 'Zoologi: Ilmu Hewan',
                'Reproduksi pada Manusia', 'Perkembangan Embrio', 'Hormon dan Sistem Endokrin',
                'Homeostasis Tubuh', 'Rantai Makanan dan Jaring Makanan',
                'Adaptasi Makhluk Hidup', 'Simbiosis dalam Alam', 'Konservasi Spesies Langka',
                'Bioteknologi dan Rekayasa Genetika', 'Kloning dan Etikanya',
                'Neuroscience: Cara Kerja Otak', 'Memori dan Pembelajaran pada Otak',
                'Genetika Mendel', 'Mutasi Genetik', 'Biologi Sel Kanker',
                'Sistem Kekebalan Tubuh Lanjutan', 'Mikrobioma Usus Manusia',
                'Biologi Kelautan', 'Biologi Konservasi', 'Ekosistem Hutan Hujan Tropis',
                'Biologi Perkembangan', 'Anatomi Perbandingan Vertebrata',
                'Biologi Molekuler', 'Ribosom dan Sintesis Protein', 'Siklus Sel dan Pembelahan',
                'Biologi Evolusioner Modern', 'Epigenetika', 'Biodiversitas Indonesia',
                'Rekayasa Jaringan dan Sel Punca', 'Biologi Penuaan',
            ],
            'kimia' => [
                'Stoikiometri dan Perhitungan Kimia', 'Larutan dan Konsentrasi',
                'Kesetimbangan Kimia', 'Laju Reaksi Kimia', 'Kimia Anorganik Dasar',
                'Kimia Polimer', 'Elektrokimia dan Baterai', 'Korosi Logam',
                'Kimia Lingkungan', 'Polusi Kimia dan Dampaknya', 'Kimia Analitik',
                'Kromatografi', 'Spektrometri Massa', 'Kimia Inti dan Radiokimia',
                'Kimia Permukaan dan Katalis', 'Nanokimia', 'Kimia Farmasi',
                'Kimia Pangan', 'Kimia Kosmetik', 'Kimia Forensik',
                'Kimia Industri', 'Proses Haber-Bosch dan Pupuk', 'Kimia Polimer Plastik',
                'Kimia Minyak Bumi', 'Kimia Air dan Pengolahan Air Bersih',
                'Sifat Koligatif Larutan', 'Kimia Koordinasi', 'Kimia Kuantum Dasar',
                'Struktur Atom dan Model Atom', 'Konfigurasi Elektron',
                'Hukum Gas Ideal', 'Kimia Gas Mulia', 'Kimia Unsur Transisi',
                'Reaksi Redoks', 'Elektrolisis', 'Kimia Green Chemistry',
                'Kimia Material Semikonduktor', 'Sintesis Senyawa Organik',
                'Stereokimia dan Isomer', 'Kimia Polimer Alami',
                'Kimia Atmosfer dan Lapisan Ozon', 'Kimia Baterai Lithium',
                'Kimia Nutrisi dan Vitamin', 'Kimia Deterjen dan Sabun',
            ],
            default => [],
        };
    }

    private function fillUpToFifty(string $slug): void
    {
        $category = $this->categories[$slug];
        $current = $category->articles()->count();
        $target = 50;

        if ($current >= $target) {
            $this->command?->info("  {$category->name}: sudah {$current} artikel, dilewati.");

            return;
        }

        $topics = $this->topikPelengkap($slug);
        $needed = $target - $current;
        $topics = array_slice($topics, 0, $needed);

        foreach ($topics as $topic) {
            $title = $topic;
            if (Article::where('title', $title)->exists()) {
                $title = $topic.' — Panduan Lengkap';
            }

            $this->createArticle([
                'title' => $title,
                'wiki_title' => $topic,
                'categories' => [$slug],
                'excerpt' => "Pembahasan tentang {$topic} dalam bidang ".$category->name.'.',
                'content' => $this->generatePlaceholderBody($topic, $category->name),
            ]);
        }

        $this->command?->info('  '.$category->name.': ditambahkan '.count($topics).' artikel (total sekarang '.$category->articles()->count().').');
    }

    /**
     * Kerangka artikel generik untuk topik pelengkap — orisinal, tidak
     * disalin dari sumber manapun, dan ditulis agar mudah diperkaya lebih
     * lanjut lewat panel admin.
     */
    private function generatePlaceholderBody(string $topic, string $categoryName): string
    {
        return <<<HTML
<p>{$topic} adalah salah satu topik penting dalam bidang {$categoryName} yang memiliki relevansi baik secara akademis maupun dalam penerapannya di kehidupan sehari-hari. Artikel ini memberikan gambaran awal tentang konsep tersebut sebagai titik awal pembahasan lebih lanjut.</p>

<h3>Pengantar Konsep</h3>
<p>Memahami {$topic} membutuhkan pengenalan terhadap prinsip-prinsip dasar yang melandasinya. Topik ini biasanya dipelajari sebagai bagian dari kurikulum {$categoryName} tingkat lanjut, dan menjadi fondasi bagi pemahaman konsep-konsep yang lebih kompleks di bidang terkait.</p>

<h3>Mengapa Topik Ini Penting</h3>
<p>Studi tentang {$topic} memberikan wawasan berharga yang dapat diterapkan dalam berbagai konteks, mulai dari penelitian akademis hingga aplikasi praktis dalam industri dan teknologi. Pemahaman yang mendalam mengenai topik ini membuka jalan bagi eksplorasi lebih jauh dalam bidang {$categoryName}.</p>

<h3>Penerapan dan Relevansi</h3>
<p>Konsep-konsep yang terkait dengan {$topic} sering dijumpai dalam berbagai penerapan praktis, dan pemahamannya membantu menjelaskan fenomena-fenomena yang lebih luas dalam bidang {$categoryName}.</p>

<p><em>Catatan editor: artikel ini adalah kerangka awal yang dibuat otomatis melalui seeder dan siap dilengkapi lebih lanjut melalui panel admin dengan detail, contoh, dan referensi tambahan.</em></p>
HTML;
    }
}
