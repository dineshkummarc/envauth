CREATE TABLE IF NOT EXISTS "music" (
    "id" BIGSERIAL PRIMARY KEY,
    "title" VARCHAR(255) NOT NULL,
    "artist" VARCHAR(255) NOT NULL,
    "duration" SMALLINT NULL,
    "created_at" TIMESTAMP NULL,
    "is_active" BOOLEAN NULL DEFAULT NULL
);
