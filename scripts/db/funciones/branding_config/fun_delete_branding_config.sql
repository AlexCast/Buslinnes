CREATE OR REPLACE FUNCTION fun_delete_branding_config()
RETURNS BOOLEAN AS
$$
BEGIN
    DELETE FROM tab_branding_config
    WHERE id_config = 1;

    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;