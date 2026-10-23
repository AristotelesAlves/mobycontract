DO $$
DECLARE
    nome_do_banco TEXT;
BEGIN
    FOREACH nome_do_banco IN ARRAY ARRAY['mobycontract', 'mobycontract_teste']
    LOOP
        IF NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = nome_do_banco) THEN
            EXECUTE format('CREATE DATABASE %I OWNER mobycontract', nome_do_banco);
        END IF;
    END LOOP;
END
$$;