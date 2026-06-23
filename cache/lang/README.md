# Language cache files

This folder contains the per-language translation caches (language_tmx_<lang>.php).

These files are BUILD ARTIFACTS generated from the TMX source
(shared/config/lang/language_tmx.xml) and are no longer tracked in git
(see the cache/lang/*.php entry in the repository .gitignore).

Generate / refresh them at deploy time with:

    make lang

If the caches are absent they are also regenerated lazily on the first request
per language (slower first hit), so the application still works without the
build step — running `make lang` simply pre-warms them and avoids a cold parse.
