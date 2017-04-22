# TCExam - README

------------------------------------------------------

FOR ANY USAGE THAT REQUIRES COMMERCIAL LICENSING TERMS,
PLEASE CONTACT INFO@TECNICK.COM TO PURCHASE A LICENSE.

------------------------------------------------------

* Name: TCExam
* Author: Nicola Asuni
* URLs:
	* http://www.tcexam.org
	* https://github.com/tecnickcom/tcexam
* Requirements and Install Documentation:
	* [ENG] install/index.htm
	* Check also the documentation on http://www.tcexam.org
* Source Documentation:
	http://www.tcexam.org
* Support - Help:
	Please use support Issues at https://github.com/tecnickcom/tcexam
* Software License:
	* Copyright (C) 2004-2017 Nicola Asuni - Tecnick.com LTD
	* See LICENSE.TXT file for more information.


[![Donate via PayPal](https://img.shields.io/badge/donate-paypal-87ceeb.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&currency_code=GBP&business=paypal@tecnick.com&item_name=donation%20for%20tcexam%20project)
*Please consider supporting this project by making a donation via [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&currency_code=GBP&business=paypal@tecnick.com&item_name=donation%20for%20tcexam%20project)*


## Third-party Software

TCExam includes some third-party software components that are not striclty required but have been included as you convenience, and if used are subject to their respective licenses.

* **PHPMailer**
    * Full Featured Email Transfer Class for PHP.
    * Author: Brent R. Matzelle (bmatzelle@yahoo.com)
    * Homepage: http://phpmailer.sourceforge.net/
    * License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
    * Location: /shared/phpmailer/

* **The DHTML Calendar**
    * Calendar widget written in Javascript.
    * Author: Mihai Bazon (mihai_bazon@yahoo.com)
    * Homepage: http://dynarch.com/mishoo/
    * License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
    * Location: /shared/jscripts/jscalendar/

* **Javascript VirtualKeyboard**
    * Author: Ilya Lebedev (ilya@lebedev.net)
    * Homepage: http://debugger.ru/projects/virtualkeyboard
    * License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
    * Location: /shared/jscripts/vk/

* **Radius Class**
    * Radius client implementation in pure PHP.
    * Author: SysCo/al (developer@sysco.ch)
    * Homepage: http://developer.sysco.ch/php/
    * License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
    * Location: /shared/radius/

* **phpCAS**
    * Central Authentication Service (CAS) client in PHP.
    * Copyright 2007-2011, JA-SIG, Inc. http://www.jasig.org/
    * Homepage: https://wiki.jasig.org/display/CASC/phpCAS
    * License: Apache License, Version 2.0 http://www.apache.org/licenses/LICENSE-2.0
    * Location: /shared/cas/

* **Fonts**

    TCExam includes fonts for the TCPDF library, they are not striclty required but have been included as you convenience.

    All the PHP files on the fonts directory are subject to the general TCPDF license (GNU-LGPLv3),
    they do not contain any binary data but just a description of the general properties of a particular font.
    These files can be also generated on the fly using the font utilities and TCPDF methods.

    All the original binary TTF font files have been renamed for compatibility with TCPDF and compressed using the gzcompress PHP function that uses the ZLIB data format (.z files).

    The binary files (.z) that begins with the prefix "free" have been extracted from the GNU FreeFont collection (GNU-GPLv3).
    The binary files (.z) that begins with the prefix "pdfa" have been derived from the GNU FreeFont, so they are subject to the same license.
    For the details of Copyright, License and other information, please check the files inside the directory fonts/freefont-20100919
    Link : http://www.gnu.org/software/freefont/

    The binary files (.z) that begins with the prefix "dejavu" have been extracted from the DejaVu fonts 2.33 (Bitstream) collection.
    For the details of Copyright, License and other information, please check the files inside the directory fonts/dejavu-fonts-ttf-2.33
    Link : http://dejavu-fonts.org

    The binary files (.z) that begins with the prefix "ae" have been extracted from the Arabeyes.org collection (GNU-GPLv2).
    Link : http://projects.arabeyes.org/


## Description

TCExam is a software solution (CBA - Computer-Based Assessment) to
drastically simplify the evaluation process for schools, universities,
recruiting firms as well as private and public companies, allowing
professors, teachers and examiners to create, distribute and manage exams,
tests, surveys and quizzes.

Compared to the traditional Pen-and-Paper Testing (PPT), TCExam simplifies
the whole process of evaluation reducing costs and improving quality and
reliability of the examinations.

TCExam comes in a variety of languages and is already used all over the
world by top universities, schools, private and public institutions,
independent educators and research centers.

### KEY FEATURES

* **Flexibility and Configurability**: The system has been designed to offer a
high degree of adaptability to cover a great variety of usage scenarios.
Numerous configuration features allow customizing TCExam to fit all possible
requirements.

* **Free Open Source Software (FOSS)**: TCExam software is distributed with
GNU-AGPLv3 license. Open Source promotes Software reliability and quality by
supporting independent peer review and rapid evolution of the source code.

* **Web-based Architecture**: TCExam is Web-based and developed with LAMP
(Linux, Apache, MySQL, PHP) technology. User-friendly interface allows you
to install and use TCExam by simply connecting one cable to the switch of
your computer network. All the computers on the network will be able to use
TCExam independently from the client’s operating system as a normal intranet
website, with no need to install any additional software or plug-in.

* **Internationalization (I18N)**: TCExam is language-independent through the
adoption of the UTF-8, Unicode and  TMX standards. It supports the
Right-To-Left mode and currently includes translations in 25 different
languages.

* **Accessibility and Usability**: TCExam Web interface conforms to the XHTML
1.0 Strict standard, the guidelines on Accessibility (W3C-WAI-WCAG 1.0) and
Usability, to provide equal access and equal opportunity to people with
disabilities, including blindness.

* **Results and Statistics**: TCExam outputs a variety of result pages, enabling
various selection filters and providing numerous statistical indexes.
Results and statistics can then be exported into various formats for filing
or reworking. The test-takers can immediately be informed of the result of
their test, or have it delivered via email.

* **Data Import and Export**: TCExam uses Open Standard protocols for data
filing and interchange: TSV, XML and PDF. Everything is fully documented to
be easily extended or used by external applications. Custom filters can be
added to import data from other systems. Include Optical Mark Recognition
(OMR) system to import users' answers from paper sheets. TCExam supports
several types of remote authentication and single-sign-on protocols: LDAP,
RADIUS, CAS.

* **Multimedia Content**: TCExam uses a common mark-up language to add text
formatting, images, multimedia objects (audio and video) and mathematical
formulas (supports LaTeX and MathML).

* **Unique Tests**: TCExam can simultaneously generate unique tests for
different users by randomly selecting and sorting questions and alternative
answers. This feature drastically reduces or eliminates the chances of
cheating on the test.

* **Paper Testing with Optical Character Recognition (OMR)**: TCExam can
generate printable PDF documents for pen-and-paper testing. The OMR answer
sheet can be scanned and uploaded to TCExam for automatic test importing,
scoring and reporting.

* **Security**: TCExam is more secure than traditional Pen-and-Paper testing.
It supports SSL (Secure Socket Layer) encryption and includes various
authentication levels to discriminate the access to the various sections
(user/password, access level, group, IP address). Test time, test activity,
results, and statistics: every part of the test is managed and stored in the
server, leaving no sensitive data in the users’ client computers.
