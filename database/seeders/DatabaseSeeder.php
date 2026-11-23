<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@npblog.local'],
            [
                'name' => 'Dapit',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Default Settings
        $settings = [
            'blog_name' => 'NPblog',
            'blog_description' => 'Blog personal minimalis bergaya hand-drawn oleh Dapit.',
            'owner_name' => 'Dapit',
            'owner_bio' => 'Software engineer, penggiat keamanan siber, dan penikmat kesederhanaan sistem.',
            'posts_per_page' => 9,
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 3. 4 Categories
        $categoriesData = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Catatan dan panduan seputar pemrograman, web development, dan inovasi teknologi modern.',
            ],
            [
                'name' => 'Cybersecurity',
                'slug' => 'cybersecurity',
                'description' => 'Eksplorasi keamanan informasi, audit sistem, analisis kerentanan, dan pertahanan siber.',
            ],
            [
                'name' => 'Infrastructure',
                'slug' => 'infrastructure',
                'description' => 'Manajemen server Linux, orkestrasi container, jaringan handal, dan otomatisasi deployment.',
            ],
            [
                'name' => 'Personal',
                'slug' => 'personal',
                'description' => 'Refleksi, pemikiran personal, produktivitas, dan cerita di balik layar kehidupan digital.',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $catData) {
            $categories[$catData['slug']] = Category::query()->firstOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );
        }

        // 4. Tags
        $tagsData = ['laravel', 'php', 'security', 'docker', 'linux', 'web', 'devops', 'notes'];
        $tags = [];
        foreach ($tagsData as $tagName) {
            $tags[$tagName] = Tag::query()->firstOrCreate(
                ['slug' => $tagName],
                ['name' => ucfirst($tagName)]
            );
        }

        // 5. 12 Articles (3 per category)
        $articlesData = [
            // Technology
            [
                'category' => 'technology',
                'title' => 'Membangun Arsitektur Web Minimalis Tanpa Ketergantungan Berlebih',
                'body' => "## Kesederhanaan dalam Rekayasa Perangkat Lunak\n\nDalam ekosistem pengembangan web modern, sering kali kita tergoda untuk menambahkan tumpukan pustaka eksternal yang rumit untuk memecahkan masalah sederhana. Pendekatan minimalis berfokus pada apa yang benar-benar esensial.\n\n```php\nfinal class SimplePipeline {\n    public function handle(Request \$request): Response {\n        return new Response('Minimal and fast');\n    }\n}\n```\n\nDengan memanfaatkan fitur bawaan bahasa pemrograman dan framework inti, kita mengurangi beban pemeliharaan jangka panjang dan meningkatkan efisiensi aplikasi secara signifikan.",
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(12),
                'tags' => ['web', 'php', 'notes'],
            ],
            [
                'category' => 'technology',
                'title' => 'Eksplorasi Fitur Tersembunyi pada PHP 8.3 yang Jarang Disadari',
                'body' => "## Fitur Baru yang Membuat Kode Lebih Rapi\n\nPHP 8.3 membawa sejumlah peningkatan penting yang sering luput dari perhatian pengembang harian. Mulai dari validasi JSON terstandarisasi, penanganan typing yang lebih ketat, hingga peningkatan performa engine runtime.\n\n### Mengapa Typed Class Constants Penting?\n\nDengan adanya typed class constants, kontrak data antar modul aplikasi menjadi jauh lebih terjamin dan meminimalisir bug run-time yang sulit dilacak.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(9),
                'tags' => ['php', 'laravel'],
            ],
            [
                'category' => 'technology',
                'title' => 'Mengapa Blog Ini Menggunakan Gaya Hand-Drawn Visual',
                'body' => "## Menyuntikkan Jiwa ke Antarmuka Digital\n\nSebagian besar website saat ini terlihat seragam dengan sudut membulat standar dan palet warna yang serupa. Pendekatan hand-drawn menghadirkan kehangatan buku catatan fisik ke dalam layar monitor.\n\nSetiap garis tinta dan sudut asimetris dirancang dengan perhitungan CSS presisi tanpa membebani performa browser pengguna.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'tags' => ['web', 'notes'],
            ],

            // Cybersecurity
            [
                'category' => 'cybersecurity',
                'title' => 'Prinsip Hardening Linux Server untuk Aplikasi Produksi',
                'body' => "## Fondasi Keamanan Tingkat Server\n\nKeamanan aplikasi web tidak bermula dari baris kode saja, melainkan dari konfigurasi sistem operasi tempat server berjalan.\n\n1. Matikan autentikasi SSH berbasis kata sandi\n2. Pasang firewall minimalis dengan `ufw` atau `nftables`\n3. Aktifkan auto-updates untuk security patches penting\n\nLangkah-langkah sederhana ini mampu mengeliminasi lebih dari 90% upaya serangan otomatis di internet terbuka.",
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(14),
                'tags' => ['security', 'linux', 'devops'],
            ],
            [
                'category' => 'cybersecurity',
                'title' => 'Mencegah Celah SSRF pada Fitur Import dan Webhook',
                'body' => "## Memahami Vektor Serangan SSRF\n\nServer-Side Request Forgery (SSRF) terjadi saat aplikasi menerima URL dari pengguna lalu melakukan request ke URL tersebut tanpa validasi ketat. Penyerang dapat memanfaatkan ini untuk mengakses metadata instance cloud atau port internal lokal.\n\nValidasi IP privat dan domain allowlist adalah pertahanan mutlak yang tidak boleh diabaikan.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(8),
                'tags' => ['security', 'web'],
            ],
            [
                'category' => 'cybersecurity',
                'title' => 'Audit Keamanan Header HTTP: CSP, HSTS, dan Referrer Policy',
                'body' => "## Mengunci Perilaku Browser Klien\n\nHTTP security headers merupakan lapisan pertahanan awal yang murah komputasi namun sangat efektif. Menetapkan Content-Security-Policy yang ketat dapat menghentikan eksekusi script XSS berbahaya secara tuntas.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'tags' => ['security', 'web'],
            ],

            // Infrastructure
            [
                'category' => 'infrastructure',
                'title' => 'Manajemen Container Rootless dengan Podman di Lingkungan Server',
                'body' => "## Keamanan Container Tanpa Hak Akses Root\n\nMenjalankan container engine sebagai root membawa risiko eskalasi hak akses bila terjadi container breakout. Podman memungkinkan eksekusi container sepenuhnya dalam user namespace biasa.\n\n```bash\npodman run -d --name app-service -p 8080:80 nginx:alpine\n```\n\nDengan pendekatan ini, integritas host server tetap terjaga bahkan ketika service di dalam container mengalami kompromi.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'tags' => ['docker', 'linux', 'devops'],
            ],
            [
                'category' => 'infrastructure',
                'title' => 'Strategi Backup Otomatis Terverifikasi untuk Database MySQL',
                'body' => "## Backup yang Tidak Pernah Diuji Bukanlah Backup\n\nBanyak tim memiliki script backup rutin, namun tidak pernah memvalidasi apakah file dump tersebut benar-benar dapat direstore dengan sempurna saat insiden terjadi.\n\nOtomatisasi pengujian restore pada instance sementara adalah satu-satunya jaminan pemulihan bencana yang sahih.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'tags' => ['devops', 'linux'],
            ],
            [
                'category' => 'infrastructure',
                'title' => 'Optimasi Nginx FastCGI Cache untuk Latensi di Bawah 50ms',
                'body' => "## Mempercepat Response Time Konten Statis dan Dinamis\n\nNginx tidak hanya bertindak sebagai reverse proxy, namun juga cache engine yang luar biasa tangguh untuk mereduksi beban backend PHP-FPM secara drastis saat lonjakan lalu lintas.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->addDays(2), // Scheduled future article!
                'tags' => ['devops', 'linux', 'web'],
            ],

            // Personal
            [
                'category' => 'personal',
                'title' => 'Menulis Sebagai Cara Berpikir yang Lebih Jernih',
                'body' => "## Artikulasi Ide ke Dalam Kata\n\nSering kali kita mengira telah memahami sebuah konsep teknis secara menyeluruh, sampai kita mencoba menuliskannya untuk orang lain. Proses menulis memaksa kita merapikan kepingan logika yang berserakan.\n\nMenulis bukan sekadar mendokumentasikan apa yang sudah kita tahu, melainkan alat untuk menemukan apa yang belum kita mengerti.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(15),
                'tags' => ['notes'],
            ],
            [
                'category' => 'personal',
                'title' => 'Membangun Ruang Tenang di Tengah Kebisingan Notifikasi',
                'body' => "## Menjaga Fokus di Era Serba Instan\n\nNotifikasi tiada henti memecah konsentrasi kerja mendalam. Membatasi kanal komunikasi dan menjadwalkan blok waktu tanpa interupsi adalah salah satu investasi terbesar bagi produktivitas jangka panjang.",
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'tags' => ['notes'],
            ],
            [
                'category' => 'personal',
                'title' => 'Draf Konsep Eksplorasi Homelab Rumah',
                'body' => "## Catatan Awal Rencana Homelab\n\nIni adalah draf catatan mengenai konfigurasi mini PC berdaya rendah untuk kebutuhan homelab pribadi, monitoring jaringan rumah, dan backup terdesentralisasi.",
                'is_featured' => false,
                'status' => 'draft',
                'published_at' => null, // Draft article!
                'tags' => ['notes', 'linux'],
            ],
        ];

        foreach ($articlesData as $art) {
            $cat = $categories[$art['category']];
            $title = $art['title'];
            $slug = Str::slug($title);
            $body = $art['body'];
            $wordCount = str_word_count($body);
            $readingTime = max(1, (int) ceil($wordCount / 200));

            // Generate simple sanitized body_html for now
            $bodyHtml = '<p>'.str_replace("\n\n", '</p><p>', e($body)).'</p>';
            $excerpt = Str::limit(strip_tags($body), 160);

            $article = Article::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $admin->id,
                    'category_id' => $cat->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'body_html' => $bodyHtml,
                    'status' => $art['status'],
                    'published_at' => $art['published_at'],
                    'is_featured' => $art['is_featured'],
                    'language' => 'id',
                    'word_count' => $wordCount,
                    'reading_time' => $readingTime,
                ]
            );

            // Sync tags
            $tagIds = [];
            foreach ($art['tags'] as $t) {
                if (isset($tags[$t])) {
                    $tagIds[] = $tags[$t]->id;
                }
            }
            $article->tags()->sync($tagIds);
        }
    }
}
