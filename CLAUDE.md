# CLAUDE.md — PRISMA v2
## Spec Definitiva para Claude Code AI
### PHP 8.2 MVC Puro · MySQL 8 · Redis 7 · 8 Módulos · Design System Deep Sea

> **PRISMA** é uma plataforma SaaS de gestão de links e QR Codes com módulos de
> encurtamento, encapsulamento, hub digital, ferramentas, importação de favoritos
> e launcher inteligente. PHP 8.2 MVC puro (sem framework), MySQL 8, Redis 7.
> Empresa: PageUp Sistemas · Porto Velho, RO · pageupsistemas@gmail.com

---

## 1. Stack Técnico

| Camada | Tecnologia |
|---|---|
| Linguagem | PHP 8.2+ |
| Banco de dados | MySQL 8.0+ (PDO, JSON columns) |
| Cache / Filas | Redis 7 (via phpredis ext + predis/predis) |
| Dependências PHP | Composer |
| QR Code lib | `chillerlan/php-qrcode ^5` |
| QR Scanner lib | `khanamiryan/qrcode-detector-decoder ^2` |
| QR-Logo (imagem) | `intervention/image ^3` (wrapper ImageMagick) |
| E-mail | `phpmailer/phpmailer ^6` |
| Env vars | `vlucas/phpdotenv ^5.6` |
| Redis client | `predis/predis ^2` (fallback quando ext não disponível) |
| Frontend | Bootstrap 5.3 + Bootstrap Icons 1.11 + Chart.js 4 (CDN) |
| Fontes | Google Fonts: Syne 800/700 (display) · Inter 400/500 (body) |
| Servidor dev | PHP built-in `php -S localhost:8000 -t public/` |
| Servidor prod | Nginx + PHP-FPM |
| Filas prod | Supervisor (2 workers) |
| Padrão | MVC puro sem framework — Router próprio |

### Extensões PHP Obrigatórias

```
ext-pdo_mysql   ext-redis (phpredis)   ext-imagick   ext-gd
ext-fileinfo    ext-zip                ext-intl       ext-mbstring
ext-json        ext-curl               ext-openssl
```

---

## 2. Design System — Deep Sea + Spectrum

### Paleta Principal

```css
/* ── Fundos ─────────────────────────────── */
--color-base:       #0D1B2A;   /* Deep Sea / Ink Black */
--color-surface:    #152233;   /* Cards, sidebars */
--color-elevated:   #1A2E44;   /* Dropdowns, modais */
--color-border:     #1E3A5F;   /* Bordas, dividers */

/* ── Texto ───────────────────────────────── */
--color-text-primary:   #E8F4F8;
--color-text-secondary: #94A3B8;
--color-text-muted:     #4E6B87;

/* ── Acentos ─────────────────────────────── */
--color-accent:     #2E86AB;   /* Azul oceano */
--color-accent-alt: #A23B72;   /* Magenta */
--color-success:    #22C55E;
--color-warning:    #F59E0B;
--color-danger:     #EF4444;

/* ── Gradientes ──────────────────────────── */
--gradient-brand:    linear-gradient(135deg, #0D1B2A 0%, #1A3A5C 100%);
--gradient-accent:   linear-gradient(135deg, #2E86AB 0%, #1A5276 100%);
--gradient-card:     linear-gradient(160deg, #152233 0%, #0D1B2A 100%);
--gradient-spectrum: linear-gradient(90deg,
    #FF6B6B 0%, #FF9F43 20%, #FFD93D 40%,
    #6BCB77 60%, #4D96FF 80%, #C77DFF 100%);

/* ── Sombras ─────────────────────────────── */
--shadow-sm:  0 1px 3px rgba(0,0,0,.5);
--shadow-md:  0 4px 12px rgba(0,0,0,.4);
--shadow-lg:  0 8px 32px rgba(0,0,0,.5);

/* ── Tipografia ──────────────────────────── */
--font-display: 'Syne', system-ui, sans-serif;   /* peso 700/800 — títulos */
--font-sans:    'Inter', system-ui, sans-serif;  /* peso 400/500 — corpo */
--font-mono:    'JetBrains Mono', 'Fira Code', monospace;
--radius:       8px;
--radius-lg:    16px;
```

### Google Fonts (CDN — no `<head>` de todos os layouts)

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
```

### Classes Bootstrap Override (`public/assets/css/app.css`)

```css
body {
    background-color: var(--color-base);
    color: var(--color-text-primary);
    font-family: var(--font-sans);
}
h1, h2, h3, .display-font { font-family: var(--font-display); }
.card {
    background: var(--gradient-card);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}
.navbar   { background: var(--color-surface) !important; border-bottom: 1px solid var(--color-border); }
.sidebar  { background: var(--color-surface); border-right: 1px solid var(--color-border); min-height: 100vh; }
.btn-primary { background: var(--gradient-accent); border: none; }
.form-control, .form-select {
    background-color: var(--color-elevated);
    border-color: var(--color-border);
    color: var(--color-text-primary);
}
.table { --bs-table-bg: transparent; --bs-table-striped-bg: rgba(255,255,255,.03); --bs-table-hover-bg: rgba(46,134,171,.1); }
.spectrum-bar { background: var(--gradient-spectrum); height: 3px; }
.badge-type    { background: var(--color-accent); }
.badge-success { background: var(--color-success); }
.badge-danger  { background: var(--color-danger); }
```

### Ícones
**Bootstrap Icons 1.11** via CDN (`bi-*`). Nenhuma lib adicional.

---

## 3. Estrutura de Diretórios Completa

```
prisma/
├── CLAUDE.md
├── composer.json
├── composer.lock
├── .env.example
├── .env                          ← NÃO comitar
├── .htaccess                     ← redireciona root para public/
├── .gitignore
│
├── public/                       ← DOCUMENT ROOT (Nginx/Apache webroot)
│   ├── index.php                 ← Front Controller
│   ├── .htaccess
│   └── assets/
│       ├── css/app.css
│       ├── js/
│       │   ├── app.js
│       │   └── launcher-widget.js  ← bundle Shadow DOM (gerado, não editar)
│       └── img/
│           ├── logo.svg
│           └── logo-dark.svg
│
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── QRController.php         ← QR Code + QR-Logo
│   │   ├── HistoryController.php
│   │   ├── BatchController.php
│   │   ├── AnalyticsController.php
│   │   ├── ScannerController.php
│   │   ├── RedirectController.php   ← Short URL + Encapsulador
│   │   ├── DownloadController.php
│   │   ├── LinkController.php       ← Encurtador + Encapsulador CRUD
│   │   ├── HubController.php        ← Hub Digital (admin + public)
│   │   ├── BookmarkController.php   ← Importador de Favoritos
│   │   ├── ToolController.php       ← Mini Ferramentas (público)
│   │   ├── LauncherController.php   ← API do Launcher widget
│   │   ├── ProfileController.php
│   │   ├── AdminController.php
│   │   └── ApiController.php        ← API REST v1
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Organization.php
│   │   ├── QRCode.php
│   │   ├── Scan.php
│   │   ├── Batch.php
│   │   ├── BatchItem.php
│   │   ├── Link.php                 ← encurtador + encapsulador
│   │   ├── LinkClick.php
│   │   ├── Bookmark.php
│   │   ├── BookmarkFolder.php
│   │   ├── HubPage.php
│   │   ├── HubBlock.php
│   │   ├── LauncherLink.php
│   │   ├── Job.php                  ← fila MySQL (audit fallback)
│   │   └── CreditTransaction.php
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   │   ├── auth.php
│   │   │   └── public.php           ← Hub Digital + Ferramentas (sem sidebar)
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── register.php
│   │   │   ├── forgot.php
│   │   │   └── reset.php
│   │   ├── dashboard/index.php
│   │   ├── qr/
│   │   │   ├── generate.php
│   │   │   └── show.php
│   │   ├── history/index.php
│   │   ├── batch/index.php
│   │   ├── analytics/
│   │   │   ├── index.php
│   │   │   └── show.php
│   │   ├── scanner/index.php
│   │   ├── links/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── show.php
│   │   ├── hub/
│   │   │   ├── index.php
│   │   │   ├── editor.php
│   │   │   └── public.php           ← prisma.app/hub/{slug}
│   │   ├── bookmarks/
│   │   │   ├── index.php
│   │   │   └── import.php
│   │   ├── tools/
│   │   │   └── index.php            ← público, sem login
│   │   ├── profile/index.php
│   │   ├── admin/
│   │   │   ├── users.php
│   │   │   └── settings.php
│   │   └── errors/
│   │       ├── 403.php
│   │       ├── 404.php
│   │       └── 500.php
│   │
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Controller.php      ← BaseController
│   │   ├── Model.php           ← BaseModel (PDO wrapper)
│   │   ├── Database.php        ← singleton PDO
│   │   ├── Cache.php           ← Redis wrapper (predis)
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Session.php
│   │   ├── Auth.php
│   │   ├── Validator.php
│   │   ├── View.php
│   │   ├── Middleware.php
│   │   └── helpers.php
│   │
│   └── Services/
│       ├── QRGenerator.php         ← 12 tipos QR Code
│       ├── QRLogoService.php       ← QR-Logo + Índice PRISMA de Legibilidade
│       ├── QRScannerService.php
│       ├── AnalyticsService.php
│       ├── BatchProcessor.php
│       ├── ShortURLService.php
│       ├── StorageService.php      ← abstração local/S3
│       ├── QueueService.php        ← Redis LPUSH/BRPOP
│       ├── MailerService.php
│       ├── BookmarkImporterService.php  ← NETSCAPE HTML parser
│       ├── LauncherIndexService.php     ← JSON index + Redis cache
│       ├── GeoService.php
│       └── CurrencyService.php         ← cache BRL/USD/EUR
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── redis.php
│   └── routes.php
│
├── database/
│   ├── migrations/
│   │   ├── 001_users.sql
│   │   ├── 002_password_resets.sql
│   │   ├── 003_organizations.sql
│   │   ├── 004_qrcodes.sql
│   │   ├── 005_scans.sql
│   │   ├── 006_batches.sql
│   │   ├── 007_links.sql
│   │   ├── 008_link_clicks.sql
│   │   ├── 009_bookmarks.sql
│   │   ├── 010_hub_pages.sql
│   │   ├── 011_hub_blocks.sql
│   │   ├── 012_launcher_links.sql
│   │   ├── 013_jobs.sql
│   │   └── 014_credit_transactions.sql
│   ├── seeds/
│   │   └── AdminSeeder.php
│   └── migrate.php
│
├── storage/
│   ├── qrcodes/       ← PNGs e SVGs gerados
│   ├── qr-logos/      ← QRs com logo renderizados
│   ├── batches/       ← ZIPs de lotes
│   ├── uploads/       ← logos enviados (logos de clientes)
│   ├── bookmarks/     ← arquivos .html exportados
│   └── logs/
│
├── workers/
│   ├── queue-worker.php   ← consome Redis BRPOP
│   └── scheduler.php      ← tarefas periódicas (limpeza, relatórios)
│
├── supervisor/
│   └── prisma-worker.conf ← config Supervisor
│
└── tests/
    └── .gitkeep
```

---

## 4. Banco de Dados MySQL 8

### `config/database.php`

```php
return [
    'driver'   => 'mysql',
    'host'     => env('DB_HOST', '127.0.0.1'),
    'port'     => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'prisma'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
```

### `config/redis.php`

```php
return [
    'host'     => env('REDIS_HOST', '127.0.0.1'),
    'port'     => (int) env('REDIS_PORT', 6379),
    'password' => env('REDIS_PASSWORD', null),
    'database' => (int) env('REDIS_DB', 0),
    'timeout'  => 2.0,
];
```

---

## 5. Migrations SQL (ordem de execução)

### `001_users.sql`
```sql
CREATE TABLE IF NOT EXISTS users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)     NOT NULL UNIQUE,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(180) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,
    role            ENUM('superadmin','admin','user') NOT NULL DEFAULT 'user',
    avatar          VARCHAR(255),
    email_verified  TINYINT(1)   NOT NULL DEFAULT 0,
    api_key         VARCHAR(64)  UNIQUE,
    credits         INT UNSIGNED NOT NULL DEFAULT 0,
    active          TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at   TIMESTAMP    NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role),
    INDEX idx_api   (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `002_password_resets.sql`
```sql
CREATE TABLE IF NOT EXISTS password_resets (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(180) NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    expires_at TIMESTAMP    NOT NULL,
    used_at    TIMESTAMP    NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `003_organizations.sql`
```sql
CREATE TABLE IF NOT EXISTS organizations (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid       CHAR(36)     NOT NULL UNIQUE,
    name       VARCHAR(120) NOT NULL,
    slug       VARCHAR(80)  NOT NULL UNIQUE,
    logo       VARCHAR(255),
    owner_id   BIGINT UNSIGNED NOT NULL,
    plan       ENUM('starter','business','whitelabel') NOT NULL DEFAULT 'starter',
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    settings   JSON,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS organization_users (
    organization_id BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    role            ENUM('owner','admin','member') NOT NULL DEFAULT 'member',
    joined_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (organization_id, user_id),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `004_qrcodes.sql`
```sql
CREATE TABLE IF NOT EXISTS qrcodes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)     NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NULL,
    type            ENUM(
                        'url','text','email','phone','sms','whatsapp',
                        'wifi','vcard','geo','event','bitcoin','pix'
                    ) NOT NULL DEFAULT 'url',
    content         TEXT         NOT NULL,
    label           VARCHAR(120),
    fg_color        CHAR(7)      NOT NULL DEFAULT '#000000',
    bg_color        CHAR(7)      NOT NULL DEFAULT '#FFFFFF',
    transparent     TINYINT(1)   NOT NULL DEFAULT 0,
    ecc_level       ENUM('L','M','Q','H') NOT NULL DEFAULT 'M',
    size            SMALLINT UNSIGNED NOT NULL DEFAULT 512,
    logo_path       VARCHAR(255),           ← logo do cliente (upload)
    logo_size       TINYINT UNSIGNED NOT NULL DEFAULT 20,
    has_qr_logo     TINYINT(1)   NOT NULL DEFAULT 0,  ← QR-Logo renderizado
    legibility_index TINYINT UNSIGNED NULL,            ← Índice PRISMA 0–100
    file_png        VARCHAR(255),
    file_svg        VARCHAR(255),
    file_logo_png   VARCHAR(255),           ← QR-Logo final
    short_code      VARCHAR(10)  UNIQUE,
    short_url       VARCHAR(512),
    scan_count      INT UNSIGNED NOT NULL DEFAULT 0,
    render_status   ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'done',
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_user    (user_id),
    INDEX idx_type    (type),
    INDEX idx_short   (short_code),
    INDEX idx_status  (render_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `005_scans.sql`
```sql
CREATE TABLE IF NOT EXISTS scans (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    qr_id      BIGINT UNSIGNED NOT NULL,
    ip         VARCHAR(45),
    ip_hash    CHAR(64),          ← SHA-256 do IP (anonimizado para LGPD)
    user_agent VARCHAR(512),
    referer    VARCHAR(512),
    country    CHAR(2),
    city       VARCHAR(100),
    device     ENUM('desktop','mobile','tablet','bot','unknown') DEFAULT 'unknown',
    scanned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (qr_id) REFERENCES qrcodes(id) ON DELETE CASCADE,
    INDEX idx_qr_id   (qr_id),
    INDEX idx_scanned (scanned_at),
    INDEX idx_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `006_batches.sql`
```sql
CREATE TABLE IF NOT EXISTS batches (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(120) NOT NULL,
    total      INT UNSIGNED NOT NULL DEFAULT 0,
    done       INT UNSIGNED NOT NULL DEFAULT 0,
    zip_file   VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS batch_items (
    id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    qr_id    BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (qr_id)    REFERENCES qrcodes(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `007_links.sql` — Encurtador + Encapsulador
```sql
CREATE TABLE IF NOT EXISTS links (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)     NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NULL,
    slug            VARCHAR(40)  NOT NULL UNIQUE,    ← código curto
    destination     TEXT         NOT NULL,           ← URL de destino final
    title           VARCHAR(200),
    tags_json       JSON,                            ← array de tags
    -- Encapsulador: tipo
    wrapper_type    ENUM('none','intersticial','utm','conditional','ab','cloaking')
                    NOT NULL DEFAULT 'none',
    -- UTM params (wrapper_type = 'utm')
    utm_json        JSON,                            ← {source,medium,campaign,term,content}
    -- Condicional (wrapper_type = 'conditional')
    conditions_json JSON,                            ← [{field,operator,value,destination}]
    -- A/B Split (wrapper_type = 'ab')
    ab_variants     JSON,                            ← [{url,weight}] soma = 100
    -- Intersticial branding
    intersticial_template VARCHAR(40),
    -- Expiração
    expires_at      TIMESTAMP    NULL,
    -- Analytics
    click_count     INT UNSIGNED NOT NULL DEFAULT 0,
    active          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_slug    (slug),
    INDEX idx_user    (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `008_link_clicks.sql`
```sql
CREATE TABLE IF NOT EXISTS link_clicks (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    link_id    BIGINT UNSIGNED NOT NULL,
    ip_hash    CHAR(64),
    country    CHAR(2),
    device     ENUM('desktop','mobile','tablet','bot','unknown') DEFAULT 'unknown',
    referer    VARCHAR(512),
    variant    VARCHAR(10),            ← para A/B
    clicked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE,
    INDEX idx_link    (link_id),
    INDEX idx_clicked (clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `009_bookmarks.sql` — Importador de Favoritos
```sql
CREATE TABLE IF NOT EXISTS bookmark_folders (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    name       VARCHAR(200)    NOT NULL,
    parent_id  BIGINT UNSIGNED NULL,    ← hierarquia
    position   INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES bookmark_folders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookmarks (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    folder_id  BIGINT UNSIGNED NULL,
    title      VARCHAR(500)    NOT NULL,
    url        TEXT            NOT NULL,
    favicon    VARCHAR(512),
    health     ENUM('ok','broken','unknown') NOT NULL DEFAULT 'unknown',
    health_checked_at TIMESTAMP NULL,
    in_launcher TINYINT(1)     NOT NULL DEFAULT 0,
    position   INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (folder_id) REFERENCES bookmark_folders(id) ON DELETE SET NULL,
    INDEX idx_user    (user_id),
    INDEX idx_folder  (folder_id),
    INDEX idx_health  (health),
    INDEX idx_launcher (in_launcher)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `010_hub_pages.sql` — Hub Digital
```sql
CREATE TABLE IF NOT EXISTS hub_pages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36)     NOT NULL UNIQUE,
    user_id         BIGINT UNSIGNED NOT NULL,
    organization_id BIGINT UNSIGNED NULL,
    slug            VARCHAR(80)  NOT NULL UNIQUE,   ← prisma.app/hub/{slug}
    title           VARCHAR(200) NOT NULL,
    bio             TEXT,
    avatar          VARCHAR(255),
    theme_color     CHAR(7)      NOT NULL DEFAULT '#2E86AB',
    active          TINYINT(1)   NOT NULL DEFAULT 1,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `011_hub_blocks.sql`
```sql
CREATE TABLE IF NOT EXISTS hub_blocks (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hub_id     BIGINT UNSIGNED NOT NULL,
    type       ENUM(
                   'link','group','whatsapp','social','map',
                   'schedule','catalog','video','contact_form'
               ) NOT NULL,
    title      VARCHAR(200),
    blocks_json JSON NOT NULL,           ← dados específicos do tipo
    position   INT UNSIGNED NOT NULL DEFAULT 0,
    active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hub_id) REFERENCES hub_pages(id) ON DELETE CASCADE,
    INDEX idx_hub      (hub_id),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `012_launcher_links.sql`
```sql
CREATE TABLE IF NOT EXISTS launcher_links (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT UNSIGNED NOT NULL,
    source         ENUM('qrcode','shortlink','bookmark','manual') NOT NULL,
    source_id      BIGINT UNSIGNED NULL,  ← id na tabela de origem
    title          VARCHAR(300)   NOT NULL,
    url            TEXT           NOT NULL,
    icon           VARCHAR(100),          ← nome de ícone Bootstrap ou URL
    tags_json      JSON,
    roles_json     JSON,                  ← roles que vêem este link
    use_count      INT UNSIGNED   NOT NULL DEFAULT 0,
    last_used_at   TIMESTAMP      NULL,
    active         TINYINT(1)     NOT NULL DEFAULT 1,
    created_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user   (user_id),
    INDEX idx_source (source, source_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### `013_jobs.sql` — Fila Redis com audit MySQL
```sql
CREATE TABLE IF NOT EXISTS jobs (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue        VARCHAR(80)    NOT NULL DEFAULT 'default',
    payload      JSON           NOT NULL,
    status       ENUM('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
    attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    error        TEXT,
    available_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at   TIMESTAMP      NULL,
    finished_at  TIMESTAMP      NULL,
    created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_queue_status (queue, status),
    INDEX idx_available    (available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### `014_credit_transactions.sql`
```sql
CREATE TABLE IF NOT EXISTS credit_transactions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    type        ENUM('purchase','consume','refund','bonus') NOT NULL,
    amount      INT             NOT NULL,   ← positivo = entrada, negativo = saída
    description VARCHAR(255),
    reference   VARCHAR(100),              ← uuid do QR-Logo, por exemplo
    balance_after INT UNSIGNED  NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 6. Core MVC — Implementação

### `app/Core/Database.php` — Singleton PDO

```php
namespace App\Core;

class Database {
    private static ?\PDO $instance = null;

    public static function get(): \PDO {
        if (self::$instance === null) {
            $cfg = require ROOT . '/config/database.php';
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
            self::$instance = new \PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        }
        return self::$instance;
    }
}
```

### `app/Core/Cache.php` — Redis via Predis

```php
namespace App\Core;

use Predis\Client as Predis;

class Cache {
    private static ?Predis $client = null;

    public static function client(): Predis {
        if (self::$client === null) {
            $cfg = require ROOT . '/config/redis.php';
            self::$client = new Predis([
                'scheme'   => 'tcp',
                'host'     => $cfg['host'],
                'port'     => $cfg['port'],
                'password' => $cfg['password'],
                'database' => $cfg['database'],
                'read_write_timeout' => $cfg['timeout'],
            ]);
        }
        return self::$client;
    }

    public static function get(string $key): mixed {
        $v = self::client()->get($key);
        return $v !== null ? json_decode($v, true) : null;
    }

    public static function set(string $key, mixed $value, int $ttl = 300): void {
        self::client()->setex($key, $ttl, json_encode($value));
    }

    public static function del(string $key): void {
        self::client()->del([$key]);
    }

    public static function incr(string $key): int {
        return self::client()->incr($key);
    }

    public static function expire(string $key, int $ttl): void {
        self::client()->expire($key, $ttl);
    }
}
```

### `app/Core/Model.php` — BaseModel

```php
namespace App\Core;

abstract class Model {
    protected static string $table = '';
    protected static string $pk    = 'id';

    public static function find(int $id): ?array;
    public static function findBy(string $col, mixed $val): ?array;
    public static function findByUuid(string $uuid): ?array;
    public static function all(string $order = 'id DESC', int $limit = 1000): array;
    public static function create(array $data): int;        // retorna lastInsertId
    public static function update(int $id, array $data): bool;
    public static function delete(int $id): bool;
    public static function count(string $where = '1', array $params = []): int;
    public static function paginate(int $page, int $perPage, string $where = '1', array $params = []): array;
    // Retorna ['data'=>[...], 'total'=>N, 'pages'=>N, 'page'=>N]

    protected static function query(string $sql, array $params = []): \PDOStatement;
    protected static function row(string $sql, array $params = []): ?array;
    protected static function rows(string $sql, array $params = []): array;
    protected static function timestamps(array $data, bool $update = false): array;
}
```

### `app/Core/Controller.php` — BaseController

```php
namespace App\Core;

abstract class Controller {
    protected View    $view;
    protected Request $request;

    public function __construct() {
        $this->view    = new View();
        $this->request = new Request();
    }

    protected function render(string $view, array $data = [], string $layout = 'main'): void;
    protected function redirect(string $url, int $code = 302): void;
    protected function json(mixed $data, int $status = 200): void;
    protected function requireAuth(): void;
    protected function requireRole(string $role): void;
    protected function flash(string $type, string $msg): void;
    protected function user(): ?array;
    protected function csrfToken(): string;      // Session::csrf()
    protected function verifyCsrf(): void;       // lança 403 se inválido
}
```

### `config/routes.php` — Rotas Completas

```php
return function(Router $r) {

    // ── Públicas ────────────────────────────────────────────────────────
    $r->get('/',            'AuthController@showLogin');
    $r->get('/login',       'AuthController@showLogin');
    $r->post('/login',      'AuthController@login');
    $r->get('/register',    'AuthController@showRegister');
    $r->post('/register',   'AuthController@register');
    $r->get('/forgot',      'AuthController@showForgot');
    $r->post('/forgot',     'AuthController@sendReset');
    $r->get('/reset/{token}','AuthController@showReset');
    $r->post('/reset',      'AuthController@resetPassword');

    // Short URL / Encapsulador
    $r->get('/r/{slug}',    'RedirectController@handle');

    // Hub Digital público
    $r->get('/hub/{slug}',  'HubController@public');

    // Mini Ferramentas (público, sem login)
    $r->get('/tools',       'ToolController@index');
    $r->post('/tools/currency', 'ToolController@currency');

    // ── Autenticadas ─────────────────────────────────────────────────────
    $r->group(['middleware' => 'auth'], function(Router $r) {
        $r->get('/logout',    'AuthController@logout');
        $r->get('/dashboard', 'DashboardController@index');

        // QR Code
        $r->get('/generate',            'QRController@showGenerate');
        $r->post('/generate',           'QRController@generate');
        $r->get('/qr/{uuid}',           'QRController@show');
        $r->delete('/qr/{uuid}',        'QRController@delete');
        $r->post('/qr/preview',         'QRController@preview');
        $r->post('/qr/{uuid}/logo',     'QRController@addLogo');      // QR-Logo
        $r->get('/qr/{uuid}/logo/status','QRController@logoStatus');  // polling async

        // Histórico + Analytics
        $r->get('/history',             'HistoryController@index');
        $r->get('/analytics',           'AnalyticsController@index');
        $r->get('/analytics/{uuid}',    'AnalyticsController@show');

        // Batch
        $r->get('/batch',               'BatchController@index');
        $r->post('/batch',              'BatchController@process');
        $r->get('/batch/{id}/download', 'BatchController@download');

        // Scanner
        $r->get('/scanner',             'ScannerController@index');
        $r->post('/scanner',            'ScannerController@scan');

        // Download
        $r->get('/download/{uuid}',     'DownloadController@download');

        // Encurtador + Encapsulador
        $r->get('/links',               'LinkController@index');
        $r->get('/links/create',        'LinkController@create');
        $r->post('/links',              'LinkController@store');
        $r->get('/links/{uuid}',        'LinkController@show');
        $r->put('/links/{uuid}',        'LinkController@update');
        $r->delete('/links/{uuid}',     'LinkController@delete');

        // Hub Digital
        $r->get('/hub',                 'HubController@index');
        $r->get('/hub/create',          'HubController@create');
        $r->post('/hub',                'HubController@store');
        $r->get('/hub/{uuid}/edit',     'HubController@edit');
        $r->put('/hub/{uuid}',          'HubController@update');
        $r->delete('/hub/{uuid}',       'HubController@delete');
        $r->post('/hub/{uuid}/blocks',  'HubController@addBlock');
        $r->put('/hub/block/{id}',      'HubController@updateBlock');
        $r->delete('/hub/block/{id}',   'HubController@deleteBlock');

        // Importador de Favoritos
        $r->get('/bookmarks',           'BookmarkController@index');
        $r->post('/bookmarks/import',   'BookmarkController@import');
        $r->post('/bookmarks/export',   'BookmarkController@export');
        $r->post('/bookmarks/health-check', 'BookmarkController@healthCheck');
        $r->delete('/bookmarks/{id}',   'BookmarkController@delete');
        $r->post('/bookmarks/{id}/launcher', 'BookmarkController@addToLauncher');

        // Launcher API
        $r->get('/launcher/index',      'LauncherController@getIndex');  // JSON
        $r->post('/launcher/track',     'LauncherController@track');
        $r->get('/launcher/embed',      'LauncherController@embedScript'); // widget JS

        // Perfil
        $r->get('/profile',             'ProfileController@index');
        $r->post('/profile',            'ProfileController@update');
        $r->post('/profile/api-key',    'ProfileController@regenerateApiKey');

        // API REST v1
        $r->group(['prefix' => '/api/v1', 'middleware' => 'api_key'], function(Router $r) {
            $r->post('/qr',             'ApiController@generateQR');
            $r->get('/qr',              'ApiController@listQR');
            $r->get('/qr/{uuid}',       'ApiController@getQR');
            $r->delete('/qr/{uuid}',    'ApiController@deleteQR');
            $r->post('/links',          'ApiController@createLink');
            $r->get('/links',           'ApiController@listLinks');
            $r->get('/links/{uuid}',    'ApiController@getLink');
            $r->get('/stats',           'ApiController@stats');
        });

        // Admin
        $r->group(['prefix' => '/admin', 'middleware' => 'role:admin'], function(Router $r) {
            $r->get('/users',           'AdminController@users');
            $r->post('/users/{id}/toggle', 'AdminController@toggleUser');
            $r->get('/settings',        'AdminController@settings');
            $r->post('/settings',       'AdminController@saveSettings');
        });
    });
};
```

---

## 7. Autenticação Completa

### Fluxos

#### Login (`GET/POST /login`)
```
GET  /login  → exibe formulário (redireciona para /dashboard se já logado)
POST /login  → valida campos → verifica email + password_verify()
              → checa active = 1
              → Rate Limiting: chave Redis "login_attempts:{ip}" → max 5 em 15min
              → Auth::login() → session_regenerate_id(true)
              → redireciona /dashboard ou URL intended
```

#### Registro (`GET/POST /register`)
```
POST /register → Validator::make() validações:
                 name: required|min:2|max:120
                 email: required|email|unique:users
                 password: required|min:8|confirmed
               → password_hash(PASSWORD_ARGON2ID)
               → uuid4(), api_key = bin2hex(random_bytes(32))
               → User::create() → credits = 0
               → MailerService::sendWelcome()
               → Auth::login() → /dashboard
```

#### Recuperação de Senha
```
POST /forgot → token = bin2hex(random_bytes(32)) → expires_at = +1h
             → MailerService::sendPasswordReset()
             → flash "Se o e-mail existir, você receberá o link."

GET  /reset/{token} → verifica token + não expirado + não usado → form
POST /reset         → password_hash() + update + marca token usado → login
```

### `app/Core/Auth.php`

```php
public static function check(): bool;
public static function user(): ?array;
public static function id(): ?int;
public static function attempt(string $email, string $password): bool;
public static function login(int $userId): void;
public static function logout(): void;
public static function hasRole(string $role): bool;
public static function regenerate(): void;
```

### Rate Limiting com Redis

```php
// app/Core/Middleware.php
public static function rateLimit(string $key, int $max, int $windowSec): void {
    $count = Cache::incr("rl:{$key}");
    if ($count === 1) Cache::expire("rl:{$key}", $windowSec);
    if ($count > $max) {
        Response::json(['error' => 'Too Many Requests'], 429);
        exit;
    }
}
// Uso no AuthController:
Middleware::rateLimit("login:{$_SERVER['REMOTE_ADDR']}", 5, 900);
```

---

## 8. Módulo QR Code + QR-Logo

### 12 Tipos de QR Code

| Tipo | Builder | Conteúdo gerado |
|---|---|---|
| `url` | `buildURL` | URL direta |
| `text` | `buildText` | Texto puro |
| `email` | `buildEmail` | `mailto:to?subject=S&body=B` |
| `phone` | `buildPhone` | `tel:+5569XXXXX` |
| `sms` | `buildSMS` | `sms:+5569?body=MSG` |
| `whatsapp` | `buildWhatsApp` | `https://wa.me/55...?text=MSG` |
| `wifi` | `buildWiFi` | `WIFI:T:WPA;S:SSID;P:PASS;H:false;;` |
| `vcard` | `buildVCard` | vCard 3.0 multiline |
| `geo` | `buildGeo` | `geo:lat,lng?q=Label` |
| `event` | `buildEvent` | VCALENDAR/VEVENT |
| `bitcoin` | `buildBitcoin` | `bitcoin:addr?amount=X` |
| `pix` | `buildPIX` | EMV QR Code PIX (Bacen) |

### `app/Services/QRLogoService.php` — QR-Logo

```php
namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class QRLogoService {
    const MIN_LEGIBILITY_INDEX = 70;   // abaixo → rejeitar

    /**
     * Renderiza QR Code com silhueta da logo do cliente usando ImageMagick.
     * Retorna caminho do arquivo ou lança exceção se índice < MIN_LEGIBILITY_INDEX.
     *
     * Algoritmo:
     * 1. Gera QR base ECC H (30% tolerância) 1024×1024 PNG via chillerlan
     * 2. Carrega logo do cliente via Intervention/Image + Imagick
     * 3. Extrai silhueta: threshold (255→branco, resto→transparente)
     * 4. Aplica tint da cor do QR à silhueta
     * 5. Redimensiona silhueta para max 25% da área do QR
     * 6. Compõe logo sobre QR com posição central
     * 7. Calcula Índice PRISMA de Legibilidade
     * 8. Se índice >= 70 → salva + retorna path
     * 9. Se índice < 70 → lança QRLogoLegibilityException
     */
    public function render(int $qrcodeId, string $logoPath, array $options = []): array {
        // $options: ['size'=>1024, 'fg_color'=>'#000000', 'bg_color'=>'#FFFFFF']
        // Retorna ['path' => 'storage/qr-logos/uuid.png', 'index' => 85]
    }

    /**
     * Índice PRISMA de Legibilidade (0–100)
     * Mede a proporção de módulos do QR que permanece legível após sobreposição.
     * Score = (módulos intactos / total de módulos) × 100
     * Módulos de sincronização (finder patterns, timing) têm peso 2×
     */
    public function calculateIndex(string $qrPath, string $logoPath, array $options): int {
        // Implementar via Imagick pixel comparison
    }
}
```

### Fluxo Assíncrono do QR-Logo

```
1. Usuário faz POST /qr/{uuid}/logo (upload da logo)
2. QRController salva logo em storage/uploads/{uuid}/logo.png
3. Debita 1 crédito do usuário (CreditTransaction)
4. QueueService::push('qrlogo', ['qrcode_id' => $id, 'logo_path' => ...])
5. Atualiza qrcodes.render_status = 'pending'
6. Retorna 202 Accepted + job_id
7. Frontend faz polling: GET /qr/{uuid}/logo/status → {"status":"pending"}
8. Worker consome job → QRLogoService::render()
   - Sucesso: salva file_logo_png, atualiza legibility_index, render_status='done'
   - Falha (índice < 70): render_status='failed', reembolsa 1 crédito
9. Frontend polling → {"status":"done","logo_url":"/...","index":82}
```

---

## 9. Módulo Encurtador + Encapsulador

### Tipos de Encapsulador

| Tipo | Descrição | Dados extras |
|---|---|---|
| `none` | Redirect direto (302) | — |
| `intersticial` | Página branded de 5s antes do destino | `intersticial_template` |
| `utm` | Injeta parâmetros UTM na URL de destino | `utm_json` |
| `conditional` | Redireciona baseado em device/país/horário | `conditions_json` |
| `ab` | Divide tráfego entre variantes (hash do IP) | `ab_variants` |
| `cloaking` | Abre destino em iframe | — |

### Condições Suportadas (conditional)

```json
[
  {"field": "device",  "operator": "eq", "value": "mobile",  "destination": "https://app.exemplo.com"},
  {"field": "country", "operator": "in", "value": ["BR","PT"], "destination": "https://pt.exemplo.com"},
  {"field": "hour",    "operator": "between", "value": [8, 18], "destination": "https://business.exemplo.com"}
]
```

Campos: `device` (mobile/desktop/tablet), `country` (ISO-2), `hour` (0–23), `clicks` (contador).

### `RedirectController::handle(string $slug)`

```
1. Cache::get("link:{$slug}") → hit: usa dados, skip DB
   Cache miss → Link::findBy('slug', $slug) → Cache::set("link:{$slug}", $link, 300)

2. if $link['expires_at'] && $link['expires_at'] < now() → 410 Gone

3. QueueService::push('click', ['link_id'=>$id, 'ip'=>$ip, 'ua'=>$ua, 'referer'=>$ref])
   ← NUNCA bloqueia o redirect

4. switch($link['wrapper_type']):
   'none'         → header('Location: ' . $destination, true, 302); exit;
   'utm'          → injeta UTMs na URL → redirect
   'intersticial' → render views/public/intersticial.php (JS countdown 5s)
   'conditional'  → avalia conditions_json → redirect para URL correspondente
   'ab'           → variante = hash(ip) % 100 → seleciona variant por peso → redirect
   'cloaking'     → render views/public/cloaking.php (iframe fullscreen)
```

---

## 10. Hub Digital

### URL Pública: `prisma.app/hub/{slug}`

### 9 Tipos de Bloco

| Tipo | Descrição | Campos em `blocks_json` |
|---|---|---|
| `link` | Link simples com título e URL | `{url, label, icon}` |
| `group` | Agrupador com título e N links | `{title, links:[{url,label}]}` |
| `whatsapp` | Botão WhatsApp com mensagem | `{phone, message, label}` |
| `social` | Grid de ícones de redes sociais | `{networks:[{name,url}]}` |
| `map` | Mapa Google Maps embed | `{address, lat, lng, zoom}` |
| `schedule` | Grade de horários de atendimento | `{hours:[{day,open,close}]}` |
| `catalog` | Cards de produtos/serviços | `{items:[{title,price,img,url}]}` |
| `video` | Embed YouTube/Vimeo | `{video_url, autoplay}` |
| `contact_form` | Formulário de contato | `{fields:[{name,type,required}], email_to}` |

### Regras
- Máximo 50 blocos por Hub Page
- Posição é ordenável via drag-and-drop (AJAX PUT /hub/block/{id})
- Hub público não requer login do visitante
- `view_count` incrementa a cada visita única (por IP hash, TTL 1h Redis)

---

## 11. Importador de Favoritos

### Formato Aceito: NETSCAPE Bookmark File Format
Exportado por Chrome, Firefox, Safari, Edge. Extensão `.html`.

### `BookmarkImporterService::import(string $htmlContent, int $userId): array`

```php
/**
 * Parser NETSCAPE Bookmark File
 * 1. strip HTML tags mantendo <DT>, <DL>, <A>, <H3>
 * 2. Constrói árvore recursiva: DL = folder, DT > A = bookmark, DT > H3 = folder
 * 3. Deduplica: compara hash(url) contra bookmarks existentes do user
 * 4. Salva BookmarkFolders e Bookmarks preservando hierarquia
 * 5. Retorna: ['imported'=>N, 'duplicates'=>N, 'folders'=>N, 'errors'=>[]]
 * Limite: 5.000 bookmarks por importação
 */
```

### Health Check Assíncrono

```php
// QueueService::push('bookmark_health', ['bookmark_id' => $id])
// Worker: HEAD request com timeout 5s
// Atualiza bookmarks.health = 'ok'|'broken'
// Se HTTP 200-399 → 'ok', else → 'broken', timeout/erro → 'broken'
```

### Exportar para `.html`

```php
// BookmarkController@export → gera NETSCAPE HTML válido
// Formato: <!DOCTYPE NETSCAPE-Bookmark-file-1>
// Hierarquia preservada como <DL><p> aninhados
// Content-Disposition: attachment; filename="prisma-favoritos.html"
```

---

## 12. Launcher — Widget Shadow DOM

### Embed na Página do Cliente

```html
<!-- Adicionar antes do </body> -->
<script
  src="https://prisma.app/launcher/embed"
  data-user-id="USER_UUID"
  data-api-key="LAUNCHER_TOKEN"
  async
></script>
```

### Arquitetura Shadow DOM

```javascript
// public/assets/js/launcher-widget.js
// Compilado como IIFE — sem dependências externas

(function() {
    const host = document.createElement('div');
    host.id = 'prisma-launcher-host';
    document.body.appendChild(host);

    const shadow = host.attachShadow({ mode: 'closed' });
    // → Shadow DOM: CSS completamente isolado do host
    // → Sem vazamento de estilos em nenhuma direção

    // Estrutura interna:
    // <button id="trigger">  ← flutuante, posição fixa
    // <div id="panel">       ← painel de busca
    //   <input id="search">
    //   <ul id="results">
    // </div>
})();
```

### Algoritmo de Busca — Bitap (sem dependências)

```javascript
/**
 * Bitap algorithm — fuzzy search O(mn)
 * m = tamanho do padrão, n = tamanho do texto
 * Máximo 1 erro (deleção, inserção, substituição)
 * Aplicado sobre: title + url + tags do LauncherIndex
 */
function bitapSearch(text, pattern, maxErrors = 1) {
    // implementar Bitap completo
    // retorna: {score: float, matched: bool}
}
```

### JSON Index (carregado uma vez por sessão)

```json
{
  "user_id": 42,
  "generated_at": "2026-09-01T10:00:00Z",
  "links": [
    {
      "id": 1,
      "title": "Dashboard PRISMA",
      "url": "https://prisma.app/dashboard",
      "icon": "bi-speedometer2",
      "tags": ["admin", "prisma"],
      "use_count": 15,
      "last_used_at": "2026-09-01T09:30:00Z"
    }
  ]
}
```

### Cache Redis: `launcher:{user_id}` TTL 300s

### `LauncherController::getIndex()`
```
1. Cache::get("launcher:{user_id}") → hit → JSON response
2. Miss → LauncherIndexService::build($userId)
         → query launcher_links + bookmarks(in_launcher=1) + links recentes
         → ordena por use_count DESC, last_used_at DESC
         → Cache::set("launcher:{user_id}", $index, 300)
3. Retorna JSON com header Cache-Control: private, max-age=300
```

### Sugestões Contextuais

```javascript
// Rank = base_score + contextual_boost
// contextual_boost:
//   +2 se hora atual está no padrão de uso do link (analisado no server)
//   +1 se link foi usado nas últimas 2h
//   +3 se corresponde ao role do usuário (roles_json)
```

---

## 13. Mini Ferramentas (público, sem login)

### URL: `/tools` — todas serverless (JS puro no browser, sem chamadas AJAX exceto currency)

| Ferramenta | Implementação |
|---|---|
| Conversor de unidades | JS — comprimento, peso, temperatura, volume |
| Conversor de moedas | PHP API (cache BRL/USD/EUR Redis 1h via `CurrencyService`) |
| Gerador de senha | `crypto.getRandomValues()` JS |
| Validador CPF/CNPJ | JS — algoritmo Receita Federal |
| Gerador UUID | `crypto.randomUUID()` JS |
| Base64 encode/decode | `btoa()`/`atob()` JS |
| Formatador JSON | `JSON.stringify(JSON.parse())` JS |
| Conversor timestamp | `new Date(ts * 1000)` JS |
| Contador de palavras | `str.split(/\s+/)` JS |
| Lorem Ipsum | array local JS |
| Extrator de cores | Canvas `getImageData()` — upload de imagem JS |

**Regra:** Nenhuma ferramenta requer login. Nenhuma persiste dados do usuário.

---

## 14. Monetização

### Modelo (sem planos tradicionais)

| Fonte de Receita | Detalhe |
|---|---|
| **QR-Logo por render** | 1 crédito por renderização aprovada (índice ≥ 70) |
| **Pacotes de créditos** | Compra avulsa: 10, 50, 100, 500 créditos |
| **Taxa mensal por organização** | Valor fixo por org ativa |
| **White-label anual** | Licença para agências/revendedores |
| **API por volume** | Tiers: 1k, 10k, 100k req/mês |

### Fluxo de Créditos

```
Compra → CreditTransaction(type='purchase', amount=+N)
       → users.credits += N

Consumo (QR-Logo) → verifica users.credits >= 1
                  → debita ANTES do processamento
                  → CreditTransaction(type='consume', amount=-1, reference=qrcode_uuid)
                  → se render falha (índice < 70) → reembolso automático
                    → CreditTransaction(type='refund', amount=+1)
```

---

## 15. Fila Redis + Workers

### `app/Services/QueueService.php`

```php
namespace App\Services;

use App\Core\Cache;

class QueueService {
    const QUEUES = ['qrlogo', 'click', 'bookmark_health', 'email', 'default'];

    public static function push(string $queue, array $payload): string {
        $job = [
            'id'         => uuid4(),
            'queue'      => $queue,
            'payload'    => $payload,
            'created_at' => date('c'),
            'attempts'   => 0,
        ];
        Cache::client()->lpush("queue:{$queue}", json_encode($job));
        // Audit MySQL (não bloqueante)
        \App\Models\Job::create(['queue'=>$queue, 'payload'=>json_encode($payload)]);
        return $job['id'];
    }

    public static function pop(string $queue, int $timeout = 5): ?array {
        $raw = Cache::client()->brpop(["queue:{$queue}"], $timeout);
        return $raw ? json_decode($raw[1], true) : null;
    }
}
```

### `workers/queue-worker.php`

```php
<?php
define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';
(new Dotenv\Dotenv(ROOT))->safeLoad();

use App\Services\QueueService;
use App\Services\QRLogoService;
use App\Services\BookmarkImporterService;

$queue = $argv[1] ?? 'default';

while (true) {
    $job = QueueService::pop($queue);
    if (!$job) continue;

    try {
        match($job['payload']['type'] ?? $queue) {
            'qrlogo'          => (new QRLogoService())->processJob($job['payload']),
            'click'           => \App\Services\AnalyticsService::recordClick($job['payload']),
            'bookmark_health' => BookmarkImporterService::checkHealth($job['payload']),
            'email'           => \App\Services\MailerService::processJob($job['payload']),
            default           => throw new \RuntimeException("Unknown job type"),
        };
        \App\Models\Job::markDone($job['id'] ?? null);
    } catch (\Throwable $e) {
        error_log("[Worker] Error: " . $e->getMessage());
        \App\Models\Job::markFailed($job['id'] ?? null, $e->getMessage());
    }
}
```

### `supervisor/prisma-worker.conf`

```ini
[program:prisma-qrlogo-worker]
command=php /var/www/prisma/workers/queue-worker.php qrlogo
directory=/var/www/prisma
user=www-data
numprocs=2
autostart=true
autorestart=true
startretries=3
stdout_logfile=/var/log/supervisor/prisma-qrlogo.log
stderr_logfile=/var/log/supervisor/prisma-qrlogo-err.log
stopasgroup=true
killasgroup=true

[program:prisma-default-worker]
command=php /var/www/prisma/workers/queue-worker.php default
directory=/var/www/prisma
user=www-data
numprocs=1
autostart=true
autorestart=true
stdout_logfile=/var/log/supervisor/prisma-worker.log
stderr_logfile=/var/log/supervisor/prisma-worker-err.log
```

---

## 16. StorageService

```php
namespace App\Services;

class StorageService {
    private string $driver;   // 'local' | 's3'

    public function __construct() {
        $this->driver = env('STORAGE_DRIVER', 'local');
    }

    // Salva arquivo. Retorna path relativo.
    public function put(string $destination, string $sourcePath): string;

    // Retorna conteúdo do arquivo.
    public function get(string $path): string;

    // Remove arquivo.
    public function delete(string $path): bool;

    // URL pública de acesso.
    public function url(string $path): string;

    // Implementações:
    // local: ROOT . '/storage/' . $destination
    // s3:    AWS SDK S3 (Fase 2) — mesma interface
}
```

---

## 17. Segurança — Checklist Obrigatório

- [ ] Todas as saídas passam por `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` — função `e()`
- [ ] Senhas com `password_hash(PASSWORD_ARGON2ID)` + `password_verify()`
- [ ] SQL exclusivamente via PDO prepared statements — zero concatenação de string em queries
- [ ] Uploads: validar MIME com `finfo_file()` — NUNCA confiar em extensão
- [ ] Uploads em `storage/uploads/` (fora do webroot) + `.htaccess` negando execução
- [ ] CSRF token em todos os formulários POST — `Session::csrf()` + `Middleware::csrf()`
- [ ] Session regenerada após login com `session_regenerate_id(true)`
- [ ] Rate limiting Redis: max 5 tentativas/IP em 15min — chave `rl:login:{ip}`
- [ ] API key: `bin2hex(random_bytes(32))` — nunca sequencial, nunca em logs
- [ ] Short codes: `base64_url_encode(random_bytes(6))` — nunca sequencial
- [ ] UUIDs: RFC4122 v4 — `uuid4()` helper
- [ ] IPs anonimizados: `hash('sha256', $ip . env('APP_KEY'))` → coluna `ip_hash`
- [ ] Shadow DOM Launcher: CSS isolado — sem acesso ao DOM do host
- [ ] Headers de segurança em `public/index.php`:
  ```php
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');   // DENY bloquearia iframe cloaking próprio
  header('X-XSS-Protection: 1; mode=block');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('Permissions-Policy: geolocation=(), microphone=()');
  ```
- [ ] `APP_DEBUG=false` em produção → página de erro genérica, sem stack trace
- [ ] Nginx bloqueia acesso direto a `storage/` exceto `storage/qr-logos/` e `storage/qrcodes/`

---

## 18. Redis — Estratégia de Cache

| Chave | Conteúdo | TTL |
|---|---|---|
| `link:{slug}` | dados do link/encurtador | 5 min |
| `launcher:{user_id}` | JSON index do launcher | 5 min |
| `geo:{ip_hash}` | dados de geolocalização | 1 hora |
| `currency:rates` | cotações BRL/USD/EUR | 1 hora |
| `rl:login:{ip}` | contador tentativas login | 15 min |
| `hub:view:{slug}:{ip_hash}` | flag visita única hub | 1 hora |
| `queue:{name}` | lista Redis (LPUSH/BRPOP) | sem TTL |

---

## 19. `.env.example`

```dotenv
# Aplicação
APP_NAME="PRISMA"
APP_ENV=local                     # local | production
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=                          # php -r "echo bin2hex(random_bytes(32));"

# Banco MySQL
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prisma
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0

# E-mail (SMTP)
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@prisma.app
MAIL_FROM_NAME="PRISMA"

# Storage
STORAGE_DRIVER=local              # local | s3 (Fase 2)
STORAGE_PATH=/storage
UPLOAD_MAX_SIZE=10485760          # 10MB

# GeoIP
GEOIP_ENABLED=true
GEOIP_PROVIDER=ip-api.com

# Rate Limiting
RATE_LIMIT_LOGIN=5
RATE_LIMIT_WINDOW=900

# QR-Logo
QRLOGO_MIN_INDEX=70               # Índice PRISMA mínimo aceitável
QRLOGO_CREDIT_COST=1

# Launcher
LAUNCHER_CACHE_TTL=300

# AWS S3 (Fase 2)
# AWS_KEY=
# AWS_SECRET=
# AWS_BUCKET=
# AWS_REGION=us-east-1
```

---

## 20. `composer.json`

```json
{
    "name": "pageup/prisma",
    "description": "PRISMA — Plataforma SaaS de Links e QR Codes · PHP 8.2 MVC",
    "type": "project",
    "require": {
        "php": ">=8.2",
        "chillerlan/php-qrcode": "^5.0",
        "khanamiryan/qrcode-detector-decoder": "^2.0",
        "phpmailer/phpmailer": "^6.8",
        "vlucas/phpdotenv": "^5.6",
        "predis/predis": "^2.2",
        "intervention/image": "^3.3"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        },
        "files": [
            "app/Core/helpers.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "migrate": "php database/migrate.php",
        "seed":    "php database/seeds/AdminSeeder.php",
        "serve":   "php -S localhost:8000 -t public/",
        "worker":  "php workers/queue-worker.php default",
        "test":    "vendor/bin/phpunit"
    }
}
```

---

## 21. `public/.htaccess` (Apache)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>

<FilesMatch "\.(env|md|json|lock|log|sql|conf)$">
    Require all denied
</FilesMatch>

# Bloqueia acesso direto a storage exceto assets gerados
<DirectoryMatch "storage/(uploads|batches|bookmarks|logs)">
    Require all denied
</DirectoryMatch>

Options -Indexes
```

---

## 22. Nginx (produção)

```nginx
server {
    listen 80;
    server_name prisma.app www.prisma.app;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name prisma.app www.prisma.app;

    root /var/www/prisma/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/prisma.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/prisma.app/privkey.pem;

    # Bloqueia acesso a storage privado
    location ~* ^/storage/(uploads|batches|bookmarks|logs)/ {
        deny all;
        return 403;
    }

    # Assets estáticos com cache longo
    location ~* \.(css|js|png|svg|ico|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # PHP-FPM
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass   unix:/run/php/php8.2-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_read_timeout 60;
    }

    # Segurança
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
}
```

---

## 23. `public/index.php` — Front Controller

```php
<?php
declare(strict_types=1);
define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

(new Dotenv\Dotenv(ROOT))->safeLoad();

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Sessão segura
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => env('APP_ENV') === 'production',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Roteador
$router = new App\Core\Router();
$routes = require ROOT . '/config/routes.php';
$routes($router);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
```

---

## 24. Helpers Globais (`app/Core/helpers.php`)

```php
function env(string $key, mixed $default = null): mixed;
function e(string $val): string;               // htmlspecialchars(ENT_QUOTES, UTF-8)
function url(string $path = ''): string;
function asset(string $path): string;          // /assets/css/app.css?v=abc123
function redirect(string $url, int $code = 302): never;
function flash(string $type, string $msg): void;
function renderFlash(): string;
function uuid4(): string;                      // RFC4122 v4
function shortCode(int $len = 8): string;      // base64url(random_bytes)
function validMime(string $path, array $allowed): bool; // finfo_file()
function formatBytes(int $bytes): string;
function detectDevice(string $ua): string;     // desktop|mobile|tablet|bot|unknown
function truncate(string $str, int $len = 80): string;
function nplural(int $n, string $s, string $p): string;
function ipHash(string $ip): string;           // SHA-256(ip + APP_KEY) — LGPD
function isJson(string $str): bool;
function jsonDecode(string $json): array;
```

---

## 25. Layout Auth (`app/Views/layouts/auth.php`)

```html
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> — PRISMA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body style="background:var(--color-base);min-height:100vh;display:flex;align-items:center;">
    <div class="container" style="max-width:420px;margin:auto;">
        <div class="text-center mb-4">
            <img src="<?= asset('img/logo.svg') ?>" height="48" alt="PRISMA">
            <h4 class="mt-2" style="font-family:var(--font-display);color:var(--color-text-primary);">PRISMA</h4>
            <div class="spectrum-bar mt-1 rounded"></div>
        </div>
        <div class="card p-4">
            <?= renderFlash() ?>
            <?= $content ?>
        </div>
        <p class="text-center mt-3" style="color:var(--color-text-muted);font-size:.85rem;">
            © <?= date('Y') ?> PageUp Sistemas · Porto Velho, RO
        </p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 26. Layout Principal (`app/Views/layouts/main.php`)

```
┌─────────────────────────────────────────────────────────────┐
│  NAVBAR (topo fixo)  Spectrum bar ████████████████████████  │
│  Logo PRISMA │ Busca global │ Novo QR ▾ │ Créditos │ Avatar │
├──────────────┬──────────────────────────────────────────────┤
│              │                                              │
│  SIDEBAR     │  CONTEÚDO PRINCIPAL                          │
│              │                                              │
│  Dashboard   │  [Flash messages]                            │
│  ── QR ──    │  [Título + breadcrumb]                       │
│  Gerar QR    │  [...view content...]                        │
│  Histórico   │                                              │
│  Analytics   │                                              │
│  Lote        │                                              │
│  ── Links ── │                                              │
│  Encurtador  │                                              │
│  Hub Digital │                                              │
│  ── Mais ──  │                                              │
│  Favoritos   │                                              │
│  Ferramentas │                                              │
│  Scanner     │                                              │
│  ── Admin ── │                                              │
│  Usuários    │                                              │
│  Config      │                                              │
│              │                                              │
└──────────────┴──────────────────────────────────────────────┘
```

- Sidebar colapsa em mobile (Bootstrap offcanvas)
- Avatar → dropdown: Perfil, API Key, Créditos, Sair
- Barra de créditos visível no navbar (badge numérico)

---

## 27. Ordem de Implementação (MVP → Completo)

```
═══════════════════════════════════════
Fase 1 — Infraestrutura Base
═══════════════════════════════════════
 [1]  .env.example + config/app.php + config/database.php + config/redis.php
 [2]  app/Core/helpers.php
 [3]  app/Core/Database.php         (PDO singleton)
 [4]  app/Core/Cache.php            (Redis/Predis)
 [5]  app/Core/Model.php            (BaseModel)
 [6]  app/Core/Session.php
 [7]  app/Core/Auth.php
 [8]  app/Core/Validator.php
 [9]  app/Core/Request.php + Response.php
[10]  app/Core/View.php
[11]  app/Core/Router.php
[12]  app/Core/Middleware.php       (auth, role, csrf, rate_limit)
[13]  public/index.php              (Front Controller)
[14]  public/.htaccess
[15]  database/migrations/*.sql     (001 a 014)
[16]  database/migrate.php          (CLI runner)
[17]  database/seeds/AdminSeeder.php

═══════════════════════════════════════
Fase 2 — Auth
═══════════════════════════════════════
[18]  app/Models/User.php
[19]  app/Services/MailerService.php
[20]  app/Controllers/AuthController.php
[21]  app/Views/layouts/auth.php
[22]  app/Views/auth/login.php
[23]  app/Views/auth/register.php
[24]  app/Views/auth/forgot.php + reset.php
[25]  config/routes.php             (rotas de auth)

═══════════════════════════════════════
Fase 3 — QR Code Core
═══════════════════════════════════════
[26]  app/Services/StorageService.php
[27]  app/Services/QRGenerator.php  (12 tipos)
[28]  app/Models/QRCode.php + Scan.php
[29]  app/Controllers/QRController.php
[30]  app/Views/layouts/main.php    (sidebar + navbar)
[31]  app/Views/qr/generate.php     (preview em tempo real)
[32]  app/Views/qr/show.php
[33]  app/Controllers/DownloadController.php
[34]  public/assets/css/app.css     (Deep Sea + Spectrum)
[35]  public/assets/js/app.js       (preview AJAX + debounce)

═══════════════════════════════════════
Fase 4 — Analytics + Short URL
═══════════════════════════════════════
[36]  app/Services/AnalyticsService.php + GeoService.php
[37]  app/Services/ShortURLService.php
[38]  app/Controllers/RedirectController.php (Short URL básico)
[39]  app/Controllers/HistoryController.php + View
[40]  app/Controllers/AnalyticsController.php + Views

═══════════════════════════════════════
Fase 5 — Encurtador + Encapsulador
═══════════════════════════════════════
[41]  app/Models/Link.php + LinkClick.php
[42]  app/Controllers/LinkController.php + Views
[43]  RedirectController → extend para Encapsulador (5 tipos)
[44]  app/Views/public/intersticial.php
[45]  app/Views/public/cloaking.php

═══════════════════════════════════════
Fase 6 — QR-Logo + Fila
═══════════════════════════════════════
[46]  app/Services/QueueService.php
[47]  workers/queue-worker.php
[48]  supervisor/prisma-worker.conf
[49]  app/Services/QRLogoService.php (ImageMagick + Índice)
[50]  QRController → endpoints logo (POST + status polling)
[51]  app/Models/Job.php + CreditTransaction.php

═══════════════════════════════════════
Fase 7 — Hub Digital
═══════════════════════════════════════
[52]  app/Models/HubPage.php + HubBlock.php
[53]  app/Controllers/HubController.php
[54]  app/Views/hub/index.php + editor.php
[55]  app/Views/hub/public.php       (página pública /hub/{slug})
[56]  app/Views/layouts/public.php

═══════════════════════════════════════
Fase 8 — Importador de Favoritos
═══════════════════════════════════════
[57]  app/Models/Bookmark.php + BookmarkFolder.php
[58]  app/Services/BookmarkImporterService.php (NETSCAPE parser)
[59]  app/Controllers/BookmarkController.php + Views
[60]  Worker job bookmark_health

═══════════════════════════════════════
Fase 9 — Launcher
═══════════════════════════════════════
[61]  app/Models/LauncherLink.php
[62]  app/Services/LauncherIndexService.php
[63]  app/Controllers/LauncherController.php
[64]  public/assets/js/launcher-widget.js (Shadow DOM + Bitap)

═══════════════════════════════════════
Fase 10 — Mini Ferramentas
═══════════════════════════════════════
[65]  app/Controllers/ToolController.php
[66]  app/Services/CurrencyService.php (cache Redis 1h)
[67]  app/Views/tools/index.php

═══════════════════════════════════════
Fase 11 — Batch + Scanner
═══════════════════════════════════════
[68]  app/Models/Batch.php + BatchItem.php
[69]  app/Services/BatchProcessor.php
[70]  app/Controllers/BatchController.php + View
[71]  app/Services/QRScannerService.php
[72]  app/Controllers/ScannerController.php + View

═══════════════════════════════════════
Fase 12 — Admin + API + Perfil
═══════════════════════════════════════
[73]  app/Controllers/DashboardController.php + View (KPIs)
[74]  app/Controllers/ProfileController.php + View
[75]  app/Controllers/AdminController.php + Views
[76]  app/Controllers/ApiController.php (API v1)
[77]  config/routes.php              (rotas completas)
```

---

## 28. Comandos CLI

```bash
# Setup do zero
composer install
cp .env.example .env
# Editar .env com credenciais MySQL e Redis
composer migrate           # cria as 14 tabelas
composer seed              # admin@prisma.app / Admin@123
composer serve             # http://localhost:8000

# Verificar extensões
php -r "
\$ext = ['pdo_mysql','redis','imagick','gd','fileinfo','mbstring','openssl','zip','curl','intl'];
foreach (\$ext as \$e) echo \$e . ': ' . (extension_loaded(\$e) ? 'OK' : 'FALTA') . PHP_EOL;
"

# Gerar chaves
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"   # APP_KEY
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"   # API_KEY

# Iniciar worker manualmente (dev)
php workers/queue-worker.php qrlogo &
php workers/queue-worker.php default &

# Instalar/recarregar Supervisor (prod)
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all

# Testar Redis
php -r "
require 'vendor/autoload.php';
(new Dotenv\Dotenv(__DIR__))->safeLoad();
\$c = new Predis\Client(['host'=>getenv('REDIS_HOST'),'port'=>getenv('REDIS_PORT')]);
\$c->set('test','ok');
echo \$c->get('test') . PHP_EOL;   // ok
"

# Testar QR-Logo (1 render manual)
php -r "
define('ROOT', __DIR__);
require 'vendor/autoload.php';
(new Dotenv\Dotenv(ROOT))->safeLoad();
\$svc = new App\Services\QRLogoService();
\$result = \$svc->render(1, 'storage/uploads/test-logo.png');
echo 'Index: ' . \$result['index'] . PHP_EOL;
echo 'Path: '  . \$result['path']  . PHP_EOL;
"

# Rodar migrations
php database/migrate.php

# Seed admin
php database/seeds/AdminSeeder.php

# Limpeza de QR antigos (>90 dias)
php -r "
define('ROOT', __DIR__);
require 'vendor/autoload.php';
(new Dotenv\Dotenv(ROOT))->safeLoad();
App\Models\QRCode::cleanOld(90);
echo 'OK' . PHP_EOL;
"
```

---

## 29. API REST v1

Autenticação: header `X-API-Key: <user_api_key>`
Base URL: `/api/v1`
Formato: JSON · `Content-Type: application/json`

### Endpoints

| Método | Rota | Descrição |
|---|---|---|
| POST | `/qr` | Gera e salva QR Code |
| GET | `/qr` | Lista QR Codes |
| GET | `/qr/{uuid}` | Detalhes de um QR |
| DELETE | `/qr/{uuid}` | Remove QR Code |
| POST | `/links` | Cria link encurtado |
| GET | `/links` | Lista links |
| GET | `/links/{uuid}` | Detalhes de link |
| GET | `/stats` | Estatísticas do usuário |

### Resposta Padrão

```json
// Sucesso
{ "success": true, "data": { ... }, "meta": { "page": 1, "total": 42 } }

// Erro
{ "success": false, "error": { "code": 422, "message": "...", "details": { "field": "..." } } }
```

---

## 30. Funcionalidades Fase 2 (pós-MVP)

| Feature | Prioridade |
|---|---|
| S3 StorageService | Alta |
| Stripe / Pagar.me para compra de créditos | Alta |
| 2FA TOTP | Média |
| Webhook por scan/click | Média |
| QR com senha / expiração | Média |
| Multi-organização (convidar membros) | Alta |
| Exportar relatório PDF | Média |
| Geolocalização mapa (Leaflet.js) | Baixa |
| Multi-idioma PT-BR / EN / ES | Baixa |
| App mobile (PWA) | Alta |

---

## 31. Referências

| Recurso | URL |
|---|---|
| chillerlan/php-qrcode | https://github.com/chillerlan/php-qrcode |
| khanamiryan/qrcode-detector-decoder | https://github.com/khanamiryan/qrcode-detector-decoder |
| Intervention Image v3 | https://image.intervention.io/v3 |
| PHPMailer | https://github.com/PHPMailer/PHPMailer |
| Predis | https://github.com/predis/predis |
| phpdotenv | https://github.com/vlucas/phpdotenv |
| Bootstrap 5.3 | https://cdn.jsdelivr.net/npm/bootstrap@5.3.3 |
| Bootstrap Icons 1.11 | https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3 |
| Chart.js 4 | https://cdn.jsdelivr.net/npm/chart.js@4 |
| Syne Font | https://fonts.google.com/specimen/Syne |
| Inter Font | https://fonts.google.com/specimen/Inter |
| Spec PIX EMV Bacen | https://www.bcb.gov.br (Manual BR Code QR Code Pix v2.1) |
| vCard RFC 2426 | https://www.rfc-editor.org/rfc/rfc2426 |
| iCalendar RFC 5545 | https://www.rfc-editor.org/rfc/rfc5545 |
| Bitap algorithm | https://en.wikipedia.org/wiki/Bitap_algorithm |
| NETSCAPE Bookmark format | https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/platform-apis/aa753582(v=vs.85) |
| ip-api.com GeoIP | https://ip-api.com/docs/api:json |
| ImageMagick PHP | https://www.php.net/manual/en/book.imagick.php |
| Supervisor | http://supervisord.org/configuration.html |
| Deep Sea base | #0D1B2A · HSL(211, 53%, 11%) |
| PageUp Sistemas | Porto Velho, RO · pageupsistemas@gmail.com |
