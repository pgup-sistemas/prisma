# PRISMA

Plataforma SaaS de gestão de links e QR Codes — encurtamento, encapsulamento avançado,
Hub Digital, importador de favoritos, mini ferramentas e um agente de desktop com busca
ultrarrápida. PHP 8.2 MVC puro (sem framework), MySQL 8, Redis 7.

**PageUp Sistemas** · Porto Velho, RO · pageupsistemas@gmail.com

> Repositório privado. O agente de desktop (Launcher) tem repositório próprio, open
> source: veja [`desktop-agent/`](desktop-agent/) e o link do repositório publicado.

---

## Módulos

| Módulo | Descrição |
|---|---|
| **QR Code** | 12 tipos (URL, WiFi, vCard, PIX, WhatsApp, evento, etc.) + QR-Logo com Índice PRISMA de Legibilidade |
| **Encurtador / Encapsulador** | Links curtos com 6 modos: direto, UTM, intersticial, condicional (device/país/horário), A/B split, cloaking |
| **Hub Digital** | Página pública tipo "link in bio" com 9 tipos de bloco, incluindo PIX dinâmico e Agenda com horários reais |
| **Importador de Favoritos** | Parser NETSCAPE Bookmark File, health check assíncrono, exportação |
| **Launcher** | Busca fuzzy (Bitap) sobre favoritos/links/QR Codes — widget embutível (Shadow DOM) e agente de desktop (Electron) |
| **Mini Ferramentas** | CEP, IMC, conversor de moedas, compressão de imagem/PDF, geradores diversos — público, sem login |
| **API REST v1** | Autenticação via `X-API-Key`, endpoints de QR/Links/estatísticas |

## Stack

PHP 8.2 (PDO, sem framework) · MySQL 8 · Redis 7 (fila + cache) · Bootstrap 5.3 ·
`chillerlan/php-qrcode` · `khanamiryan/qrcode-detector-decoder` · `intervention/image` ·
`phpmailer/phpmailer` · `predis/predis`. Detalhes completos da arquitetura em [CLAUDE.md](CLAUDE.md).

## Setup

```bash
composer install
cp .env.example .env      # edite DB_*, REDIS_*, APP_KEY
composer migrate          # cria as tabelas
composer seed              # usuário admin inicial
composer serve              # http://localhost:8000
```

Extensões PHP obrigatórias: `pdo_mysql`, `redis`, `gd`, `fileinfo`, `zip`, `intl`,
`mbstring`, `json`, `curl`, `openssl`. Ghostscript (`gs`) é necessário para a
compressão de PDF em `/tools`.

Workers (fila Redis — QR-Logo, cliques, health-check de favoritos, e-mail):

```bash
php workers/queue-worker.php qrlogo &
php workers/queue-worker.php default &
```

Em produção, use os arquivos em [`supervisor/`](supervisor/) para manter os workers vivos.

## Estrutura

```
app/Controllers/   Controllers MVC
app/Models/         Modelos (PDO wrapper via App\Core\Model)
app/Views/           Views PHP puro por módulo
app/Core/            Router, Auth, Session, Cache (Redis), Validator, helpers
app/Services/        Regras de negócio (QRGenerator, ShortURLService, BookmarkImporterService...)
config/               app.php, database.php, redis.php, routes.php
database/migrations/  SQL numerado, aplicado via database/migrate.php
desktop-agent/         Agente Electron do Launcher (repositório próprio)
```

## Documentação

- [CLAUDE.md](CLAUDE.md) — especificação técnica completa (schema, rotas, segurança, design system)
- [desktop-agent/README.md](desktop-agent/README.md) — arquitetura e setup do Launcher desktop
