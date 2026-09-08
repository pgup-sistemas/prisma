<?php

declare(strict_types=1);

namespace App\Services;

use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QROutputInterface;

class QRGenerator
{
    private const ECC_MAP = [
        'L' => EccLevel::L,
        'M' => EccLevel::M,
        'Q' => EccLevel::Q,
        'H' => EccLevel::H,
    ];

    public const TYPE_FIELDS = [
        'url'      => ['url'],
        'text'     => ['text'],
        'email'    => ['to', 'subject', 'body'],
        'phone'    => ['phone'],
        'sms'      => ['phone', 'body'],
        'whatsapp' => ['phone', 'text'],
        'wifi'     => ['ssid', 'password', 'encryption', 'hidden'],
        'vcard'    => ['name', 'phone', 'email', 'organization', 'title', 'url'],
        'geo'      => ['lat', 'lng', 'label'],
        'event'    => ['title', 'start', 'end', 'location', 'description'],
        'bitcoin'  => ['address', 'amount'],
        'pix'      => ['key', 'name', 'city', 'amount', 'txid', 'description'],
    ];

    public const TYPE_RULES = [
        'url'      => ['url' => 'required|url'],
        'text'     => ['text' => 'required'],
        'email'    => ['to' => 'required|email'],
        'phone'    => ['phone' => 'required'],
        'sms'      => ['phone' => 'required'],
        'whatsapp' => ['phone' => 'required'],
        'wifi'     => ['ssid' => 'required'],
        'vcard'    => ['name' => 'required'],
        'geo'      => ['lat' => 'required|numeric', 'lng' => 'required|numeric'],
        'event'    => ['title' => 'required', 'start' => 'required'],
        'bitcoin'  => ['address' => 'required'],
        'pix'      => ['key' => 'required', 'name' => 'required', 'city' => 'required'],
    ];

    /**
     * Constrói o conteúdo textual do QR Code a partir do tipo e dos dados fornecidos.
     */
    public function buildContent(string $type, array $data): string
    {
        return match ($type) {
            'url'      => $this->buildURL($data),
            'text'     => $this->buildText($data),
            'email'    => $this->buildEmail($data),
            'phone'    => $this->buildPhone($data),
            'sms'      => $this->buildSMS($data),
            'whatsapp' => $this->buildWhatsApp($data),
            'wifi'     => $this->buildWiFi($data),
            'vcard'    => $this->buildVCard($data),
            'geo'      => $this->buildGeo($data),
            'event'    => $this->buildEvent($data),
            'bitcoin'  => $this->buildBitcoin($data),
            'pix'      => $this->buildPix($data),
            default    => throw new \InvalidArgumentException("Tipo de QR Code inválido: {$type}"),
        };
    }

    public function buildURL(array $data): string
    {
        return (string) ($data['url'] ?? '');
    }

    public function buildText(array $data): string
    {
        return (string) ($data['text'] ?? '');
    }

    public function buildEmail(array $data): string
    {
        $to = rawurlencode((string) ($data['to'] ?? ''));
        $params = array_filter([
            'subject' => $data['subject'] ?? null,
            'body'    => $data['body'] ?? null,
        ]);

        $query = http_build_query($params);

        return 'mailto:' . str_replace('%40', '@', $to) . ($query !== '' ? '?' . $query : '');
    }

    public function buildPhone(array $data): string
    {
        return 'tel:' . $this->onlyPhoneChars((string) ($data['phone'] ?? ''));
    }

    public function buildSMS(array $data): string
    {
        $phone = $this->onlyPhoneChars((string) ($data['phone'] ?? ''));
        $body = (string) ($data['body'] ?? '');

        return 'sms:' . $phone . ($body !== '' ? '?body=' . rawurlencode($body) : '');
    }

    public function buildWhatsApp(array $data): string
    {
        $phone = preg_replace('/\D/', '', (string) ($data['phone'] ?? ''));
        $text = (string) ($data['text'] ?? '');

        return 'https://wa.me/' . $phone . ($text !== '' ? '?text=' . rawurlencode($text) : '');
    }

    public function buildWiFi(array $data): string
    {
        $type = strtoupper((string) ($data['encryption'] ?? 'WPA'));
        $type = in_array($type, ['WPA', 'WEP', 'NOPASS'], true) ? $type : 'WPA';
        $ssid = $this->escapeWifi((string) ($data['ssid'] ?? ''));
        $password = $this->escapeWifi((string) ($data['password'] ?? ''));
        $hidden = !empty($data['hidden']) ? 'true' : 'false';

        $wifi = "WIFI:T:{$type};S:{$ssid};";
        $wifi .= $type !== 'NOPASS' ? "P:{$password};" : '';
        $wifi .= "H:{$hidden};;";

        return $wifi;
    }

    public function buildVCard(array $data): string
    {
        $name = (string) ($data['name'] ?? '');
        $phone = (string) ($data['phone'] ?? '');
        $email = (string) ($data['email'] ?? '');
        $org = (string) ($data['organization'] ?? '');
        $title = (string) ($data['title'] ?? '');
        $url = (string) ($data['url'] ?? '');

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:' . $this->escapeVCard($name),
            'FN:' . $this->escapeVCard($name),
        ];

        if ($org !== '')   $lines[] = 'ORG:' . $this->escapeVCard($org);
        if ($title !== '') $lines[] = 'TITLE:' . $this->escapeVCard($title);
        if ($phone !== '') $lines[] = 'TEL;TYPE=CELL:' . $phone;
        if ($email !== '') $lines[] = 'EMAIL:' . $email;
        if ($url !== '')   $lines[] = 'URL:' . $url;

        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines);
    }

    public function buildGeo(array $data): string
    {
        $lat = (string) ($data['lat'] ?? '0');
        $lng = (string) ($data['lng'] ?? '0');
        $label = (string) ($data['label'] ?? '');

        return "geo:{$lat},{$lng}" . ($label !== '' ? '?q=' . rawurlencode($label) : '');
    }

    public function buildEvent(array $data): string
    {
        $title = (string) ($data['title'] ?? '');
        $start = $this->toIcalDate((string) ($data['start'] ?? ''));
        $end = $this->toIcalDate((string) ($data['end'] ?? ''));
        $location = (string) ($data['location'] ?? '');
        $description = (string) ($data['description'] ?? '');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//PRISMA//QR Code Generator//PT',
            'BEGIN:VEVENT',
            'UID:' . uuid4() . '@prisma.app',
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'SUMMARY:' . $this->escapeVCard($title),
        ];

        if ($start !== '')       $lines[] = 'DTSTART:' . $start;
        if ($end !== '')         $lines[] = 'DTEND:' . $end;
        if ($location !== '')    $lines[] = 'LOCATION:' . $this->escapeVCard($location);
        if ($description !== '') $lines[] = 'DESCRIPTION:' . $this->escapeVCard($description);

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines);
    }

    public function buildBitcoin(array $data): string
    {
        $address = (string) ($data['address'] ?? '');
        $amount = $data['amount'] ?? null;

        return 'bitcoin:' . $address . ($amount ? '?amount=' . $amount : '');
    }

    /**
     * EMV QR Code estático para PIX (Manual BR Code Bacen v2.1).
     */
    public function buildPix(array $data): string
    {
        $key = (string) ($data['key'] ?? '');
        $name = strtoupper(substr((string) ($data['name'] ?? ''), 0, 25));
        $city = strtoupper(substr((string) ($data['city'] ?? ''), 0, 15));
        $amount = isset($data['amount']) && $data['amount'] !== '' ? (float) $data['amount'] : null;
        $txid = (string) ($data['txid'] ?? '***');
        $description = $data['description'] ?? null;

        $merchantAccount = $this->emvField('00', 'br.gov.bcb.pix')
            . $this->emvField('01', $key)
            . ($description !== null ? $this->emvField('02', substr((string) $description, 0, 99)) : '');

        $payload = $this->emvField('00', '01')
            . $this->emvField('26', $merchantAccount)
            . $this->emvField('52', '0000')
            . $this->emvField('53', '986')
            . ($amount !== null && $amount > 0 ? $this->emvField('54', number_format($amount, 2, '.', '')) : '')
            . $this->emvField('58', 'BR')
            . $this->emvField('59', $name !== '' ? $name : 'PRISMA')
            . $this->emvField('60', $city !== '' ? $city : 'BRASIL')
            . $this->emvField('62', $this->emvField('05', $txid !== '' ? $txid : '***'));

        $payload .= '6304';

        return $payload . strtoupper(sprintf('%04X', $this->crc16ccitt($payload)));
    }

    /**
     * Renderiza o QR Code em PNG e SVG a partir do conteúdo já construído.
     *
     * @return array{png: string, svg: string}
     */
    public function render(string $content, array $options, string $filenameBase): array
    {
        $size = (int) ($options['size'] ?? 512);
        $eccLevel = self::ECC_MAP[$options['ecc_level'] ?? 'M'] ?? EccLevel::M;
        $fg = $this->hexToRgb((string) ($options['fg_color'] ?? '#000000'));
        $bg = $this->hexToRgb((string) ($options['bg_color'] ?? '#FFFFFF'));
        $transparent = !empty($options['transparent']);

        $scale = max(1, (int) round($size / 45));

        $dir = ROOT . '/storage/qrcodes';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pngPath = "{$dir}/{$filenameBase}.png";
        $svgPath = "{$dir}/{$filenameBase}.svg";

        $pngOptions = new QROptions([
            'version'         => \chillerlan\QRCode\Common\Version::AUTO,
            'eccLevel'        => $eccLevel,
            'outputType'      => QROutputInterface::GDIMAGE_PNG,
            'scale'           => $scale,
            'imageTransparent' => $transparent,
            'moduleValues'    => $this->moduleValues($fg, $bg),
            'bgColor'         => $bg,
        ]);
        (new QRCode($pngOptions))->render($content, $pngPath);

        $svgOptions = new QROptions([
            'version'      => \chillerlan\QRCode\Common\Version::AUTO,
            'eccLevel'     => $eccLevel,
            'outputType'   => QROutputInterface::MARKUP_SVG,
            'scale'        => $scale,
            'moduleValues' => $this->moduleValues($this->rgbToHex($fg), $transparent ? 'transparent' : $this->rgbToHex($bg)),
            'svgAddXmlHeader' => true,
            'outputBase64' => false,
        ]);
        file_put_contents($svgPath, (new QRCode($svgOptions))->render($content));

        return [
            'png' => 'storage/qrcodes/' . $filenameBase . '.png',
            'svg' => 'storage/qrcodes/' . $filenameBase . '.svg',
        ];
    }

    private function moduleValues(mixed $dark, mixed $light): array
    {
        $values = [];

        foreach (QROutputInterface::DEFAULT_MODULE_VALUES as $module => $isDark) {
            $values[$module] = $isDark ? $dark : $light;
        }

        return $values;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function rgbToHex(array $rgb): string
    {
        return sprintf('#%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2]);
    }

    private function onlyPhoneChars(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone);
    }

    private function escapeWifi(string $value): string
    {
        return addcslashes($value, '\\;,:"');
    }

    private function escapeVCard(string $value): string
    {
        return str_replace(["\\", ',', ';', "\n"], ['\\\\', '\,', '\;', '\n'], $value);
    }

    private function toIcalDate(string $datetime): string
    {
        if ($datetime === '') {
            return '';
        }

        $ts = strtotime($datetime);

        return $ts === false ? '' : gmdate('Ymd\THis\Z', $ts);
    }

    private function emvField(string $id, string $value): string
    {
        return $id . str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    private function crc16ccitt(string $data): int
    {
        $crc = 0xFFFF;

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);

            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return $crc;
    }
}
