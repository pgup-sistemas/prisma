ALTER TABLE hub_blocks
    MODIFY COLUMN type ENUM(
        'link','group','whatsapp','social','map',
        'schedule','catalog','video','contact_form','pix'
    ) NOT NULL;
