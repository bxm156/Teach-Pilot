<?php
///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
//                                                                       //
// Copyright (C) 2010 Dual Code Inc. (www.dualcode.com)                  //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License version 2 as     //
// published by the Free Software Foundation.                            //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
// http://www.gnu.org/licenses/old-licenses/gpl-2.0.html                 //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/// Given an object containing all the necessary data,
/// (defined by the form in mod.html) this function
/// will create a new instance and return the id number
/// of the new instance.
function bigbluebutton_add_instance($bigbluebutton) {
    $bigbluebutton->timemodified = time();

    if ($returnid = insert_record('bigbluebutton', $bigbluebutton)) {
        $event = NULL;
        $event->courseid    = $bigbluebutton->course;
		$event->name		= $bigbluebutton->name;
		$event->meetingname = $bigbluebutton->meetingname;
        $event->meetingid 	= $bigbluebutton->meetingid;
        $event->attendeepw  = $bigbluebutton->attendeepw;
        $event->moderatorpw = $bigbluebutton->moderatorpw;
        $event->autologin 	= $bigbluebutton->autologin;
        $event->newwindow 	= $bigbluebutton->newwindow;
        $event->welcomemsg 	= $bigbluebutton->welcomemsg;		
        add_event($event);
    }
    return $returnid;
}


/// Given an object containing all the necessary data,
/// (defined by the form in mod.html) this function
/// will update an existing instance with new data.
function bigbluebutton_update_instance($bigbluebutton) {
    $bigbluebutton->timemodified = time();
    $bigbluebutton->id = $bigbluebutton->instance;
    if ($returnid = update_record('bigbluebutton', $bigbluebutton)) {
        $event = NULL;
        if ($event->id = get_field('event', 'id', 'modulename', 'bigbluebutton', 'instance', $bigbluebutton->id)) {
	        $event->courseid    = $bigbluebutton->course;
			$event->name		= $bigbluebutton->name;
			$event->meetingname = $bigbluebutton->meetingname;
	        $event->meetingid 	= $bigbluebutton->meetingid;
    	    $event->attendeepw  = $bigbluebutton->attendeepw;
        	$event->moderatorpw = $bigbluebutton->moderatorpw;
	        $event->autologin 	= $bigbluebutton->autologin;
    	    $event->newwindow 	= $bigbluebutton->newwindow;
        	$event->welcomemsg 	= $bigbluebutton->welcomemsg;	
			update_event($event);
        }
    }
    return $returnid;
}



/// Given an ID of an instance of this module,
/// this function will permanently delete the instance
/// and any data that depends on it.
function bigbluebutton_delete_instance($id) {
    if (! $bigbluebutton = get_record('bigbluebutton', 'id', $id)) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #

    if (! delete_records('bigbluebutton', 'id', $bigbluebutton->id)) {
        $result = false;
    }

    $pagetypes = page_import_types('mod/bigbluebutton/');
    foreach($pagetypes as $pagetype) {
        if(!delete_records('block_instance', 'pageid', $bigbluebutton->id, 'pagetype', $pagetype)) {
            $result = false;
        }
    }

    if (! delete_records('event', 'modulename', 'bigbluebutton', 'instance', $bigbluebutton->id)) {
        $result = false;
    }

    return $result;
}


// Create string where we check if the meeting is running
function wc_isMeetingRunningURL($myIP,$mySecuritySalt,$myMeetingID) {
	$checkAPI = "/bigbluebutton/api/isMeetingRunning?";
	$queryStr = "meetingID=".$myMeetingID;
	$checksum = sha1($queryStr.$mySecuritySalt);
	$secQueryURL = "http://".$myIP.$checkAPI.$queryStr."&checksum=".$checksum;
	
	return $secQueryURL;
}


// Determine if the meeting is already running (e.g. has attendees in it)
function wc_isMeetingRunning($myIP,$mySecuritySalt,$myMeetingID) {
	$secQueryURL = wc_isMeetingRunningURL($myIP,$mySecuritySalt,$myMeetingID);
	$myResponse = file_get_contents($secQueryURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;
	$runningNode = $doc->getElementsByTagName("running");
	$isRunning = $runningNode->item(0)->nodeValue;
	
	return $isRunning;
}


//Create meeting if it's not already running
function wc_createMeeting($myIP,$mySecuritySalt,$myMeetingName,$myMeetingID,$myAttendeePW,$myModeratorPW,$myWelcomeMsg,$myLogoutURL) {
	  $createAPI = "/bigbluebutton/api/create?";
	  $myVoiceBridge = rand(70000,79999);
	  $queryStr = "name=".$myMeetingName."&meetingID=".$myMeetingID."&attendeePW=".$myAttendeePW."&moderatorPW=".$myModeratorPW."&voiceBridge=".$myVoiceBridge."&welcome=".$myWelcomeMsg."&logoutUrl=".$myLogoutURL;
      $checksum = sha1($queryStr.$mySecuritySalt);
	  $secQueryURL = "http://".$myIP.$createAPI.$queryStr."&checksum=".$checksum;
	  $myResponse = file_get_contents($secQueryURL);
	  $doc= new DOMDocument();
	  $doc->loadXML($myResponse);
	  $returnCodeNode = $doc->getElementsByTagName("returncode");
	  $returnCode = $returnCodeNode->item(0)->nodeValue;

	  if ($returnCode=="SUCCESS") {
		return $returnCode;
	  }
	  else {
	    $messageKeyNode = $doc->getElementsByTagName("messageKey");
	    $messageKey = $messageKeyNode->item(0)->nodeValue;
		return $messageKey;
	  }
}


// Create a URL to join the meeting
function wc_joinMeetingURL($myIP,$mySecuritySalt,$myName,$myMeetingID,$myPassword) {
	$joinAPI = "/bigbluebutton/api/join?";
	$queryStr = "fullName=".$myName."&meetingID=".$myMeetingID."&password=".$myPassword;
    $checksum = sha1($queryStr.$mySecuritySalt);
	$createStr = "http://".$myIP.$joinAPI.$queryStr."&checksum=".$checksum;
	
	return $createStr;
}

// This API is not yet supported in bigbluebutton
function wc_endMeeting() {
	return false;
}

// This API is not yet supported in bigbluebutton
function wc_listAttendees() {
	return false;
}

// This API is not yet supported in bigbluebutton
function wc_getMeetingInfo() {
	return false;
}

// Determine the URL of the current page (for logoutURL)
function wc_currentPageURL() {
  $pageURL = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
  $pageURL .= $_SERVER['SERVER_PORT'] != '80' ? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"] : $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
  return $pageURL;
}


// Determine the IP/Domain of the current Corporate University
function wc_currentDomain() {
  $currentDomain = $_SERVER["SERVER_NAME"];
  return $currentDomain;
}


//////////////////////////////////////////////////
//
// The following functions are to communicate 
// with the Dual Code BigBlueButton network
//
//      DO NOT MODIFY THESE FUNCTIONS
//
//////////////////////////////////////////////////


function dc_authenticate($myAccountID,$myAccountPWD) {
	$authenticateURL = "http://bigbluebutton.dualcode.com/api.php?call=authenticate&accountid=".urlencode($myAccountID)."&accountpwd=".urlencode($myAccountPWD);
	
	$myResponse = file_get_contents($authenticateURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;

	if ($returnCode=="SUCCESS") {
	  $serveridNode = $doc->getElementsByTagName("serverid");
	  $serverid = $serveridNode->item(0)->nodeValue;	
	  return $serverid; 
	}
	else {
	  $messageKeyNode = $doc->getElementsByTagName("messageKey");
	  $messageKey = $messageKeyNode->item(0)->nodeValue;
	  return $messageKey;
	}
}

function dc_getChecksum($myAccountID,$myAccountPWD,$queryStr) {
	$checkSumURL = "http://bigbluebutton.dualcode.com/api.php?call=checksum&queryStr=".urlencode($queryStr);
	$myResponse = file_get_contents($checkSumURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;

	if ($returnCode=="SUCCESS") {
	  $checksumNode = $doc->getElementsByTagName("checksum");
	  $checksum = $checksumNode->item(0)->nodeValue;	
	  return $checksum;
	}
	else {
	  return $returnCode;
	}
}

function dc_createMeeting($myAccountID,$myAccountPWD,$myMeetingName,$myMeetingID,$myAttendeePW,$myModeratorPW,$myWelcomeMsg,$myLogoutURL) {
	$myIP= dc_authenticate($myAccountID,$myAccountPWD);
	if ($myIP != "FAILURE") {
	  $createAPI = "/bigbluebutton/api/create?";
	  $myVoiceBridge = rand(70000,79999);
	  $queryStr = "name=".$myMeetingName."&meetingID=".$myMeetingID."&attendeePW=".$myAttendeePW."&moderatorPW=".$myModeratorPW."&voiceBridge=".$myVoiceBridge."&welcome=".$myWelcomeMsg."&logoutUrl=".$myLogoutURL;
	  //echo urlencode($queryStr);
      $checksum = dc_getChecksum($myAccountID,$myAccountPWD,urlencode($queryStr));
	  $secQueryURL = "http://".$myIP.$createAPI.$queryStr."&checksum=".$checksum;
	  $myResponse = file_get_contents($secQueryURL);
	  $doc= new DOMDocument();
	  $doc->loadXML($myResponse);
	  $returnCodeNode = $doc->getElementsByTagName("returncode");
	  $returnCode = $returnCodeNode->item(0)->nodeValue;

	  if ($returnCode=="SUCCESS") {
		return $returnCode;
	  }
	  else {
	    $messageKeyNode = $doc->getElementsByTagName("messageKey");
	    $messageKey = $messageKeyNode->item(0)->nodeValue;
		return $messageKey;
	  }
	}
	else {
	  return "FAILURE";
	}
}


// Determine if the meeting is already running (e.g. has attendees in it)
function dc_isMeetingRunning($myAccountID,$myAccountPWD,$myMeetingID) {
	$secQueryURL = dc_isMeetingRunningURL($myAccountID,$myAccountPWD,$myMeetingID);
	$myResponse = file_get_contents($secQueryURL);
	$doc = new DOMDocument();
	$doc->loadXML($myResponse);
	$returnCodeNode = $doc->getElementsByTagName("returncode");
	$returnCode = $returnCodeNode->item(0)->nodeValue;
	$runningNode = $doc->getElementsByTagName("running");
	$isRunning = $runningNode->item(0)->nodeValue;
	
	return $isRunning;
}

// Create string where we check if the meeting is running
function dc_isMeetingRunningURL($myAccountID,$myAccountPWD,$myMeetingID) {
	$myIP = dc_authenticate($myAccountID,$myAccountPWD);
	if ($myIP != "FAILURE") {
	  $checkAPI = "/bigbluebutton/api/isMeetingRunning?";
	  $queryStr = "meetingID=".$myMeetingID;
	  $checksum = dc_getChecksum($myAccountID,$myAccountPWD,urlencode($queryStr));
	  $secQueryURL = "http://".$myIP.$checkAPI.$queryStr."&checksum=".$checksum;

      return $secQueryURL;
	}
	else {
	  return "FAILURE";
	}
}


// Create a URL to join the meeting
function dc_joinMeetingURL($myAccountID,$myAccountPWD,$myName,$myMeetingID,$myPassword) {
	$myIP = dc_authenticate($myAccountID,$myAccountPWD);
	if ($myIP != "FAILURE") {
  	  $joinAPI = "/bigbluebutton/api/join?";
	  $queryStr = "fullName=".$myName."&meetingID=".$myMeetingID."&password=".$myPassword;
	  $checksum = dc_getChecksum($myAccountID,$myAccountPWD,urlencode($queryStr));
	  $createStr = "http://".$myIP.$joinAPI.$queryStr."&checksum=".$checksum;

	  return $createStr;
	}
	else {
	  return "FAILURE";
	}
}
?>
