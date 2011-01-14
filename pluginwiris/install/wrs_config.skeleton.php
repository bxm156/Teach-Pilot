<?php
/* ERROR REPORTING */
//$CFG->wiriserrorreporting = E_ALL;


/* WIRIS Service */
$CFG->wirisservicehost = '@wirisservicehost@';	// Host of the application server
$CFG->wirisserviceport = '@wirisserviceport@';	// Port of the application server
$CFG->wirisservicepath = '@wirisservicepath@';	// Context root of the application server
$CFG->wirisserviceversion = '2.0';				// Wished version of the application server


/* WIRIS Image Service */
$CFG->wiristransparency = 'true';							// Set transparent background for the formulas (available for Mozilla / IE 7 or greater)
//$CFG->wirisimagebgcolor = '#fafafa';						// Background color of the formulas
//$CFG->wirisimagefontsize = '16';							// Font size of the formula
//$CFG->wirisimagesymbolcolor = '#000000';					// Symbol color
//$CFG->wirisimageidentcolor = '#000000';					// Ident color
//$CFG->wirisimagenumbercolor = '#000000';					// Number color
//$CFG->wirisimageidentmathvariant = 'italic-sans-serif';	// Font variant for idents
//$CFG->wirisimagenumbermathvariant = 'sans-serif';			// Font variant for numbers
//$CFG->wirisimagefontident = 'Helvetica';					// Font family for idents
//$CFG->wirisimagefontnumber = 'Arial';						// Font family for numbers
//$CFG->wirisimagefontranges = array('x3b1-x3ff;Lucida Console,105', 'x41-x5A;Helvetica');


/* WIRIS Editor - equation editor */
$CFG->wirisformulaeditorenabled = @wirisformulaeditorenabled@;	// Enable the insertion of formulas using WIRIS Editor
$CFG->wiriseditorarchive = 'wiriseditor.jar';					// SHOULD NOT BE USUALLY MODIFIED
$CFG->wiriseditorclass = 'WirisFormulaEditor';					// SHOULD NOT BE USUALLY MODIFIED


/* WIRIS CAS - calculator */
$CFG->wiriscasenabled = @wiriscasenabled@;				// Enable the insertion of WIRIS CAS Applet in the HTML Editor
$CFG->wiriscascodebase = '@wiriscascodebase@';			// Codebase of the WIRIS CAS applet
$CFG->wiriscasarchive = '@wiriscasarchive@';			// File of the WIRIS CAS applet
$CFG->wiriscasclass = '@wiriscasclass@';				// Class name of the WIRIS CAS applet
$CFG->wiriscaslang = '@wiriscaslang@';					// Available languages 'en,ca,de,es,et,eu,fr,it,nl,pt' (depend on your WIRIS CAS installation).


/* Filter variables */
$CFG->wirisfilterdir = 'filter/wiris';					// SHOULD USUALLY NOT BE MODIFIED
$CFG->wirisimagedir  = 'filter/wiris';					// SHOULD USUALLY NOT BE MODIFIED
$CFG->wirisformulaimageclass = 'Wirisformula';			// SHOULD USUALLY NOT BE MODIFIED
$CFG->wiriscasimageclass = 'Wiriscas';					// SHOULD USUALLY NOT BE MODIFIED

/* Proxy variables */
$CFG->wirisproxy = @wirisproxy@;
$CFG->wirisproxy_host = '@wirisproxy_host@';
$CFG->wirisproxy_port = @wirisproxy_port@;

$CFG->wirisPHP4compatibility = @wirisPHP4compatibility@;		// PHP 4 COMPATIBILITY: MARKT IT IF YOU ARE USING PHP 4, BUT DON'T USE PROXY.

include_once $CFG->dirroot . '/pluginwiris/version.php';
?>
