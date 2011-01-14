<?php 

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com  //
//           (C) 2001-3001 Eloy Lafuente (stronk7) http://contiento.com  //
//           (C) 2009 Simon Karpen               http://voicethread.com  //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

// This filter allows you to embed VoiceThread content within Moodle. 
// See the README.txt or the instructions at http://voicethread.com/FIXME
// for more information

function voicethread_filter($courseid, $text) {

    global $CFG;

    $u = empty($CFG->unicodedb) ? '' : 'u'; //Unicode modifier

    if (!isset($CFG->voicethread_site)) {
        set_config( 'voicethread_site','voicethread.com' );
    } 
    $voicethread_site = $CFG->voicethread_site;

    $voicethread_site = preg_replace('/http:\/\//','',$voicethread_site);
    $voicethread_site = preg_replace('/\/$/','',$voicethread_site);

    preg_match_all('/\[\[vt:(.*?)(\|(.*?))?\]\]/s'.$u, $text, $list_of_movies);
    preg_match_all('/\[\[vtsmall:(.*?)(\|(.*?))?\]\]/s'.$u, $text, $list_of_small_movies);

/// No Voicethread links found. Return original text
    if (empty($list_of_movies[0]) && empty($list_of_small_movies[0])) {
        return $text;
    }

    foreach ($list_of_movies[0] as $key=>$item) {
        $replace = '';
    /// Extract info from the Voicethread link
        $movie = new stdClass;
        $movie->reference = $list_of_movies[1][$key];
        $movie->title = $list_of_movies[3][$key];
    /// Calculate footer text (it's optional in the filter)
        if ($movie->title) {
            $footertext = '<br /><span class="filtervoicethread-title">'.format_string($movie->title).'</span>';
        } else {
            $footertext = '';
        }
    /// Calculate the replacement
        $replace = '<div id="voicethread-container">'.
                   '<object width="800" height="600"> '.
                   '<param name="movie" value="http://'.$voicethread_site.'/book.swf?b='.$movie->reference.'"></param> '.
                   '<param name="wmode" value="transparent"></param>'.
                   '<embed src="http://'.$voicethread_site.'/book.swf?b='.$movie->reference.'" type="application/x-shockwave-flash" wmode="transparent" width="800" height="600"></embed>'.
                   '</object>'.$footertext.'</div>';
    /// If replace found, do it
        if ($replace) {
            $text = str_replace($list_of_movies[0][$key], $replace, $text);
        }
    }

    foreach ($list_of_small_movies[0] as $key=>$item) {
        $replace = '';
    /// Extract info from the VoiceThread link
        $movie = new stdClass;
        $movie->reference = $list_of_small_movies[1][$key];
        $movie->title = $list_of_small_movies[3][$key];
    /// Calculate footer text (it's optional in the filter)
        if ($movie->title) {
            $footertext = '<br /><span class="filtervoicethread-title">'.format_string($movie->title).'</span>';
        } else {
            $footertext = '';
        }
    /// Calculate the replacement
        $replace = '<div id="voicethread-container">'.
                   '<object width="480" height="360"> '.
                   '<param name="movie" value="http://'.$voicethread_site.'/book.swf?b='.$movie->reference.'"></param> '.
                   '<param name="wmode" value="transparent"></param>'.
                   '<embed src="http://'.$voicethread_site.'/book.swf?b='.$movie->reference.'" type="application/x-shockwave-flash" wmode="transparent" width="480" height="360"></embed>'.
                   '</object>'.$footertext.'</div>';
    /// If replace found, do it
        if ($replace) {
            $text = str_replace($list_of_small_movies[0][$key], $replace, $text);
        }
    }


/// Finally, return the text
    return $text;
}
?>
