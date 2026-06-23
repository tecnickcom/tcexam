# TCExam — Translators

We are looking for volunteer translators to help translate TCExam into more languages. TCExam
currently ships with 26 languages, maintained in a single TMX file.

## How to translate

1. Download TCExam from <https://github.com/tecnickcom/tcexam>.

2. Unpack the archive in your web root if you want to install it, or anywhere else if you only
   want to translate.

3. Locate the translation source file:

   ```
   shared/config.default/lang/language_tmx.xml
   ```

   (After installation a copy lives at `shared/config/lang/language_tmx.xml`.)

4. `language_tmx.xml` is an XML file in the **TMX** standard. It can be edited either manually or
   with a compatible CAT (Computer-Aided Translation) tool — see
   <https://en.wikipedia.org/wiki/Translation_Memory_eXchange>.

5. As a simpler alternative, you can directly translate one of the compiled per-language files in
   `cache/lang/` (these are build artifacts generated from the TMX).

## Notes

Please respect the original uppercase/lowercase and the length of the sentences.

### TUID identifier prefixes

| Prefix | Meaning |
|--------|---------|
| `d_` | a brief description |
| `h_` | a help message used in the `title` attribute of HTML tags |
| `w_` | a single word or short phrase used as a field label |
| `m_` | a general message |

### Special identifiers

- `a_meta_charset` — the charset for the language; must always be `UTF-8`.
- `a_meta_dir` — text direction: `ltr` (left to right) or `rtl` (right to left).
- `a_meta_language` — the HTML language code (`en` = English, `it` = Italian, …).

### HTML code

Some fields contain HTML markup. The `<` and `>` symbols are escaped as `&lt;` and `&gt;`.

### Testing

To test your translation in a running install: regenerate the language caches with
`make lang`, or delete the `cache/lang/language_tmx_*.php` files so they are rebuilt on the next
request.

## Submitting your work

Once completed, email the files to [info@tecnick.com](mailto:info@tecnick.com). Your credits will
be added to the credits page. Thank you!
