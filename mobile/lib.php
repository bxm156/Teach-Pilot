<?php

	/*
	**************************************************************************
	* Visit us at http://www.mpage.hk *
	* The Friendly Open mPage for iPhone/iPod Touch *
	* Visit us at http://www.mbooks.hk *
	* The Friendly Open mBooks for iPad *
	**************************************************************************
	**************************************************************************
	* NOTICE OF COPYRIGHT *
	* *
	* Copyright (C) 2010 MassMedia.hk *
	* *
	* This plugin is free; you can redistribute it and/or modify *
	* it under the terms of the GNU General Public License as *
	* published by the Free Software Foundation; either version*
	* 2 of the License, or (at your option) any later version. *
	* *
	* This program is distributed in the hope that it will be useful, *
	* but WITHOUT ANY WARRANTY; without even the implied warranty of *
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the *
	* GNU General Public License for more details: *
	* *
	* http://www.gnu.org/copyleft/gpl.html *
	* *
	* *
	* *
	**************************************************************************
	*/
	
	require_once('../config.php');
	require_once($CFG->dirroot .'/mod/forum/lib.php');
	require_once($CFG->dirroot.'/calendar/lib.php');
	require_once($CFG->libdir.'/filelib.php');
	
	function array_for_label($label) {
		$return_array = array();
		//$return_array["id"] = $label->id;
		//$return_array["course"] = $label->course;
		$return_array["name"] = $label->name;
		$return_array["content"] = $label->content;
		return $return_array;
	}
	
	function recursive_get_files($path) {
		global $CFG;
	
		$items = get_directory_list($path, array($CFG->moddata, 'backupdata'), false, true, true);
		
		$return_array = array();
		
		foreach ($items as $item) {
			if (is_dir("$path/$item")) {
				$return_array[$item] = recursive_get_files("$path/$item");
			}
			else {
				$return_array[$item] = filesize("$path/$item");
			}
		}
	       
	    return $return_array;
	}
	
	function array_for_resource($resource) {
	
		global $CFG;
	
		$return_array = array();
		$return_array["type"] = $resource->type;
		$return_array["name"] = $resource->name;
		$return_array["reference"] = $resource->reference;
		$return_array["alltext"] = $resource->alltext;
		$return_array["options"] = $resource->options;
		if($resource->type == "directory") {
			
			if ($resource->reference) {
	    		$relativepath = "{$resource->course}/{$resource->reference}";
	   		} else {
	    		$relativepath = "{$resource->course}";
	    	}
	    	
	    	$items = recursive_get_files("$CFG->dataroot/$relativepath");
	    	$return_array["files"] = $items;
		}
		return $return_array;
	}
	
	function array_for_assignment($assignment) {
		$return_array = array();
		$return_array["name"] = $assignment->name;
		$return_array["description"] = $assignment->description;
		//$return_array["format"] = $assignment->format;
		$return_array["assignmenttype"] = $assignment->assignmenttype;
		$return_array["resubmit"] = $assignment->resubmit;
		$return_array["timedue"] = $assignment->timedue;
		$return_array["timeavailable"] = $assignment->timeavailable;
		$return_array["maxbytes"] = $assignment->maxbytes;
		$return_array["grade"] = $assignment->grade;
		$return_array["var1"] = $assignment->var1;
		//$return_array["var2"] = $assignment->var2;
		//$return_array["var3"] = $assignment->var3;
		//$return_array["var4"] = $assignment->var4;
		//$return_array["var5"] = $assignment->var5;
		//$return_array["preventlate"] = $assignment->preventlate;
		return $return_array;
	}
	
	function recursive_get_posts($post) {
		$children = get_record_select('forum_posts', "parent = " . $post->id . " AND discussion = " . $post->discussion);
		
		$return_array = array();
		$return_array["id"] = $post->id;
		$return_array["userid"] = $post->userid;
		$return_array["created"] = $post->created;
		$return_array["modified"] = $post->modified;
		$return_array["subject"] = $post->subject;
		$return_array["message"] = $post->message;
		$return_array["format"] = $post->format;
		$return_array["attachment"] = $post->attachment;
		$return_array["children"] = array();
		
		if(is_array($children)) {
			foreach($children as $child) {
				$return_array["children"][] = recursive_get_posts($child);
			}
		}
		elseif($children) {
			$return_array["children"][] = recursive_get_posts($children);
		}
		
		return $return_array;
	}
	
	function array_for_unknown_module($unknown) {
		$return_array = array();
		$return_array["id"] = $unknown->id;
		$return_array["name"] = $unknown->name;
		return $return_array;
	}
	
	function array_for_modules($modules) {
		
		global $USER, $CFG;
		
		$return_array = array();
		
		foreach($modules as $module) {
			
			$can_see_hidden_modules = FALSE;
			if($CFG->rolesactive) {
				$context = get_context_instance(CONTEXT_COURSE, $module->course);
				$can_see_hidden_modules = has_capability('moodle/course:viewhiddenactivities', $context, $USER->id);
			}
			else {
				// For older Moodle versions.
				$can_see_hidden_modules = isteacher($module->course, $USER->id, true);
			}
			
			$show_module = ($module->visible || $can_see_hidden_modules);
			
			if($show_module) {
				
				$new_module = array();
				$new_module["course"] = $module->course;
				$new_module["indent"] = $module->indent;
				$new_module["module"] = $module->module;
				$new_module["modname"] = $module->modname;
				$new_module["modfullname"] = $module->modfullname;
				$new_module["section"] = $module->section;
				$new_module["id"] = $module->id;
				$new_module["visible"] = $module->visible;
				
				if($module->modname == "label") {
					$new_module["instance"] = array_for_label(get_record("label", "id", $module->instance));
					if($new_module["instance"]) {
						$return_array[] = $new_module;
					}
				}			
				else if($module->modname == "resource") {
					$new_module["instance"] = array_for_resource(get_record('resource', 'id', $module->instance));
					if($new_module["instance"]) {
						$return_array[] = $new_module;
					}
				}
				else if($module->modname == "assignment") {
					$new_module["instance"] = array_for_assignment(get_record('assignment', 'id', $module->instance));				
					if($new_module["instance"]) {
						$return_array[] = $new_module;
					}
				}
				else {
					$new_module["instance"] = array_for_unknown_module(get_record($module->modname, 'id', $module->instance));
					if($new_module["instance"]) {
						$return_array[] = $new_module;
					}
				}
					
			}
		}
		
		
		return $return_array;
	}
	
	function array_for_blocks($blocks) {
		
		if(empty($blocks)) {
			return array();
		}
		
		$return_array = array();
		
		$block_types = blocks_get_record();
		foreach($blocks as $block) {
				
				// Only use roles if they're enabled.
				$can_see_hidden_blocks = FALSE;
				
				if($CFG->rolesactive) {
					$context = get_context_instance(CONTEXT_COURSE, $course->id);
					$can_see_hidden_blocks = (has_capability('moodle/site:manageblocks', $context) or $block->visible);
				}
				else {
					$can_see_hidden_blocks = isteacher($course->id, $USER->id, true);
				}
				
				$show_block = ($block->visible or $can_see_hidden_blocks);
				
				if($show_block) {
			
					$this_block_type = $block_types[intval($block->blockid)];
					if($this_block_type->name == "calendar_upcoming") {
						$block_array = array();
						$block_array["name"] = $this_block_type->name;
						$return_array[] = $block_array;
					}
					else if($this_block_type->name == "calendar_month") {
						$block_array = array();
						$block_array["name"] = $this_block_type->name;
						$return_array[] = $block_array;
					}
				}
			}
		
		return $return_array;
	}

?>