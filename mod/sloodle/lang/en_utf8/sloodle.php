<?php
/**
* This is the English language file for SLOODLE.
* It is included automatically by the Moodle framework.
* Retrieve strings using the Moodle get_string or print_string functions.
* @package sloodlelang
*/

$string['accesslevel'] = 'Access Level';
$string['accesslevel:public'] = 'Public';
$string['accesslevel:owner'] = 'Owner';
$string['accesslevel:group'] = 'Group (SL)';
$string['accesslevel:course'] = 'Course';
$string['accesslevel:site'] = 'Site';
$string['accesslevel:staff'] = 'Staff';

$string['accesslevelobject'] = 'Object Access Level';
$string['accesslevelobject:desc'] = 'This determines who may access the object in-world';
$string['accesslevelobject:use'] = 'Use object';
$string['accesslevelobject:control'] = 'Control object';

$string['accesslevelserver'] = 'Server Access Level';
$string['accesslevelserver:desc'] = 'This determines who may use the server resource';

$string['actions'] = 'Actions';
$string['activeobjects'] = 'Active objects';
$string['activeobjectlifetime'] = 'Active object lifetime (days)';
$string['activeobjectlifetime:info'] = 'The number of days before which an active object will expire if not used.';
$string['alreadyauthenticated'] = 'A Second Life avatar has already been linked and authenticated for your Moodle account.';

$string['allocated'] = 'Allocated';
$string['allentries'] = 'All avatars';
$string['allentries:info'] = 'This lists all avatars for the entire site.';
$string['allowguests'] = 'Allow guests to use the tool';
$string['allowguests:note'] = 'Does not apply if auto-registration and auto-enrolment are enabled.';
$string['allowautodeactivation'] = 'Allow auto-deactivation';

$string['authorizingfor'] = 'Authorizing for: ';
$string['authorizedfor'] = 'Authorized for: ';
$string['authorizedobjects'] = 'Authorized Objects';

$string['autoenrol'] = 'User Auto-Enrolment';
$string['autoenrol:allowforsite'] = 'Allow auto-enrolment for this site';
$string['autoenrol:allowforcourse'] = 'Allow auto-enrolment for this course';
$string['autoenrol:courseallows'] = 'This course allows auto-enrolment';
$string['autoenrol:coursedisallows'] = 'This course does not allow auto-enrolment';
$string['autoenrol:disabled'] = 'Auto-enrolment is disabled on this site';

$string['autoreg'] = 'User Auto-Registration';
$string['autoreg:allowforsite'] = 'Allow auto-registration for this site';
$string['autoreg:allowforcourse'] = 'Allow auto-registration for this course';
$string['autoreg:courseallows'] = 'This course allows auto-registration';
$string['autoreg:coursedisallows'] = 'This course does not allow auto-registration';
$string['autoreg:disabled'] = 'Auto-registration is disabled on this site';

$string['avatar'] = 'Avatar';
$string['avatarnotlinked'] = 'Your Second Life avatar is not yet linked to your Moodle account. Please use an authentication device, such as a Registration Booth or a LoginZone.';
$string['avatarname'] = 'Avatar name';
$string['avataruuid'] = 'Avatar UUID';
$string['avatarsearch'] = 'Avatar Search (within course)';

$string['backtocoursepage'] = 'Back to course page';
$string['backtosloodlesetup'] = 'Back to the SLOODLE Setup page';


$string['cfgnotecard:header'] = 'SLOODLE Configuration Notecard';
$string['cfgnotecard:generate'] = 'Generate Notecard';
$string['cfgnotecard:instructions'] = 'To configure a SLOODLE object, edit or create a notecard called \'sloodle_config\' in its inventory, and add the text from the box below.';
$string['cfgnotecard:security'] = 'For security reasons, you should make sure that the \'sloodle_config\' notecard *and* the object itself cannot be modified by the next owner.';
$string['cfgnotecard:setnote'] = 'Note: if you configure a SLOODLE Set, then it will automatically configure any other objects it creates (although you can still manually configure them if you want to).';

$string['changecourse'] = 'Change Course';
$string['choosecourse'] = 'Choose the course you want to use in Second Life.';
$string['clickchangeit'] = 'Click here to change it';
$string['clickhere'] = 'click here';
$string['clicktodeleteentry'] = 'Click here to delete this entry.';
$string['clicktoteleportanyway'] = 'Click here to teleport to the SLOODLE site in-world anyway.';
$string['clicktovisitsloodle.org'] = 'Click here to visit SLOODLE.org';
$string['compatible'] = 'Compatible';
$string['compatibility'] = 'Compatibility';
$string['compatibilitytestpassed'] = 'Compatibility test passed.';
$string['compatibilitytestfailed'] = 'Compatibility test failed.';
$string['check'] = 'Check';
$string['clicktocheckcompatibility'] = 'Click here to run a compatibility test.';
$string['clicktocheckcompatibility:nopermission'] = 'You need to be an administrator to run compatibility checks.';
$string['configerror'] = 'Configuration Error';
$string['confirmobjectauth'] = 'Do you want to authorize this object?';
$string['confirmdelete'] = 'Are you sure?';
$string['confirmdeleteuserobjects'] = 'Are you sure you want to delete all these user objects?';
$string['controlaccess'] = 'You can control access to your courses by enabling or disabling the SLOODLE Controllers';

//$string['controllerinfo'] = 'This page represents a SLOODLE Controller. These are used to control communications between Second Life and Moodle, keeping the site secure. This page is primarily for use by teachers and administrators.';

$string['controllerinfo'] = 'This course is linked to learning activities in Second Life. This page is provided to allow students to check whether the Second Life interface is currently enabled, and for instructors to configure the interface.';

$string['courseconfig'] = 'SLOODLE Course Configuration';
$string['courseconfig:info'] = 'On this page, you can configure the SLOODLE settings which affect your entire course. However, some of the settings may be disabled on your Moodle site by an administrator.<br/><br/><b>Please note:</b> auto-registration and auto-enrolment are not suitable for all Moodle installations. Please read the documentation about each one before enabling them.';
$string['coursesettings'] = 'Course Settings';
$string['createnotecard'] = 'Create notecard';

$string['databasequeryfailed'] = 'Database query failed.';
$string['delete'] = 'Delete';
$string['deletecancelled'] = 'Deletion cancelled.';
$string['deleteselected'] = 'Delete Selected';
$string['deletionfailed'] = 'Deletion failed';
$string['deletionsuccessful'] = 'Deletion successful';
$string['disabled'] = 'Disabled';
$string['day'] = 'day';
$string['days'] = 'days';
$string['directlink'] = 'Click here for a direct link to this entry.';

$string['deleteuserobjects'] = 'Delete User Objects';
$string['deleteuserobjects:help'] = 'Click this button to delete all the user objects associated with the above avatar(s)';

$string['edit'] = 'Edit';
$string['editcourse'] = 'Edit SLOODLE Course Settings';
$string['editslide'] = 'Edit Slide';
$string['enabled'] = 'Enabled';
$string['end'] = 'end';
$string['enteravatarname'] = 'Enter avatar name';
$string['error'] = 'Error';
$string['errorlinkedsloodleuser'] = 'An error occurred while trying to find SLOODLE user data linked to your Moodle account.';
$string['error:expectedsearchorcourse'] = 'Expected search string or course ID.';
$string['expired'] = 'Expired';
$string['expiresin'] = 'expires in';

$string['failedupdate'] = 'Update failed.';
$string['failedcreatesloodleuser'] = 'Failed to create a SLOODLE user account for you. Please try again.';
$string['failedaddinstance'] = 'Failed to add a new SLOODLE module instance.';
$string['failedaddsecondarytable'] = 'Failed to add the secondary table for the SLOODLE module instance.';
$string['failedcourseload'] = 'Failed to load SLOODLE course data.';
$string['failedauth-trydifferent'] = 'Failed to authorise the object. Please try a different controller.';

$string['framewidth'] = 'Frame Width';
$string['frameheight'] = 'Frame Height';

$string['getnewloginzoneallocation'] = 'Click here to get a new LoginZone allocation.';
$string['generalconfiguration'] = 'General Configuration';

$string['help:primpassword'] = 'What is the Prim Password for?';
$string['help:userediting'] = 'What is the risk?';
$string['help:autoreg'] = 'What is auto-registration?';
$string['help:autoenrol'] = 'What is auto-enrolment?';
$string['help:versionnumbers'] = 'What do these numbers mean?';
$string['help:multipleentries'] = 'Why are there multiple entries? What does it mean?';
$string['hour'] = 'hour';
$string['hours'] = 'hours';

$string['ID'] = 'ID';
$string['idletimeoutseconds'] = 'Idle timeout (seconds)';
$string['invalidid'] = 'Invalid ID';
$string['invalidcourseid'] = 'Invalid course ID';
$string['isauthorized'] = 'Is Authorized?';
$string['insufficientpermission'] = 'You do not have sufficient permission';
$string['insufficientpermissiontoviewpage'] = 'You do not have sufficient permission to view this page.';
$string['incompatible'] = 'Incompatible';
$string['incompatibleplugin'] = 'This plugin may not be compatible with your Moodle site. Please check SLOODLE documentation for more information.';

$string['lastactive'] = 'Last SLOODLE Activity';
$string['lastupdated'] = 'Last Updated';
$string['lastused'] = 'Last Used';
$string['linkedtomoodleusernum'] = 'Moodle User #';
$string['listentoobjects'] = 'Listen to object chat';

$string['loginsecuritytokenfailed'] = 'Your login security token is not valid. Please try using the Registration Booth again, and ensure you followed the correct URL to reach this page.';

$string['loginzone'] = 'SLOODLE LoginZone';
$string['loginzonedata'] = 'LoginZone Data';
$string['loginzoneposition'] = 'LoginZone Position?';
$string['loginzone:datamissing'] = 'Error! Some of the Login Zone data could not be found.';
$string['loginzone:mayneedrez'] = 'The LoginZone may need to be rezzed in-world.';
$string['loginzone:olddata'] = 'Warning! This LoginZone data has not been updated recently, so it may no longer work.';
$string['loginzone:alreadyregistered'] = 'There is already an avatar registered with your Moodle account. If you want to register another avatar, then please visit your SLOODLE profile and delete your old avatar first.';
$string['loginzone:allocationfailed'] = 'Failed to allocate a Login Position for you. Please wait a few minutes and try again.';
$string['loginzone:allocationsucceeded'] = 'Successfully allocated a LoginZone.';
$string['loginzone:expirynote'] = 'Please note that your Login Position will expire in 15 minutes. If you do not manage to use it in this time, then you will need to return here to re-activate it.';
$string['loginzone:teleport'] = 'Click here to teleport to the Login Zone.';
$string['loginzone:newallocation'] = 'Generate new LoginZone position';
$string['loginzone:needallocation'] = 'You do not have a LoginZone allocation yet. Please click the button below to get one.';


$string['minute'] = 'minute';
$string['minutes'] = 'minutes';

$string['moduletype'] = 'Module Type';
$string['moduletype:controller'] = 'SLOODLE Controller';
$string['moduletype:distributor'] = 'Distributor';
$string['moduletype:presenter'] = 'Presenter';
$string['moduletype:map'] = 'Second Life Map';
$string['moduletype:awards'] = 'Sloodle Award System'; 
$string['moduletype:tracker'] = 'tracker';

$string['moduleaction:map'] = 'Add a SLOODLE Map';

$string['modulename'] = 'SLOODLE Module';
$string['modulenameplural'] = 'SLOODLE Modules';
$string['modulenotfound'] = 'SLOODLE module not found.';
$string['modulesetup'] = 'Module Setup';
$string['moduletypemismatch'] = 'SLOODLE module type mismatch. You cannot change the SLOODLE module type after it is created.';
$string['moduletypeunknown'] = 'SLOODLE module type unknown.';
$string['moodleadminindex'] = 'Moodle administration index';
$string['moodleusernotfound'] = 'That Moodle user does not appear to exist. It may have been completely deleted from the database, or else you may have the wrong user ID.';
$string['moodleuserprofile'] = 'Moodle user profile';
$string['multipleentries'] = 'Warning: there are multiple SLOODLE entries associated with this Moodle account.';

$string['month'] = 'month';
$string['months'] = 'months';
$string['moving'] = 'moving';

$string['name'] = 'Name';
$string['needadmin'] = 'You need administrator privileges to continue.';
$string['No'] = 'No';
$string['nocompatibilityproblems'] = 'No compatibility problems detected.';
$string['noobjectconfig'] = 'No additional configuration options for this object.';
$string['now'] = 'now';
$string['nodistributorinterface'] = 'No Distributor Interface';
$string['noguestaccess'] = 'Sorry, you cannot use guest login here.';
$string['nosloodleusers'] = 'No users registered with SLOODLE';
$string['nodeletepermission'] = 'You do not have permission to delete this entry.';
$string['noentries'] = 'No entries found.';
$string['noscriptwarning'] = 'Warning: it looks like your browser does not support JavaScript so this feature may not work properly.';
$string['nouserdata'] = 'There is no user data to display.';
$string['nowenrol'] = 'Please continue to enrol in this course.';
$string['notenrolled'] = 'User not enrolled in this course.';
$string['numsloodleentries'] = '# SLOODLE entries';
$string['numsettingsstored'] = 'Number of settings stored:';
$string['numobjects'] = 'Number of objects';
$string['numdeleted'] = 'Number deleted';
$string['numprims'] = 'Prim Count: $a';

$string['nochatrooms'] = 'There are no chatrooms available in this course.';
$string['nochoices'] = 'There are no choices available in this course.';
$string['noquizzes'] = 'There are no quizzes available in this course.';
$string['noglossaries'] = 'There are no glossaries available in this course.';
$string['nodistributors'] = 'There are no distributors available in this course.';
$string['nosloodleassignments'] = 'There are no SLOODLE-compatible assignments available in this course.';
$string['nopresenters'] = 'There are no SLOODLE Presenters available in this course.';

$string['object:accesschecker'] = 'Access Checker';
$string['object:accesscheckerdoor'] = 'Access Checker Door';
$string['object:chat'] = 'WebIntercom';
$string['object:choice'] = 'Choice';
$string['object:distributor'] = 'Vending Machine';
$string['object:enrolbooth'] = 'Enrolment Booth';
$string['object:glossary'] = 'MetaGloss';
$string['object:loginzone'] = 'LoginZone';
$string['object:primdrop'] = 'PrimDrop';
$string['object:pwreset'] = 'Password Reset';
$string['object:quiz'] = 'Quiz Chair';
$string['object:quiz_pile_on'] = 'Quiz Pile-On';
$string['object:regbooth'] = 'Registration Booth';
$string['object:regenrolbooth'] = 'Registration/Enrolment Booth';
$string['object:set'] = 'SLOODLE Set';
$string['object:demo'] = 'SLOODLE Demo Object';
$string['object:awards'] = 'Sloodle Award System';
$string['object:presenter'] = 'Presenter';
$string['object:mapmarker'] = 'Map Marker';
$string['object:picturegloss'] = 'Picture Gloss';


$string['Object'] = 'Object';
$string['objectdetails'] = 'Object Details';
$string['objectnotinstalled'] = 'Object not installed';
$string['objectconfig:header'] = 'SLOODLE Object Configuration';
$string['objectconfig:body'] = 'You can choose to configure some SLOODLE objects with a notecard instead of using the common web-based authorisation. It is less secure, as it involves the use of a single prim password for all objects, but it makes it quicker and easier to rez pre-configured objects from your inventory.';
$string['objectconfig:select'] = 'Select which object you would like to create a configuration notecard for from the list below. If multiple versions are available, then they are shown in the brackets -- only use the older versions if the main version does not work.';
$string['objectconfig:noobjects'] = 'There are no object configurations available.';
$string['objectconfig:noprimpassword'] = 'ERROR: The Prim Password has been disabled for this Controller. Please specify a Prim Password if you would like to use notecard configuration.';
$string['objectconfig:backtoform'] = 'Go back to the configuration form.';

$string['objectauth'] = 'SLOODLE Object Authorization';
$string['objectauthalready'] = 'This object has already been authorized. If you want to re-authorize it, then please delete its authorization entry from your SLOODLE Controller.';
$string['objectauthcancelled'] = 'You have cancelled the object authorization.';
$string['objectauthfailed'] = 'Object authorization has failed.';
$string['objectauthnocontrollers'] = 'There are no SLOODLE Controllers on the site. Please create one on a course in order to authorise objects.';
$string['objectauthnopermission'] = 'You do not have the permission to authorise any objects. You may need to create a SLOODLE Controller on your course(s).';
$string['objectauthnotfound'] = 'Object not found for authorization.';
$string['objectauthsuccessful'] = 'Object authorization has been successful.';
$string['objectconfiguration'] = 'Object Configuration';
$string['objectname'] = 'Object Name';
$string['objectuuid'] = 'Object UUID';
$string['objecttype'] = 'Object Type';

$string['of'] = 'of';
$string['or'] = 'or';

$string['postedfromsl'] = 'Posted from Second Life';
$string['pendingavatarnotfound'] = 'Could not locate a pending entry for your avatar. Perhaps you are already registered?';
$string['pendingallocations'] = 'Pending Allocations';
$string['pendingavatars'] = 'Pending Avatars';
//$string['pendingavatars:info'] = '';

$string['pluginfailedtoload'] = 'Failed to load plugin.';
$string['playsounds'] = 'Play sounds?';
$string['position'] = 'Position';

$string['presenter:backtoimporters']  = 'Back to importers list';
$string['presenter:viewpresentation']  = 'View the presentation';
$string['presenter:edit'] = 'Edit the presentation';
$string['presenter:empty'] = 'This presentation does not have any slides in it yet.';
$string['presenter:clickedit'] = 'To edit this presentation, click the Edit tab above.';
$string['presenter:clickaddslide'] = 'To add slides to this presentation, click the Add Slide tab above.';
$string['presenter:viewanddelete']  = 'View and delete entries';
$string['presenter:add'] = 'Add slides';
$string['presenter:addfiles'] = 'Add the above files to the presentation'; 
$string['presenter:bulkupload'] = 'Upload Many';  
$string['presenter:addatend'] = 'Add a new slide at the end of this presentation';
$string['presenter:addbefore'] = 'Add a new slide before this slide';
$string['presenter:moveslide'] = 'Move this slide';
$string['presenter:editslide'] = 'Edit this slide';
$string['presenter:viewslide'] = 'View this slide';
$string['presenter:deleteslide'] = 'Delete this slide from the presentation';
$string['presenter:movingslide'] = 'Currently moving slide \"$a\".';

$string['presenter:confirmdelete'] = 'Are you sure you want to delete slide \"$a\" from this presentation?';
$string['presenter:confirmdeletemultiple'] = 'Are you sure you want to delete $a slide(s) from the presentation?';

$string['presenter:deletedslide'] = 'Deleted slide \"$a\" from the presentation.';
$string['presenter:deletedslides'] = 'Deleted $a slide(s) from the presentation.';
$string['presenter:noslidesfordeletion'] = 'No slides selected for deletion. Please select one or more slides by ticking the checkboxes on the left, and then click \"Delete Selected\" again.';

$string['presenter:sloodleinsert'] = 'Insert at position:';                          
$string['presenter:type:image'] = 'Image';
$string['presenter:type:video'] = 'Video';
$string['presenter:type:web'] = 'Web';
$string['presenter:uploadInstructions'] = "To Bulk Upload, first click the button below, then to select multiple files, hold down the control or shift key while selecting files";
$string['presenter:importslides'] = 'Import Slides';
$string['presenter:importslidescaption'] = 'Import slides from a file or other source';
$string['presenter:selectimporter'] = 'Select the importer you would like to use';
$string['presenter:importfile'] = 'Import File';
$string['presenter:importfrommycomputer'] = 'Import a file from my computer';
$string['presenter:importfromweb'] = 'Import a file from the Internet';
$string['presenter:importposition'] = 'Import Position';
$string['presenter:importpositioncaption'] = 'Select where you would like to import the slides to.';
$string['presenter:importname'] = 'Import Name';
$string['presenter:importnamecaption'] = '(Optional) Enter a name for the material you are importing.';
$string['presenter:importfailed'] = 'Import failed. This plugin may not be compatible with your server.';
$string['presenter:importneedimagick'] = 'You need ImageMagick installed from http://www.imagemagick.org/ and (optionally) MagickWand to use this plugin. GhostScript is also required.';
$string['presenter:importsuccessful'] = 'Import successful. $a slide(s) have been added to your presentation.';

$string['presenter:magickwandnotinstalled'] = 'The MagickWand extension could not be loaded.';
$string['presenter:usingmagickwand'] = 'Using MagickWand extension.';
$string['presenter:usingexecutable'] = 'Using ImageMagick \'convert\' program.';
$string['presenter:convertnotfound'] = 'Failed to locate ImageMagick \'convert\' program.';
$string['presenter:convertdisabled'] = 'Use of the ImageMagick \'convert\' program has been disabled.';

$string['primpass'] = 'Prim Password';
$string['primpass:invalidtype'] = 'Prim Password was an invalid type. Should be a string.';
$string['primpass:tooshort'] = 'Prim Password should be at least 5 digits long (or leave field blank to disable it).';
$string['primpass:toolong'] = 'Prim Password should be at most 9 digits long (or leave field blank to disable it).';
$string['primpass:numonly'] = 'Prim Password should only contain numeric digits (0 to 9).';
$string['primpass:error'] = 'Prim Password Error';
$string['primpass:updated'] = 'Prim Password updated';
$string['primpass:leadingzero'] = 'Prim Password should not start with a 0.';

$string['randomquestionorder'] = 'Randomize question order?';
$string['releasenum'] = 'Module release number';
$string['region'] = 'Region';
$string['refreshtimeseconds'] = 'Refresh time (seconds)';
$string['repeatquiz'] = 'Automatically repeat the quiz?';
$string['relativeresults'] = 'Show relative results?';
$string['runningcompatibilitycheck'] = 'Running compatibility check...';

$string['save'] = 'Save';
$string['second'] = 'second';
$string['seconds'] = 'seconds';
$string['secondarytablenotfound'] = 'Secondary SLOODLE module table not found. Module instance may need to be created again.';

$string['searchaliases'] = 'Search Aliases';
$string['searchdefinitions'] = 'Search Definitions';
$string['selectall'] = 'Select All';
$string['selectchatroom'] = 'Select Chatroom';
$string['selectchoice'] = 'Select Choice';
$string['selectglossary'] = 'Select Glossary';
$string['selectdistributor'] = 'Select Distributor';
$string['selectassignment'] = 'Select Assignment';
$string['selectquiz'] = 'Select Quiz';
$string['selectobject'] = 'Select Object';
$string['selectuser'] = 'Select User';
$string['selectcontroller'] = 'Select Controller';
$string['selectpresenter'] = 'Select Presenter';

$string['sendobject'] = 'Send Object';
$string['setting'] = 'Settings';
$string['showavatarsonly'] = 'Only show accounts with avatars';
$string['showpartialmatches'] = 'Show Partial Matches';
$string['size'] = 'Size';

$string['sloodle'] = 'SLOODLE';
$string['sloodlenotinstalled'] = 'SLOODLE does not appear to be installed yet. Please use visit the Moodle administration index to finish SLOODLE installation:';
$string['sloodlesetup'] = 'SLOODLE Setup';
$string['sloodleversion'] = 'SLOODLE Version';

$string['sloodle:staff'] = 'SLOODLE Staff member';
$string['sloodle:objectauth'] = 'Authorise objects for SLOODLE access';
$string['sloodle:userobjectauth'] = 'Authorise user objects for self';
$string['sloodle:uselayouts'] = 'Use classroom layout profiles';
$string['sloodle:editlayouts'] = 'Edit/delete classroom layout profiles';
$string['sloodle:registeravatar'] = 'Register own avatar';
$string['sloodle:distributeself'] = 'Distribute objects to own avatar';
$string['sloodle:distributeothers'] = 'Distribute objects to other avatars';

$string['sloodleobjectdistributor'] = 'SLOODLE Object Distributor';
$string['sloodleobjectdistributor:nochannel'] = 'Distribution channel not available - Object not rezzed in-world?';
$string['sloodleobjectdistributor:reset'] = 'Check this to clear the cached Distributor data, including channel UUID and object names.';
$string['sloodleobjectdistributor:unknowncommand'] = 'Distributor command not recognised.';
$string['sloodleobjectdistributor:usernotfound'] = 'Unable to find requested user.';
$string['sloodleobjectdistributor:successful'] = 'Object distribution successful.';
$string['sloodleobjectdistributor:failed'] = 'Object distribution failed.';
$string['sloodleobjectdistributor:noobjects'] = 'No objects are currently available for distribution. The SLOODLE Object Distributor may need to be given contents?';
$string['sloodleobjectdistributor:sendtomyavatar'] = 'Send to me';
$string['sloodleobjectdistributor:sendtocustomavatar'] = 'Send to custom avatar';
$string['sloodleobjectdistributor:sendtoanotheravatar'] = 'Send to another avatar';

$string['sloodleuserediting:allowteachers'] = 'Allow teachers to edit SLOODLE user data';
$string['sloodleuserediting'] = 'Avatar Editing';//'SLOODLE User Editing';
$string['sloodleuserprofile'] = 'Avatar';//'SLOODLE User Profile';
$string['sloodleuserprofiles'] = 'Avatars';//'SLOODLE User Profiles';
$string['specialpages'] = 'Special Pages';
  
$string['status'] = 'Status';
$string['storedlayouts'] = 'Stored Layouts';
$string['submit'] = 'Submit';

$string['timeago'] = '$a ago'; // $a = period of time, e.g. "3 weeks"
$string['type'] = 'Type';
$string['trydirectlink'] = 'If you cannot see the above entry, try this <a href=\"$a\">direct link</a> instead.';

$string['unknown'] = 'unknown';
$string['unknownuser'] = 'unknown user';

$string['user'] = 'User';
$string['userlinkfailed'] = 'There was an error while trying to link your avatar to your Moodle account.';
$string['userlinksuccessful'] = 'Your avatar was successfully linked to your Moodle account. All SLOODLE objects linked to this site should now recognised you automatically.';
$string['usersearch'] = 'User search (within course)';
$string['userobjects'] = 'User Objects';
$string['userobjectlifetime'] = 'User object lifetime (days)';
$string['userobjectlifetime:info'] = 'The number of days before which a user-centric object (such as the Toolbar) will expire if not used.';
$string['userobjectauth'] = 'SLOODLE User Object Authorization';
$string['usedialogs'] = 'Use dialogs (instead of chat)?';
$string['url']  = 'URL';
$string['unknowntype'] = 'Unknown type';

$string['upload:selectfile'] = 'Select file to upload';
$string['upload:file'] = 'Upload File';
$string['upload:maxsize'] = 'Estimated maximum upload size: $a';
$string['upload:emptyfile'] = 'Uploaded file is empty. (This may mean the file was too big to upload.)';

$string['uuid'] = 'UUID';

$string['viewpending'] = 'View pending avatars';
$string['viewall'] = 'View all avatars';
$string['viewmyavatar'] = 'View my avatar details';
$string['viewprev'] = 'View previous entry';
$string['viewnext'] = 'View next entry';
$string['jumpback'] = 'Jump back';
$string['jumpforward'] = 'Jump Forward';
$string['view'] = 'View';

$string['welcometosloodle'] = 'Welcome to SLOODLE';
$string['week'] = 'week';
$string['weeks'] = 'weeks';

$string['xmlrpc:unexpectedresponse'] = 'Not getting the expected XMLRPC response. Is Second Life broken again?';
$string['xmlrpc:error'] = 'XMLRPC Error';
$string['xmlrpc:channel'] = 'XMLRPC Channel';

$string['year'] = 'year';
$string['years'] = 'years';
$string['Yes'] = 'Yes';
$string ['awards:viewgradesassociated']= 'Click here to view the grades associated with this Sloodle Award';                      
$string ['awards:stipendisfor']= 'Total Allocations:';
$string ['awards:nostudents']= 'No Students Registered';
$string ['awards:purpose']= 'It\'s purpose is: ';
$string ['awards:nostipendsgiven'] = 'No stipends have been taken by anyone yet!';   
$string ['awards:description'] = 'Description';
$string ['awards:transactions'] = 'Transactions & Allocated Stipends';   
$string ['awards:noavatar'] = 'No Avatar Registered'; 
$string ['awards:avname'] = 'Avatar Name'; 
$string ['awards:alloted'] = 'Allotted'; 
$string ['awards:withdrawn'] = 'Withdrawn'; 
$string ['awards:selectaward'] ="Please select the Sloodle Award System this object connects to";
$string ['awards:nostpendgivers'] = 'No stipend givers have been added to your moodle course.  Please add a stipend giver activity first.';
$string ['awards:date'] = 'Date';
$string ['awards:teachers'] = 'Teachers';
$string ['awards:administrators'] = 'Administrators';

$string ['awards:students'] = 'Students';
$string ['awards:noavatarbutalreadywithdrew'] = 'Avatar no longer listed. But debit transaction exists';
$string ['awards:create'] = 'Add Stipends now!'; 
$string ['awards:setup'] = 'Click here to create allocate your stipend!';    
$string['awards:admins']='Administrators';
$string['awards:students']='Students';
$string['awards:teachers']='Teachers';
$string['awards:balance']='Balance';
$string['awards:credits']='Credits';
$string['awards:debits']='Debits';
$string['awards:debits']='Debits';
$string['awards:avatars']='Avatars';
$string['awards:username']='First/Last name';
$string['awards:update']='Update';  
$string['awards:cantupdate']='Error Can\'t update transaction record';
$string['awards:successfullupdate']='Updated the following users: ';
$string['awards:typeofcurrency']='Type of Currency';
$string['awards:amount']='Amount';
$string['awards:totalallocations']='Total Allocated';
$string['awards:totaldebits']='Total Debits';
$string['awards:startingbalance']='Default Amount';
$string['awards:alreadywd']=' has already withdrawn the amount of: ';  
$string['awards:totalipoints']='Total iPoints';
$string['awards:totalawarded']='Total Awarded';   
$string['awards:iPoints']='iPoints';    
$string['awards:goback']='Back to full user list';    
$string['awards:usertransactions']='Transactions for: ';    
$string['awards:details']='Transaction Details';    
$string['awards:fullname']='Full Name';    
$string['awards:course']='Course';    
$string['awards:maxpoints']='Max Points';    
$string['awards:scoreboard']='iPoint Scoreboard';                   
$string['awards:awardsAccountDetails']='Awards Account Details';                   
$string['awards:awardsBalanceChart']='Award Statistics';                   
$string['awards:totalusers']='Total Users:';                   
$string['awards:totalcredits']='Total Credits:';                   
$string['awards:totaldebits']='Total Debits:'; 
$string['awards:totalbalances']='Total Balances:'; 
$string['awards:help:icurrency']='If you select Lindens, then your students can withdraw money using a stipend giver. On the otherhand, iPoints are non monetary points you can award your students!'; 
$string['help:maxpoints']='-->If you choose 1000 as the maximum points then students must accumulate 1000 points or higher in Second Life to acheive 100 percent grade  If however they only get 300 points in Second Life and the maximum points are set to 1000, then the grade achieved would be 300/1000  (30 percent)';                                    
$string['awards:balanceUpdate']='Update';                   
$string['awards:noneregistered']='No avatars registered in SLOODLE';  
$string['awards:alreadywd2']=' You can not choose a lower allotment unless that user\'s avatar pays the stipend giver to credit their account'; 
$string ['secondlifetracker:noavatar'] = 'No avatar registered yet'; 
$string ['secondlifetracker:activity'] = 'ACTIVITY IN SECOND LIFE';

$string ['secondlifetracker:noavatar'] = 'No avatar registered yet';
$string ['secondlifetracker:nousers'] = 'No users registered yet';
$string ['secondlifetasks'] = 'Assignment';
$string ['secondlifeobjdesc'] = 'Task Description';
$string ['secondlifelevelcompl'] = 'Level of Completion';
$string ['secondlifetracker:completed'] = 'Completed';
$string ['secondlifetracker:notcompleted'] = 'Not Completed';
$string ['secondlifetracker:time'] = 'Time';
$string ['secondlifetracker:selecttracker'] = 'Select Tracker';
$string ['secondlifetracker:notrackers'] = 'No trackers in your course';

$string['layoutpage'] = 'Layouts';
$string['layoutmanager:nopermission'] = 'You do not have permission to edit layouts';
$string['layoutmanager:namealreadyexists'] = 'You do not have permission to edit layouts';
$string['layoutmanager:savefailed'] = 'Save failed';
$string['layoutmanager:Layouts'] = 'Layouts';
$string['layoutmanager:layoutaddpageexplanation'] = 'This page shows the Sloodle tools that will work for this course. Check and uncheck them to include them in your layout. <br /><br />You can rez the layout in-world using a Layout Rezzer object, then move the objects around to the places where you want them to be. ';
$string['layoutmanager:layoutname'] = 'Layout Name';

$string['layoutmanager:object'] = 'Object';
$string['layoutmanager:module'] = 'Module';
$string['layoutmanager:x'] = 'X';
$string['layoutmanager:y'] = 'Y';
$string['layoutmanager:z'] = 'Z';
$string['layoutmanager:savelayout'] = 'Save Layout';
$string['layoutmanager:currentobjects'] = 'Objects Already In Your Layout';
$string['layoutmanager:addobjects'] = 'Add Objects To Your Layout';
$string['awards:othersettings'] = 'Other Settings';
$string['awards:assignment'] = 'Attach earned points to an assignment';
$string['awards:refresh'] = 'This is the time in seconds that the awards will refresh the display in Second Life<br>';
$string['awards:selectassignment'] = 'Assignment';

