# TCExam — LaTeX rendering

LaTeX code can be added to the description of topics, questions and answers. For example:

```
[tex]
\begin{displaymath}
\mathop{\mathrm{corr}}(X,Y)=
\frac{\displaystyle
\sum_{i=1}^n(x_i-\overline x)
(y_i-\overline y)}
{\displaystyle\biggl[
\sum_{i=1}^n(x_i-\overline x)^2
\sum_{i=1}^n(y_i-\overline y)^2
\biggr]^{1/2}}
\end{displaymath}
[/tex]
```

The TCExam LaTeX renderer converts the code to a PNG image that is cached in the `cache/` folder
and reused for later calls with the same code.

## Requirements

LaTeX rendering requires the following additional software to be installed on the server:

- **LaTeX** — <https://www.latex-project.org/> or <https://tug.org/texlive/>
  (on Windows, [MiKTeX](https://miktex.org/) is a good option);
- **ImageMagick** — <https://imagemagick.org/>.

## Configuration

Edit `shared/config/tce_latex.php` (shipped as `shared/config.default/tce_latex.php`) to match
your system. The relevant constants include:

| Constant | Purpose |
|----------|---------|
| `K_LATEX_PATH_CONVERT` | path to the ImageMagick `convert` (or `magick`) binary |
| `K_LATEX_PDFLATEX` | path to the `pdflatex` (LaTeX) binary |
| `K_LATEX_FONT_SIZE` | font size in points (default `10`) |
| `K_LATEX_CLASS` | LaTeX document class (default `article`) |
| `K_LATEX_FORMULA_DENSITY` | output image density / DPI |
| `K_LATEX_MAX_WIDTH` / `K_LATEX_MAX_HEIGHT` | maximum rendered image dimensions |
| `K_LATEX_IMG_FORMAT` | output image format (default `png`) |

To find executable paths on Linux, use `which pdflatex` and `which convert`. On Windows, use
`dir /x` to find the short (DOS) path to the executables. The renderer implementation lives in
`shared/code/tce_latexrender.php`.

## Notes

1. **Debugging.** To see raw errors while diagnosing problems, temporarily comment out the
   error-handler registration in `shared/code/tce_functions_errmsg.php`:

   ```php
   //$old_error_handler = set_error_handler('F_error_handler', K_ERROR_TYPES);
   ```

2. **Font sizes.** The default document class (`article`) only supports 10, 11 and 12 pt font
   sizes. For smaller or larger fonts, install the `extsizes` package from
   [CTAN](https://ctan.org/pkg/extsizes), refresh the TeX database (`texhash`, or *MiKTeX
   Options → Refresh Now* on Windows), then set `K_LATEX_FONT_SIZE` in `shared/config/tce_latex.php`
   accordingly.

3. **Multi-line environments.** You can build equation arrays and other `\begin{…}` blocks by
   prefacing them with two newlines.

4. **Display style.** Render formulae in display style with `\displaystyle`.

5. **Per-formula sizing.** Even with a default size set, you can resize a single formula with
   `\mbox`, e.g. `\mbox{\huge\sqrt{2}}` or `\mbox{\footnotesize\sqrt{2}}`.
