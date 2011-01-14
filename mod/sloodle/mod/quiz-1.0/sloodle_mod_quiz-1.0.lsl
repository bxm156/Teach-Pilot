       // Sloodle quiz chair
        // Allows SL users to take Moodle quizzes in-world
        // Part of the Sloodle project (www.sloodle.org)
        //
        // Copyright (c) 2006-9 Sloodle (various contributors)
        // Released under the GNU GPL
        //
        // Contributors:
        //  Edmund Edgar
        //  Peter R. Bloomfield
        //
        
        // Memory-saving hacks!
        key null_key = NULL_KEY;

        integer SLOODLE_CHANNEL_ERROR_TRANSLATION_REQUEST=-1828374651;
        integer doRepeat = 0; // whether we should run through the questions again when we're done
        integer doDialog = 1; // whether we should ask the questions using dialog rather than chat
        integer doPlaySound = 1; // whether we should play sound
        integer doRandomize = 1; // whether we should ask the questions in random order
        
        string sloodleserverroot = "";
        integer sloodlecontrollerid = 0;
        string sloodlepwd = "";
        integer sloodlemoduleid = 0;
        integer sloodleobjectaccessleveluse = 0; // Who can use this object?
        integer sloodleobjectaccesslevelctrl = 0; // Who can control this object?
        integer sloodleserveraccesslevel = 0; // Who can use the server resource? (Value passed straight back to Moodle)
        
        integer isconfigured = FALSE; // Do we have all the configuration data we need?
        integer eof = FALSE; // Have we reached the end of the configuration data?
        
        integer SLOODLE_CHANNEL_AVATAR_DIALOG = 1001;
        integer SLOODLE_CHANNEL_OBJECT_DIALOG = -3857343; // an arbitrary channel the sloodle scripts will use to talk to each other. Doesn't atter what it is, as long as the same thing is set in the sloodle_slave script. 
        integer SLOODLE_CHANNEL_AVATAR_IGNORE = -1639279999;
        
        integer SLOODLE_OBJECT_ACCESS_LEVEL_PUBLIC = 0;
        integer SLOODLE_OBJECT_ACCESS_LEVEL_OWNER = 1;
        integer SLOODLE_OBJECT_ACCESS_LEVEL_GROUP = 2;
        
        string SLOODLE_OBJECT_TYPE = "quiz-1.0";
        string SLOODLE_EOF = "sloodleeof";
        
        string sloodle_quiz_url = "/mod/sloodle/mod/quiz-1.0/linker.php";
        
        key httpquizquery = null_key;
        
        float request_timeout = 20.0;
        
        // ID and name of the current quiz
        integer quizid = -1;
        string quizname = "";
        // This stores the list of question ID's (global ID's)
        list question_ids = [];
        integer num_questions = 0;
        // Identifies the active question number (index into question_ids list)
        // (Next question will always be this value +1)
        integer active_question = -1;
        
        // Identifies which question we are currently requesting (index into question_ids list)
        integer requesting_question = -1;
        
        // Number of the loaded 'next' question (corresponds to 'active_question')
        integer qloaded_next = -1;
        
        // Text and type of the current and next question
        string qtext_current = "";
        string qtype_current = "";
        string qtext_next = "";
        string qtype_next = "";
        // Lists of option information for the current question
        list opids_current = []; // IDs
        list optext_current = []; // Texts
        list opgrade_current = []; // Grades
        list opfeedback_current = []; // Feedback if this option is selected
        // Lists of option information for the next question
        list opids_next = []; // IDs
        list optext_next = []; // Texts
        list opgrade_next = []; // Grades
        list opfeedback_next = []; // Feedback if this option is selected
        
        // Avatar currently using this cahir
        key sitter = null_key;
        // The lowest point of the char
        float lowestvector = 0.0; 
        
        // Stores the number of questions the user got correct on a given attempt
        integer num_correct = 0;
        
        
        ///// FUNCTIONS /////
        /******************************************************************************************************************************
        * sloodle_error_code - 
        * Author: Paul Preibisch
        * Description - This function sends a linked message on the SLOODLE_CHANNEL_ERROR_TRANSLATION_REQUEST channel
        * The error_messages script hears this, translates the status code and sends an instant message to the avuuid
        * Params: method - SLOODLE_TRANSLATE_SAY, SLOODLE_TRANSLATE_IM etc
        * Params:  avuuid - this is the avatar UUID to that an instant message with the translated error code will be sent to
        * Params: status code - the status code of the error as on our wiki: http://slisweb.sjsu.edu/sl/index.php/Sloodle_status_codes
        *******************************************************************************************************************************/
        sloodle_error_code(string method, key avuuid,integer statuscode){
                    llMessageLinked(LINK_SET, SLOODLE_CHANNEL_ERROR_TRANSLATION_REQUEST, method+"|"+(string)avuuid+"|"+(string)statuscode, NULL_KEY);
        }        sloodle_debug(string msg)
        {
            llMessageLinked(LINK_THIS, DEBUG_CHANNEL, msg, null_key);
        }        

        // Configure by receiving a linked message from another script in the object
        // Returns TRUE if the object has all the data it needs
        integer sloodle_handle_command(string str) 
        {
            list bits = llParseString2List(str,["|"],[]);
            integer numbits = llGetListLength(bits);
            string name = llList2String(bits,0);
            string value1 = "";
            string value2 = "";
            
            if (numbits > 1) value1 = llList2String(bits,1);
            if (numbits > 2) value2 = llList2String(bits,2);
            
            if (name == "set:sloodleserverroot") sloodleserverroot = value1;
            else if (name == "set:sloodlepwd") {
                // The password may be a single prim password, or a UUID and a password
                if (value2 != "") sloodlepwd = value1 + "|" + value2;
                else sloodlepwd = value1;
                
            } else if (name == "set:sloodlecontrollerid") sloodlecontrollerid = (integer)value1;
            else if (name == "set:sloodlemoduleid") sloodlemoduleid = (integer)value1;
            else if (name == "set:sloodleobjectaccessleveluse") sloodleobjectaccessleveluse = (integer)value1;
            else if (name == "set:sloodleserveraccesslevel") sloodleserveraccesslevel = (integer)value1;
            else if (name == "set:sloodlerepeat") doRepeat = (integer)value1;
            else if (name == "set:sloodlerandomize") doRandomize = (integer)value1;
            else if (name == "set:sloodledialog") doDialog = (integer)value1;
            else if (name == "set:sloodleplaysound") doPlaySound = (integer)value1;
            else if (name == SLOODLE_EOF) eof = TRUE;
            
            return (sloodleserverroot != "" && sloodlepwd != "" && sloodlecontrollerid > 0 && sloodlemoduleid > 0);
        }
        
        // Checks if the given agent is permitted to user this object
        // Returns TRUE if so, or FALSE if not
        integer sloodle_check_access_use(key id)
        {
            // Check the access mode
            if (sloodleobjectaccessleveluse == SLOODLE_OBJECT_ACCESS_LEVEL_GROUP) {
                return llSameGroup(id);
            } else if (sloodleobjectaccessleveluse == SLOODLE_OBJECT_ACCESS_LEVEL_PUBLIC) {
                return TRUE;
            }
            
            // Assume it's owner mode
            return (id == llGetOwner());
        }
        
        // Query the server for the identified question (request by global question ID)
        key request_question( integer qid )
        {
            // Request the identified question from Moodle
            string body = "sloodlecontrollerid=" + (string)sloodlecontrollerid;
            body += "&sloodlepwd=" + sloodlepwd;
            body += "&sloodlemoduleid=" + (string)sloodlemoduleid;
            body += "&sloodleuuid=" + (string)sitter;
            body += "&sloodleavname=" + llEscapeURL(llKey2Name(sitter));
            body += "&sloodleserveraccesslevel=" + (string)sloodleserveraccesslevel;
            body += "&ltq=" + (string)qid;
            
            key newhttp = llHTTPRequest(sloodleserverroot + sloodle_quiz_url, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], body);
            
            llSetTimerEvent(0.0);
            llSetTimerEvent(request_timeout);
            
            return newhttp;
        }
        
        // Notify the server of a response
        notify_server(string qtype, integer questioncode, string responsecode)
        {
            string body = "sloodlecontrollerid=" + (string)sloodlecontrollerid;
            body += "&sloodlepwd=" + sloodlepwd;
            body += "&sloodlemoduleid=" + (string)sloodlemoduleid;
            body += "&sloodleuuid=" + (string)sitter;
            body += "&sloodleavname=" + llEscapeURL(llKey2Name(sitter));
            body += "&sloodleserveraccesslevel=" + (string)sloodleserveraccesslevel;
            body += "&resp" + (string)questioncode + "_=" + responsecode;
            body += "&resp" + (string)questioncode + "_submit=1";
            body += "&questionids=" + (string)questioncode;
            body += "&action=notify";
            
            llHTTPRequest(sloodleserverroot + sloodle_quiz_url, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], body);
        }
        
        
        // Ask the current question
        ask_current_question() 
        {      
            // Are we using dialogs?
            if (doDialog == 1) {
                
                // We want to create a dialog with the option texts embedded into the main text,
                //  and numbers on the buttons
                integer qi;
                list qdialogoptions = [];
                
                string qdialogtext = qtext_current + "\n";
                // Go through each option
                integer num_options = llGetListLength(optext_current);
                
                if ((qtype_current == "numerical")|| (qtype_current == "shortanswer")) {
                   // Ask the question via IM
                   llInstantMessage(sitter, qtext_current);
            } else {
            for (qi = 1; qi <= num_options; qi++) {
                // Append this option to the main dialog (remebering buttons are 1-based, but lists 0-based)
                qdialogtext += (string)qi + ": " + llList2String(optext_current,qi-1) + "\n";
                // Add a button for this option
                qdialogoptions = qdialogoptions + [(string)qi];
            }
            // Present the dialog to the user
            llDialog(sitter, qdialogtext, qdialogoptions, SLOODLE_CHANNEL_AVATAR_DIALOG);
            }
            } else {
                
                // Ask the question via IM
                llInstantMessage(sitter, qtext_current);
                // Offer the options via IM
                integer x = 0;
                integer num_options = llGetListLength(optext_current);
                for (x = 0; x < num_options; x++) {
                    llInstantMessage(sitter, (string)(x + 1) + ". " + llList2String(optext_current, x));
                }        
            }
        }
        
        // Play a sound as audio feedback
        play_sound(float multiplier)
        {
            // Do nothing if sound is disabled
            if (doPlaySound == 0) return;
            string sound_file;
            float volume;
        
            // Determine what our sound file and volume should be
            if (multiplier > 0) {
                sound_file = "Correct";
            } else {
                sound_file = "Incorrect";
                multiplier = multiplier * -1;
            }
            // Cap our volume
            if (multiplier > 1) {
                volume = 1.0;
            } else {
                volume = (float)multiplier;
            }    
            
            // Make sure the sound file exists, and then play it
            if (llGetInventoryType(sound_file) == INVENTORY_SOUND) llPlaySound(sound_file,multiplier);
        }
        
        // Move the chair up or down as visual feedback
        move_vertical(float multiplier)
        {
            vector position = llGetPos();
            position.z += 0.5 * multiplier;
            llSetPos(position);
        }
        
        // Move the Quiz Chair back to the starting position
        move_to_start()
        {
            vector position = llGetPos();
            position.z = lowestvector;
            llSetPos(position);
        }
        
        // Report completion to the user
        finish_quiz() 
        {
            sloodle_translation_request(SLOODLE_TRANSLATE_IM, [0], "complete", [llKey2Name(sitter), (string)num_correct + "/" + (string)num_questions], sitter, "quiz");
            //move_to_start(); // Taking this out here leaves the quiz chair at its final position until the user stands up.
            
            // Clear the big nasty chunks of data
            optext_current = [];
            opfeedback_current = [];
            optext_next = [];
            opfeedback_next = [];  
            
            // Notify the server that the attempt was finished
            string body = "sloodlecontrollerid=" + (string)sloodlecontrollerid;
            body += "&sloodlepwd=" + sloodlepwd;
            body += "&sloodlemoduleid=" + (string)sloodlemoduleid;
            body += "&sloodleuuid=" + (string)sitter;
            body += "&sloodleavname=" + llEscapeURL(llKey2Name(sitter));
            body += "&sloodleserveraccesslevel=" + (string)sloodleserveraccesslevel;
            body += "&finishattempt=1";
            
            llHTTPRequest(sloodleserverroot + sloodle_quiz_url, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], body);
        }
        
        // Reinitialise (e.g. after one person has finished an attempt)
        reinitialise()
        {
            sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "resetting", [], null_key, "");
            llMessageLinked(LINK_THIS, SLOODLE_CHANNEL_OBJECT_DIALOG, "do:requestconfig", null_key);
            llResetScript();
        }
        
        
        ///// TRANSLATION /////
        
        // Link message channels
        integer SLOODLE_CHANNEL_TRANSLATION_REQUEST = -1928374651;
        
        // Translation output methods
        string SLOODLE_TRANSLATE_WHISPER = "whisper";               // 1 output parameter: chat channel number
        string SLOODLE_TRANSLATE_SAY = "say";               // 1 output parameter: chat channel number
        string SLOODLE_TRANSLATE_OWNER_SAY = "ownersay";    // No output parameters
        string SLOODLE_TRANSLATE_DIALOG = "dialog";         // Recipient avatar should be identified in link message keyval. At least 2 output parameters: first the channel number for the dialog, and then 1 to 12 button label strings.
        string SLOODLE_TRANSLATE_LOAD_URL = "loadurl";      // Recipient avatar should be identified in link message keyval. 1 output parameter giving URL to load.
        string SLOODLE_TRANSLATE_IM = "instantmessage";     // Recipient avatar should be identified in link message keyval. No output parameters.
        
        // Send a translation request link message
        
        sloodle_translation_request(string output_method, list output_params, string string_name, list string_params, key keyval, string batch)
        {
            
            llMessageLinked(LINK_THIS, SLOODLE_CHANNEL_TRANSLATION_REQUEST, output_method + "|" + llList2CSV(output_params) + "|" + string_name + "|" + llList2CSV(string_params) + "|" + batch, keyval);
        }
        
        ///// ----------- /////
        
        
        ///// STATES /////
        
        // Waiting on initialisation
        default
        {
            state_entry()
            {
                // Starting again with a new configuration
                llSetText("", <0.0,0.0,0.0>, 0.0);
                isconfigured = FALSE;
                eof = FALSE;
                // Reset our data
                sloodleserverroot = "";
                sloodlepwd = "";
                sloodlecontrollerid = 0;
                sloodlemoduleid = 0;
                sloodleobjectaccessleveluse = 0;
                sloodleserveraccesslevel = 0;
                doRepeat = 1;
                doDialog = 1;
                doPlaySound = 1;
                doRandomize = 1;
            }
            
            link_message( integer sender_num, integer num, string str, key id)
            {
                // Check the channel
                if (num == SLOODLE_CHANNEL_OBJECT_DIALOG) {
                    // Split the message into lines
                    list lines = llParseString2List(str, ["\n"], []);
                    integer numlines = llGetListLength(lines);
                    integer i = 0;
                    for (; i < numlines; i++) {
                        isconfigured = sloodle_handle_command(llList2String(lines, i));
                    }
                    
                    // If we've got all our data AND reached the end of the configuration data, then move on
                    if (eof == TRUE) {
                        if (isconfigured == TRUE) {
                            sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "configurationreceived", [], null_key, "");
                            state ready;
                        } else {
                            // Go all configuration but, it's not complete... request reconfiguration
                            sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "configdatamissing", [], null_key, "");
                            llMessageLinked(LINK_THIS, SLOODLE_CHANNEL_OBJECT_DIALOG, "do:reconfigure", null_key);
                            eof = FALSE;
                        }
                    }
                }
            }
            
            touch_start(integer num_detected)
            {
                // Attempt to request a reconfiguration
                if (llDetectedKey(0) == llGetOwner()) {
                    llMessageLinked(LINK_THIS, SLOODLE_CHANNEL_OBJECT_DIALOG, "do:requestconfig", null_key);
                }
            }
        }
        
        
        // Ready state - waiting for a user to climb aboard!
        state ready
        {
            state_entry()
            {
                // This is now handled by a separate poseball
                // llSitTarget(<0,0,.5>, ZERO_ROTATION);
            }
            
            changed(integer change)
            {
                // Something changed - was it a link?
                if (change & CHANGED_LINK)
                {
                    llSleep(0.5); // Allegedly llUnSit works better with this delay
                    
                    // Has an avatar sat down?
                    if (llAvatarOnSitTarget() != null_key) {
                        
                        // Store the new sitter
                        sitter = llAvatarOnSitTarget();
                        
                        // Make sure the given avatar is allowed to use this object
                        if (!sloodle_check_access_use(sitter)) {
                            sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "nopermission:use", [llKey2Name(sitter)], null_key, "");
                            llUnSit(sitter);
                            sitter = null_key;
                            return;
                        }
                        
                        // Our current position as the lowest point
                        vector thispos = llGetPos();
                        lowestvector = (float)thispos.z;
                        // Start the quiz
                        sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "starting", [llKey2Name(sitter)], null_key, "quiz");
                        state check_quiz;
                    }
                }
            }
        }
        
        
        // Fetching the general quiz data
        state check_quiz
        {
            state_entry()
            {
                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "fetchingquiz", [], null_key, "quiz");
                
                // Clear existing data
                quizname = "";
                question_ids = [];
                num_questions = 0;
                active_question = -1;
                
                qtext_current = "";
                qtype_current = "";
                qtext_next = "";
                qtype_next = "";
                
                opids_current = [];
                optext_current = [];
                opgrade_current = [];
                opfeedback_current = [];
                
                opids_next = [];
                optext_next = [];
                opgrade_next = [];
                opfeedback_next = [];        
                
                // Request the quiz data from Moodle
                string body = "sloodlecontrollerid=" + (string)sloodlecontrollerid;
                body += "&sloodlepwd=" + sloodlepwd;
                body += "&sloodlemoduleid=" + (string)sloodlemoduleid;
                body += "&sloodleuuid=" + (string)sitter;
                body += "&sloodleavname=" + llEscapeURL(llKey2Name(sitter));
                body += "&sloodleserveraccesslevel=" + (string)sloodleserveraccesslevel;
                
                httpquizquery = llHTTPRequest(sloodleserverroot + sloodle_quiz_url, [HTTP_METHOD, "POST", HTTP_MIMETYPE, "application/x-www-form-urlencoded"], body);
                
                llSetTimerEvent(0.0);
                llSetTimerEvent((float)request_timeout);
            }
            
            state_exit()
            {
                llSetTimerEvent(0.0);
            }
            
            timer()
            {
                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "httptimeout", [], null_key, "");
                state ready;
            }
            
            http_response(key id, integer status, list meta, string body)
            {
                
                // Is this the response we are expecting?
                if (id != httpquizquery) return;
                httpquizquery = null_key;
                // Make sure the response was OK
                if (status != 200) {
                        sloodle_error_code(SLOODLE_TRANSLATE_SAY, NULL_KEY,status); //send message to error_message.lsl
                    state default;
                }
                
                // Split the response into several lines
                list lines = llParseString2List(body, ["\n"], []);
                integer numlines = llGetListLength(lines);
                body = "";
                list statusfields = llParseStringKeepNulls(llList2String(lines,0), ["|"], []);
                integer statuscode = (integer)llStringTrim(llList2String(statusfields, 0), STRING_TRIM);
                
                // Was it an error code?
                if (statuscode == -10301) {
                    sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "noattemptsleft", [llKey2Name(sitter)], null_key, "");
                    state ready;
                    return;
                    
                } else if (statuscode == -10302) {
                    sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "noquestions", [], null_key, "");
                    state ready;
                    return;
                    
                } else if (statuscode <= 0) {
                    //sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "servererror", [statuscode], null_key, "");
                     sloodle_error_code(SLOODLE_TRANSLATE_IM, sitter,statuscode); //send message to error_message.lsl                 
                    // Check if an error message was reported
                    if (numlines > 1) sloodle_debug(llList2String(lines, 1));
                    state ready;
                    return;
                }
                
                // We shouldn't need the status line anymore... get rid of it
                statusfields = [];
        
                // Go through each line of the response
                integer i;
                for (i = 1; i < numlines; i++) {
        
                    // Extract and parse the current line
                    string thislinestr = llList2String(lines, i);
                    list thisline = llParseString2List(thislinestr,["|"],[]);
                    string rowtype = llList2String( thisline, 0 ); 
        
                    // Check what type of line this is
                    if ( rowtype == "quiz" ) {
                        
                        // Get the quiz ID and name
                        quizid = (integer)llList2String(thisline, 4);
                        quizname = llList2String(thisline, 2);
                        
                    } else if ( rowtype == "quizpages" ) {
                        
                        // Extract the list of questions ID's
                        list question_ids_str = llCSV2List(llList2String(thisline, 3));
                        num_questions = llGetListLength(question_ids_str);
                        integer qiter = 0;
                        question_ids = [];
                        // Store all our question IDs
                        for (qiter = 0; qiter < num_questions; qiter++) {
                            question_ids += [(integer)llList2String(question_ids_str, qiter)];
                        }
                        
                        // Are we to randomize the order of the questions?
                        if (doRandomize) question_ids = llListRandomize(question_ids, 1);
                        active_question = 0;
                    }
                }
                
                // Make sure we have all the data we need
                if (quizname == "" || num_questions == 0) {
                    sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "noquestions", [], null_key, "quiz");
                    state default;
                    return;
                }
                
                // Report the status to the user
                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "ready", [quizname], null_key, "quiz");
                state quizzing;
            }
            
            on_rez(integer par)
            {
                llResetScript();
            }
            
            changed(integer change)
            {
                reinitialise();
            }
        }
        
        // Dummy state -- goes straight back into the quiz
        state repeat_quiz
        {
            state_entry()
            {
                state quizzing;
            }
            
            on_rez(integer par)
            {
                llResetScript();
            }
            
            changed(integer change)
            {
                reinitialise();
            }
        }
        
        
        // Running the quiz
        state quizzing
        {
            on_rez(integer param)
            {
                llResetScript();
            }
            
            state_entry()
            {
                llSetText("", <0.0,0.0,0.0>, 0.0);
                num_correct = 0;
                move_to_start();
                
                // Make sure we have some questions
                if (num_questions == 0) {
                    sloodle_debug("No questions - cannot run quiz.");
                    state default;
                    return;
                }
                
                // Listen for answers coming in from the avatar in both suitable channels

                llListen(SLOODLE_CHANNEL_AVATAR_DIALOG, "", sitter, "");
                llListen(0, "", sitter, "");
                
                // Start from the beginning
                active_question = 0;
                requesting_question = 0;
                httpquizquery = request_question(llList2Integer(question_ids, requesting_question));
            }
            
            state_exit()
            {
                llSetTimerEvent(0.0);
            }
            
            listen(integer channel, string name, key id, string message)
            {
                // If using dialogs, then only listen to the dialog channel
                if (doDialog && ((qtype_current == "multichoice") || (qtype_current == "truefalse"))) {
                    if (channel != SLOODLE_CHANNEL_AVATAR_DIALOG){
                             sloodle_translation_request(SLOODLE_TRANSLATE_IM, [0], "usedialogs", [llKey2Name(sitter)], sitter, "quiz");
                         return;
                         }
                } else {
                    if (channel != 0) return;
                }
            
                // Only listen to the sitter
                if (id == sitter) {
                    // Handle the answer...
                    float scorechange = 0;
                    string feedback = "";
                    
                    // Check the type of question this was
                    if ((qtype_current == "multichoice") || (qtype_current == "truefalse")) {
                        // Multiple choice - the response should be a number from the dialog box (1-based)
                        integer answer_num = (integer)message;
                        // Make sure it's valid
                        if ((answer_num > 0) && (answer_num <= llGetListLength(opids_current))) {
                            // Correct to 0-based
                            answer_num -= 1;
                            
                            feedback = llList2String(opfeedback_current, answer_num);
                            scorechange = llList2Float(opgrade_current, answer_num);
                            // Notify the server of the response
                            notify_server(qtype_current, llList2Integer(question_ids, active_question), llList2String(opids_current, answer_num));
                        } else {
                            sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "invalidchoice", [llKey2Name(sitter)], null_key, "quiz");
                            ask_current_question();
                        }        
                     } else if (qtype_current == "shortanswer") {
                               // Notify the server of the response
                               integer x = 0;
                               integer num_options = llGetListLength(optext_current);
                               for (x = 0; x < num_options; x++) {
                                   if (llToLower(message) == llToLower(llList2String(optext_current, x))) {
                                      feedback = llList2String(opfeedback_current, x);
                                      scorechange = llList2Float(opgrade_current, x);
                                   }
                               notify_server(qtype_current, llList2Integer(question_ids, active_question), message);
                               }        
                    } else if (qtype_current == "numerical") {
                               // Notify the server of the response
                               float number = (float)message;
                               integer x = 0;
                               integer num_options = llGetListLength(optext_current);
                               for (x = 0; x < num_options; x++) {
                                   if (number == (float)llList2String(optext_current, x)) {
                                      feedback = llList2String(opfeedback_current, x);
                                      scorechange = llList2Float(opgrade_current, x);
                                   }
                               notify_server(qtype_current, llList2Integer(question_ids, active_question), message);
                               }        
                    } 
                    
                    
                     else {
                        sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "invalidtype", [qtype_current], null_key, "quiz");
                    }

                            // Give the user feedback, and add their score
                            move_vertical(scorechange); // Visual feedback
                            play_sound(scorechange); // Audio feedback
                            
                            if(scorechange>0) num_correct++; // SAL added this
                            if (feedback != "") llInstantMessage(sitter, feedback); // Text feedback
                            else if (scorechange > 0.0) {                    
                                
                            sloodle_translation_request(SLOODLE_TRANSLATE_IM, [0], "correct", [llKey2Name(sitter)], sitter, "quiz");
                            //num_correct += 1; SAL commented out this
                            } else {
                            sloodle_translation_request(SLOODLE_TRANSLATE_IM, [0], "incorrect",  [llKey2Name(sitter)], sitter, "quiz");
                            }
                            llSleep(1.);  //wait to finish the sloodle_translation_request before next question.
        
                    
                    // Are we are at the end of the quiz?
                    if ((active_question + 1) >= num_questions) {
                        // Yes - finish off
                        finish_quiz();
                        // Do we want to repeat the quiz?
                        if (doRepeat) state repeat_quiz;
                        return;
                    }
                    
                    // Advance to the next question
                    active_question++;
                    // Has our 'next' question been loaded?
                    if (qloaded_next == active_question) {
                        // Yes
                        // Clear out our current data (a feeble attempt to save memory!)
                        qtext_current = "";
                        qtype_current = "";
                        opids_current = [];
                        optext_current = [];
                        opgrade_current = [];
                        opfeedback_current = [];
                        // Transfer all our 'next' question data into the 'current' question variables
                        qtext_current = qtext_next;
                        qtype_current = qtype_next;
                        opids_current = opids_next;
                        optext_current = optext_next;
                        opgrade_current = opgrade_next;
                        opfeedback_current = opfeedback_next;
                        
                        // Ask the current question, and request the next (if there is one)
                        ask_current_question();
                        
                        if ((active_question + 1) < num_questions) {
                            requesting_question = active_question + 1;
                            httpquizquery = request_question(llList2Integer(question_ids, requesting_question));
                        }
                    } else {
                        // No - still waiting on our next question.
                        // It is now technically our 'current' question, so the http_response will automatically ask it when it arrives.
                    }
                }
            }
            
            timer()
            {
                // There has been a timeout of the HTTP request
                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "httptimeout", [], null_key, "");
                llSetTimerEvent(0.0);
            }
            
            touch_start(integer num)
            {
                 if ((active_question + 1) < num_questions)
                if (llDetectedKey(0) == sitter) ask_current_question();
            }
        
            http_response(key request_id, integer status, list metadata, string body)
            {
                
                // This response will always contain question data.
                // If the current question is being loaded, then ask it right away, and load the next.
                // If the next question is being loaded, then just store it.
                // It will be made current and asked whenever the current one gets answered.
                // If the user ever gets ahead of our loading, then they will be waiting on the 'current' question.
                // As soon as that is loaded, it will get asked.
            
                // Is this the response we are expecting?
                if (request_id != httpquizquery) return;
                httpquizquery = null_key;
                llSetTimerEvent(0.0);
                // Make sure the response was OK
                if (status != 200) {
                    sloodle_error_code(SLOODLE_TRANSLATE_SAY, NULL_KEY,status); //send message to error_message.lsl
                    state default;
                }
                
                // Split the response into several lines
                list lines = llParseStringKeepNulls(body, ["\n"], []);
                integer numlines = llGetListLength(lines);
                body = "";
                list statusfields = llParseStringKeepNulls(llList2String(lines,0), ["|"], []);
                integer statuscode = llList2Integer(statusfields, 0);
                
                // Was it an error code?
                if (statuscode == -331) {
                    sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "nopermission:use", [llKey2Name(sitter)], null_key, "");
                    sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "resetting", [], null_key, "");
                    state default;
                    return;
                    
                } else if (statuscode == -10301) {
                    sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "noattemptsleft", [llKey2Name(sitter)], null_key, "");
                    return;
                    
                } else if (statuscode == -10302) {
                    sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "noquestions", [], null_key, "");
                    return;
                    
                } else if (statuscode <= 0) {
                    //sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "servererror", [statuscode], null_key, "");
                     sloodle_error_code(SLOODLE_TRANSLATE_IM, sitter,statuscode); //send message to error_message.lsl
                    // Check if an error message was reported
                    if (numlines > 1) sloodle_debug(llList2String(lines, 1));
                    return;
                }
                
                // Save a tiny bit of memory!
                statusfields = [];
                
                // Are we loading the current question?
                integer iscurrent = (active_question == requesting_question);
        
                // Go through each line of the response
                list thisline = [];
                string rowtype = "";
                integer i = 0;
                for (i = 1; i < numlines; i++) {
        
                    // Extract and parse the current line
                    list thisline = llParseString2List(llList2String(lines, i),["|"],[]);
                    string rowtype = llList2String( thisline, 0 );
        
                    // Check what type of line this is
                    if ( rowtype == "question" ) {
                        
                        // Grab the question information and reset the options
                        if (iscurrent) {
                            qtext_current = llList2String(thisline, 4);
                            qtype_current = llList2String(thisline, 7);
                            
                            opids_current = [];
                            optext_current = [];
                            opgrade_current = [];
                            opfeedback_current = [];
                            
                            // Make sure it's a valid question type
                            if ((qtype_current != "multichoice") && (qtype_current != "truefalse") && (qtype_current != "numerical") && (qtype_current != "shortanswer")) {
                                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "invalidtype", [qtype_current], null_key, "quiz");
                                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "resetting", [], null_key, "");
                                state default;
                                return;
                            }
                        } else {
                            qloaded_next = requesting_question;
                        
                            qtext_next = llList2String(thisline, 4);
                            qtype_next = llList2String(thisline, 7);
                        
                            opids_next = [];
                            optext_next = [];
                            opgrade_next = [];
                            opfeedback_next = [];
                            
                            // Make sure it's a valid question type
                            if ((qtype_current != "multichoice") && (qtype_current != "truefalse") && (qtype_current != "numerical") && (qtype_current != "shortanswer")) {
                                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "invalidtype", [qtype_next], null_key, "quiz");
                                sloodle_translation_request(SLOODLE_TRANSLATE_SAY, [0], "resetting", [], null_key, "");
                                state default;
                                return;
                            }
                        }
                        
                    } else if ( rowtype == "questionoption" ) {
                        
                        // Add this option to the appropriate place
                        if (iscurrent) {
                            opids_current += [(integer)llList2String(thisline, 2)];
                            optext_current += [llList2String(thisline, 4)];
                            opgrade_current += [(float)llList2String(thisline, 5)];
                            opfeedback_current += [llList2String(thisline, 6)];
                        } else {
                            opids_next += [(integer)llList2String(thisline, 2)];
                            optext_next += [llList2String(thisline, 4)];
                            opgrade_next += [(float)llList2String(thisline, 5)];
                            opfeedback_next += [llList2String(thisline, 6)];
                        }
                    }
                }
                
                // Our response now depends on whether or not we just loaded the current question
                if (iscurrent) {
                    // Just loaded the current question.
                    // Is there another question after this one?
                    if ((active_question + 1) < num_questions) {
                        // Yes - load it
                        requesting_question = active_question + 1;
                        httpquizquery = request_question(llList2Integer(question_ids, requesting_question));
                    }
                    
                    // Automatically ask this question
                    ask_current_question();
                }
            }
            
            changed(integer change)
            {
                move_to_start();
                reinitialise();
            }
        }