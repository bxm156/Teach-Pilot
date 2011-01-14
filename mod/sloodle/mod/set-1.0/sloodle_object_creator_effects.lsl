// Sloodle Set object creator script.
// Sends a cleanup chat-message when touched.
//
// Part of the Sloodle project (www.sloodle.org).
// Copyright (c) 2007-8 Sloodle
// Released under the GNU GPL v3
//
// Contributors:
//  Edmund Edgar
//  Peter R. Bloomfield


integer SLOODLE_CHANNEL_OBJECT_CREATOR_REZZING_STARTED = -1639270082;
integer SLOODLE_CHANNEL_OBJECT_CREATOR_REZZING_FINISHED = -1639270083;
integer SLOODLE_CHANNEL_OBJECT_CREATOR_WILL_REZ_AT_POSITION = -1639270084;
integer SLOODLE_CHANNEL_SET_CONFIGURED = -1639270091;
integer SLOODLE_CHANNEL_SET_RESET = -1639270092;


default
{
    link_message(integer sender_num, integer num, string str, key id) {
        if (num == SLOODLE_CHANNEL_OBJECT_CREATOR_REZZING_STARTED) {
        // TODO: Change color or something
        //llSetTimerEvent(5.0);         
        } else if (num == SLOODLE_CHANNEL_OBJECT_CREATOR_REZZING_FINISHED) {
          //  llParticleSystem([]);
        } else if (num == SLOODLE_CHANNEL_SET_CONFIGURED) {
        } else if (num == SLOODLE_CHANNEL_SET_RESET) {
        }
    }
    state_entry()
    {
        llSetText("", <0,0,0>, 0.0);
    }
}

