<?php
require_once __DIR__ . '/branding_config_service.php';

function brandingConfigSavePayload(string $primaryColor, ?string $logoUrl, ?string $faviconUrl = null): array
{
    return brandingConfigInsert($primaryColor, $logoUrl, $faviconUrl);
}

function brandingConfigResetPayload(): array
{
    $deleted = brandingConfigDeletePhysical();
    if (!$deleted) {
        throw new RuntimeException('No se pudo eliminar la configuracion de marca');
    }

    return brandingConfigDefaults();
}
