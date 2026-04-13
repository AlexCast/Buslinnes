<?php
require_once __DIR__ . '/branding_config_service.php';

function brandingConfigGetPayload(): array
{
    return brandingConfigGetCurrent();
}
