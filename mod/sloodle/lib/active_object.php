<?php
    // This file is part of the Sloodle project (www.sloodle.org)
    
    /**
    * Defines a structure to store information about an active object
    *
    * @package sloodle
    * @copyright Copyright (c) 2008 Sloodle (various contributors)
    * @license http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3
    *
    * @contributor Peter R. Bloomfield
    */
    
    
    /**
    * An active object, relating to the Sloodle active objects DB table.
    * @package sloodle
    */
    class SloodleActiveObject
    {
        /**
        * The UUID of this object.
        * @var string
        * @access public
        */
        var $uuid = '';
        
        /**
        * The name of this object.
        * @var string
        * @access public
        */
        var $name = '';
        
        /**
        * The password of this object.
        * @var string
        * @access public
        */
        var $password = '';
        
        /**
        * The type of this object.
        * @var string
        * @access public
        */
        var $type = '';
        
        /**
        * The course/controller which this object is authorised for.
        * @var SloodleCourse
        * @access public
        */
        var $course = null;
        
        /**
        * The user who authorised this object.
        * @var SloodleUser
        * @access public
        */
        var $user = null;
    }

?>