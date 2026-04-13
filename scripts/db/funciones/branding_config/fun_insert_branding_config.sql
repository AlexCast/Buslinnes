CREATE OR REPLACE FUNCTION fun_insert_branding_config(
    wprimary_color tab_branding_config.primary_color%TYPE,
    wlogo_url tab_branding_config.logo_url%TYPE,
    wfavicon_url tab_branding_config.favicon_url%TYPE DEFAULT NULL
) RETURNS TABLE (
    primary_color tab_branding_config.primary_color%TYPE,
    logo_url tab_branding_config.logo_url%TYPE,
    favicon_url tab_branding_config.favicon_url%TYPE,
    updated_at tab_branding_config.updated_at%TYPE
) AS
$$
DECLARE
    v_primary_color tab_branding_config.primary_color%TYPE := lower(trim(coalesce(wprimary_color, '#8059d4ff')));
    v_logo_url tab_branding_config.logo_url%TYPE;
    v_favicon_url tab_branding_config.favicon_url%TYPE;
    v_current_logo tab_branding_config.logo_url%TYPE;
    v_current_favicon tab_branding_config.favicon_url%TYPE;
BEGIN
    IF v_primary_color !~* '^#([0-9a-f]{6}|[0-9a-f]{8})$' THEN
        RAISE EXCEPTION 'Color invalido. Use #RRGGBB o #RRGGBBAA';
    END IF;

    SELECT t.logo_url, t.favicon_url
    INTO v_current_logo, v_current_favicon
    FROM tab_branding_config t
    WHERE t.id_config = 1
    LIMIT 1;

    IF wlogo_url IS NULL OR trim(wlogo_url) = '' THEN
        v_logo_url := coalesce(v_current_logo, '/buslinnes/assets/img/logomorado.svg');
    ELSE
        v_logo_url := trim(wlogo_url);
    END IF;

    IF wfavicon_url IS NULL OR trim(wfavicon_url) = '' THEN
        v_favicon_url := coalesce(v_current_favicon, '/buslinnes/mkcert/favicon.ico');
    ELSE
        v_favicon_url := trim(wfavicon_url);
    END IF;

    INSERT INTO tab_branding_config (id_config, primary_color, logo_url, favicon_url, updated_at)
    VALUES (1, v_primary_color, v_logo_url, v_favicon_url, NOW())
    ON CONFLICT (id_config)
    DO UPDATE SET
        primary_color = EXCLUDED.primary_color,
        logo_url = EXCLUDED.logo_url,
        favicon_url = EXCLUDED.favicon_url,
        updated_at = NOW();

    RETURN QUERY
    SELECT t.primary_color, t.logo_url, t.favicon_url, t.updated_at
    FROM tab_branding_config t
    WHERE t.id_config = 1
    LIMIT 1;
END;
$$ LANGUAGE plpgsql;