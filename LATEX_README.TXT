TCExam LaTeX rendering
============================================================

The LaTeX code could be added to the description of topics, questions and answers, for example:

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

The TCExam LaTeX render converts the code to a PNG image that is cached on CACHE folder for successive calls to the same code.

The LaTeX rendering requires the following additional software:

 - LaTeX (http://www.latex-project.org/ or http://www.tug.org/teTeX/), for Windows I suggest to use MiKTeX (http://miktex.org/);
 - ImageMagick (http://www.imagemagick.org/);

Then you have to edit the file /shared/config/tce_latex.php to match your system settings.

In Linux do "which latex", "which convert" and "which identify" to find right the paths.
In Windows use the dir /x command to find the short (DOS) path to the executables.


Notes
-----

1. to debug the system comment the following line on /shared/code/tce_functions_errmsg.php :
	//$old_error_handler = set_error_handler("F_error_handler", K_ERROR_TYPES);

2. The default is to use article.cls for LaTeX which is a common class but it only supports 10,11,12 point font sizes. For smaller (or larger fonts) in the image, install the extsizes package available from CTAN http://ctan.tug.org/. Add these files to a new extsizes directory in usr/share/texmf/tex/latex. Refresh the database using "texhash" command (if using teTeX) or MiKTex Options, Refresh Now (Windows), Then in class.latexrender.php you can change var $_font_size = 10; to var $_font_size = 8;

3. You can make equation arrays and other code that starts with \begin, by prefacing them with 2 new lines.

4. Displayed formulae can be rendered using \displaystyle;

5. Examples of conversions can be found in http://www.mayer.dial.pipex.com/latexrender/latexrender.htm

6. Although the default size is set, you can resize a formula by using \mbox as in 
\mbox{\huge\sqrt{2}} or \mbox{\footnotesize\sqrt{2}}

=== EOF ==========
