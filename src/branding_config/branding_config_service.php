<?php
require_once __DIR__ . '/../base_de_datos.php';

function brandingConfigDefaults(): array
{
    return [
        'primary_color' => '#8059d4ff',
        'logo_url' => '/buslinnes/assets/img/logomorado.svg',
        'favicon_url' => '/buslinnes/mkcert/favicon.ico',
        'updated_at' => null
    ];
}

function brandingConfigGetCurrent(): array
{
    global $base_de_datos;

    $defaults = brandingConfigDefaults();

    try {
        $stmt = $base_de_datos->prepare(
            'SELECT primary_color, logo_url, favicon_url, updated_at FROM tab_branding_config WHERE id_config = 1 LIMIT 1;'
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return $defaults;
        }

        return [
            'primary_color' => !empty($row['primary_color']) ? $row['primary_color'] : $defaults['primary_color'],
            'logo_url' => !empty($row['logo_url']) ? $row['logo_url'] : $defaults['logo_url'],
            'favicon_url' => !empty($row['favicon_url']) ? $row['favicon_url'] : $defaults['favicon_url'],
            'updated_at' => $row['updated_at'] ?? null
        ];
    } catch (Throwable $e) {
        return $defaults;
    }
}

function brandingConfigInsert(string $primaryColor, ?string $logoUrl, ?string $faviconUrl = null): array
{
    global $base_de_datos;

    $stmt = $base_de_datos->prepare(
        'SELECT primary_color, logo_url, favicon_url, updated_at FROM fun_insert_branding_config(?::varchar, ?::text, ?::text);'
    );
    $stmt->execute([$primaryColor, $logoUrl, $faviconUrl]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return brandingConfigGetCurrent();
    }

    return [
        'primary_color' => $row['primary_color'],
        'logo_url' => $row['logo_url'],
        'favicon_url' => $row['favicon_url'],
        'updated_at' => $row['updated_at'] ?? null
    ];
}

function brandingConfigDeletePhysical(): bool
{
    global $base_de_datos;

    $stmt = $base_de_datos->prepare('SELECT fun_delete_branding_config() AS resultado;');
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return isset($row['resultado']) ? (bool)$row['resultado'] : false;
}
