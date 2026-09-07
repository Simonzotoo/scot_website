-- Which student group a document applies to (shown as a badge on the
-- public Downloads page) — the first batch of documents are all MSc-only,
-- and this needs to stay correct as other programmes' documents get added
-- later rather than being a single hardcoded page-wide claim.
ALTER TABLE documents
    ADD COLUMN audience VARCHAR(60) NOT NULL DEFAULT 'All Students' AFTER title,
    ADD COLUMN preview_path VARCHAR(255) NULL AFTER file_path;

UPDATE documents SET audience = 'MSc Students';
