TCExam - README
===========================================================

FOR ANY COMMERCIAL USAGE PLEASE CONTACT: info@tecnick.com

------------------------------------------------------------

Name: TCExam

Version: 12.0.008

Release date: 2013-04-12

Author:
	Nicola Asuni

Copyright (c) 2004-2013:
	Nicola Asuni
	Tecnick.com LTD
	Manor Coach House, Church Hill
	Aldershot, Hants, GU12 4RQ
	UK
	http://www.tecnick.com

URLs:
	http://www.tcexam.org
	https://github.com/tecnickcom/tcexam

Requirements and Install Documentation:
	[ENG] install/index.htm
	Check also the documentation on http://www.tcexam.org

Source Documentation:
	http://www.tcexam.org

Support - Help:
	Please use support forum under http://www.sourceforge.net/projects/tcexam

Software License
	Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com LTD

	This program is free software for non-commercial usage: 
	you can redistribute it and/or modify it under the terms of the 
	GNU Affero General Public License as published by the Free Software 
	Foundation, either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

	Additionally, you can't remove, move or hide the original TCExam
	logo, copyrights statements and links to Tecnick.com and TCExam Websites.
	
	For any commercial usage please contact info@tecnick.com.

	See LICENSE.TXT file for more information.

Third-party Software

	PHPMailer
		Full Featured Email Transfer Class for PHP.
		Author: Brent R. Matzelle (bmatzelle@yahoo.com)
		Homepage: http://phpmailer.sourceforge.net/
		License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
		Location: /shared/phpmailer/

	LaTeX rendering Class is based on:
		LaTeX Rendering Class v0.8 by Benjamin Zeiss, currently maintained by Steve Mayer.
		Homepage: http://www.mayer.dial.pipex.com/tex.htm
		License: GNU GENERAL PUBLIC LICENSE
		Location: /shared/code/tce_latexrender.php

	Radius Class
		Radius client implementation in pure PHP.
		Author: SysCo/al (developer@sysco.ch)
		Homepage: http://developer.sysco.ch/php/
		License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
		Location: /shared/radius/

	phpCAS
		CAS client in PHP.
		Copyright: Copyright © 2003-2008, The ESUP-Portail consortium & the JA-SIG Collaborative. All rights reserved.
		Author: Pascal Aubry.
		Developers: Pascal Aubry, University of Rennes 1, Brett Bieber, University of Nebraska-Lincoln, Julien Marchal, University of Nancy 2, Olivier Berger, Institut TELECOM, SudParis.
		This class includes an integrated copy of domxml-php4-php5.php, written by Alexandre Alapetite, Copyright 2004, Licence: Creative Commons "Attribution-ShareAlike 2.0 France" BY-SA.
		This class has been changed and fixed for better PHP5 compatibility by: Tim Gebhardt (DePaul University's College of Computing and Digital Media) and Nicola Asuni (Tecnick.com LTD).
		Homepage: http://www.ja-sig.org/wiki/display/CASC/phpCAS
		License: New BSD License, an OSI-certified ("open") and Gnu/FSF-recognized ("free") license.
		Location: /shared/cas/

	The DHTML Calendar
		Calendar widget written in Javascript.
		Author: Mihai Bazon (mihai_bazon@yahoo.com)
		Homepage: http://dynarch.com/mishoo/
		License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
		Location: /shared/jscripts/jscalendar/

	Javascript VirtualKeyboard
		Author: Ilya Lebedev (ilya@lebedev.net)
		Homepage: http://debugger.ru/projects/virtualkeyboard
		License: LGPL (GNU LESSER GENERAL PUBLIC LICENSE)
		Location: /shared/jscripts/vk/

	Fonts
		TCExam includes fonts for the TCPDF library, they are not striclty required but have been included as you convenience. The original TTF font files have been renamed for compatibility with TCPDF and compressed using the gzcompress PHP function that uses the ZLIB data format (.z files). To get the original distribution archives please check the information on fonts subfolders:
			DejaVu fonts 2.33 (Bitstream) - Copyright, License and other info: fonts/dejavu-fonts-ttf-2.33
			GNU FreeFont (GNU-GPLv3) - Copyright, License and other info: fonts/freefont-20100919


Description:
	TCExam is a technological solution (CBA - Computer-Based Assessment) to
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

	KEY FEATURES

	* Flexibility and Configurability: The system has been designed to offer a
	high degree of adaptability to cover a great variety of usage scenarios.
	Numerous configuration features allow customizing TCExam to fit all possible
	requirements.

	* Free Open Source Software (FOSS): TCExam software is distributed with
	GNU-AGPLv3 license. Open Source promotes Software reliability and quality by
	supporting independent peer review and rapid evolution of the source code.

	* Web-based Architecture: TCExam is Web-based and developed with LAMP
	(Linux, Apache, MySQL, PHP) technology. User-friendly interface allows you
	to install and use TCExam by simply connecting one cable to the switch of
	your computer network. All the computers on the network will be able to use
	TCExam independently from the client’s operating system as a normal intranet
	website, with no need to install any additional software or plug-in.

	* Internationalization (I18N): TCExam is language-independent through the
	adoption of the UTF-8, Unicode and  TMX standards. It supports the
	Right-To-Left mode and currently includes translations in 23 different
	languages.

	* Accessibility and Usability: TCExam Web interface conforms to the XHTML
	1.0 Strict standard, the guidelines on Accessibility (W3C-WAI-WCAG 1.0) and
	Usability, to provide equal access and equal opportunity to people with
	disabilities, including blindness.

	* Results and Statistics: TCExam outputs a variety of result pages, enabling
	various selection filters and providing numerous statistical indexes.
	Results and statistics can then be exported into various formats for filing
	or reworking. The test-takers can immediately be informed of the result of
	their test, or have it delivered via email.

	* Data Import and Export: TCExam uses Open Standard protocols for data
	filing and interchange: TSV, XML and PDF. Everything is fully documented to
	be easily extended or used by external applications. Custom filters can be
	added to import data from other systems. Include Optical Mark Recognition
	(OMR) system to import users' answers from paper sheets. TCExam supports
	several types of remote authentication and single-sign-on protocols: LDAP,
	RADIUS, CAS.

	* Multimedia Content: TCExam uses a common mark-up language to add text
	formatting, images, multimedia objects (audio and video) and mathematical
	formulas (supports LaTeX).

	* Unique Tests: TCExam can simultaneously generate unique tests for
	different users by randomly selecting and sorting questions and alternative
	answers. This feature drastically reduces or eliminates the chances of
	cheating on the test.

	* Paper Testing with Optical Character Recognition (OMR): TCExam can
	generate printable PDF documents for pen-and-paper testing. The OMR answer
	sheet can be scanned and uploaded to TCExam for automatic test importing,
	scoring and reporting.

	* Security: TCExam is more secure than traditional Pen-and-Paper testing.
	It supports SSL (Secure Socket Layer) encryption and includes various
	authentication levels to discriminate the access to the various sections
	(user/password, access level, group, IP address). Test time, test activity,
	results, and statistics: every part of the test is managed and stored in the
	server, leaving no sensitive data in the users’ client computers.

================================================================================
