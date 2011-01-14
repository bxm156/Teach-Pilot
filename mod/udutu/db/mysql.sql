


CREATE TABLE `mdl_udutu` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `course` bigint(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `reference` varchar(255) NOT NULL default '',
  `summary` text NOT NULL,
  `version` varchar(9) NOT NULL default '',
  `maxgrade` double NOT NULL default '0',
  `grademethod` tinyint(2) NOT NULL default '0',
  `maxattempt` bigint(10) NOT NULL default '1',
  `updatefreq` tinyint(1) unsigned NOT NULL default '0',
  `md5hash` varchar(32) NOT NULL default '',
  `launch` bigint(10) unsigned NOT NULL default '0',
  `skipview` tinyint(1) unsigned NOT NULL default '1',
  `hidebrowse` tinyint(1) NOT NULL default '0',
  `hidetoc` tinyint(1) NOT NULL default '0',
  `hidenav` tinyint(1) NOT NULL default '0',
  `auto` tinyint(1) unsigned NOT NULL default '0',
  `popup` tinyint(1) unsigned NOT NULL default '0',
  `options` varchar(255) NOT NULL default '',
  `width` bigint(10) unsigned NOT NULL default '100',
  `height` bigint(10) unsigned NOT NULL default '600',
  `timemodified` bigint(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `mdl_scor_cou_ix` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='each table is one udutu module and its configuration';

/*Table structure for table `mdl_udutu_scoes` */

CREATE TABLE `mdl_udutu_scoes` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `udutu` bigint(10) unsigned NOT NULL default '0',
  `manifest` varchar(255) NOT NULL default '',
  `organization` varchar(255) NOT NULL default '',
  `parent` varchar(255) NOT NULL default '',
  `identifier` varchar(255) NOT NULL default '',
  `launch` varchar(255) NOT NULL default '',
  `udututype` varchar(5) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `mdl_scorscoe_sco_ix` (`udutu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='each SCO part of the udutu module';

/*Table structure for table `mdl_udutu_scoes_data` */


CREATE TABLE `mdl_udutu_scoes_data` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `mdl_scorscoedata_sco_ix` (`scoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains variable data get from packages';

/*Table structure for table `mdl_udutu_scoes_track` */


CREATE TABLE `mdl_udutu_scoes_track` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `userid` bigint(10) unsigned NOT NULL default '0',
  `udutuid` bigint(10) NOT NULL default '0',
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `attempt` bigint(10) unsigned NOT NULL default '1',
  `element` varchar(255) NOT NULL default '',
  `value` longtext NOT NULL,
  `timemodified` bigint(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mdl_scorscoetrac_usescosco_uix` (`userid`,`udutuid`,`scoid`,`attempt`,`element`),
  KEY `mdl_scorscoetrac_use_ix` (`userid`),
  KEY `mdl_scorscoetrac_ele_ix` (`element`),
  KEY `mdl_scorscoetrac_sco_ix` (`udutuid`),
  KEY `mdl_scorscoetrac_sco2_ix` (`scoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='to track SCOes';

/*Table structure for table `mdl_udutu_seq_mapinfo` */


CREATE TABLE `mdl_udutu_seq_mapinfo` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `objectiveid` bigint(10) unsigned NOT NULL default '0',
  `targetobjectiveid` bigint(10) unsigned NOT NULL default '0',
  `readsatisfiedstatus` tinyint(1) NOT NULL default '1',
  `readnormalizedmeasure` tinyint(1) NOT NULL default '1',
  `writesatisfiedstatus` tinyint(1) NOT NULL default '0',
  `writenormalizedmeasure` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mdl_scorseqmapi_scoidobj_uix` (`scoid`,`id`,`objectiveid`),
  KEY `mdl_scorseqmapi_sco_ix` (`scoid`),
  KEY `mdl_scorseqmapi_obj_ix` (`objectiveid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='udutu2004 objective mapinfo description';

/*Table structure for table `mdl_udutu_seq_objective` */


CREATE TABLE `mdl_udutu_seq_objective` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `primaryobj` tinyint(1) NOT NULL default '0',
  `objectiveid` bigint(10) unsigned NOT NULL default '0',
  `satisfiedbymeasure` tinyint(1) NOT NULL default '1',
  `minnormalizedmeasure` float(11,4) unsigned NOT NULL default '0.0000',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mdl_scorseqobje_scoid_uix` (`scoid`,`id`),
  KEY `mdl_scorseqobje_sco_ix` (`scoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='udutu2004 objective description';

/*Table structure for table `mdl_udutu_seq_rolluprule` */

CREATE TABLE `mdl_udutu_seq_rolluprule` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `childactivityset` varchar(15) NOT NULL default '',
  `minimumcount` bigint(10) unsigned NOT NULL default '0',
  `minimumpercent` float(11,4) unsigned NOT NULL default '0.0000',
  `conditioncombination` varchar(3) NOT NULL default 'all',
  `action` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mdl_scorseqroll_scoid_uix` (`scoid`,`id`),
  KEY `mdl_scorseqroll_sco_ix` (`scoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='udutu2004 sequencing rule';

/*Table structure for table `mdl_udutu_seq_rolluprulecond` */

CREATE TABLE `mdl_udutu_seq_rolluprulecond` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `rollupruleid` bigint(10) unsigned NOT NULL default '0',
  `operator` varchar(5) NOT NULL default 'noOp',
  `cond` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mdl_scorseqroll_scorolid_uix` (`scoid`,`rollupruleid`,`id`),
  KEY `mdl_scorseqroll_sco2_ix` (`scoid`),
  KEY `mdl_scorseqroll_rol_ix` (`rollupruleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='udutu2004 sequencing rule';

/*Table structure for table `mdl_udutu_seq_rulecond` */


CREATE TABLE `mdl_udutu_seq_rulecond` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `ruleconditionsid` bigint(10) unsigned NOT NULL default '0',
  `refrencedobjective` varchar(255) NOT NULL default '',
  `measurethreshold` float(11,4) NOT NULL default '0.0000',
  `operator` varchar(5) NOT NULL default 'noOp',
  `cond` varchar(30) NOT NULL default 'always',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `mdl_scorseqrule_idscorul_uix` (`id`,`scoid`,`ruleconditionsid`),
  KEY `mdl_scorseqrule_sco2_ix` (`scoid`),
  KEY `mdl_scorseqrule_rul_ix` (`ruleconditionsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='udutu2004 rule condition';

/*Table structure for table `prefix_udutu_seq_ruleconds` */


CREATE TABLE `prefix_udutu_seq_ruleconds` (
  `id` bigint(10) unsigned NOT NULL auto_increment,
  `scoid` bigint(10) unsigned NOT NULL default '0',
  `conditioncombination` varchar(3) NOT NULL default 'all',
  `ruletype` tinyint(2) unsigned NOT NULL default '0',
  `action` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `prefix_scorseqrule_scoid_uix` (`scoid`,`id`),
  KEY `prefix_scorseqrule_sco_ix` (`scoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='udutu2004 rule conditions';


