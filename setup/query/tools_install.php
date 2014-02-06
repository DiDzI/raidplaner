<?php
    require_once(dirname(__FILE__)."/../../lib/private/connector.class.php");

    function InstallDB($Prefix)
    {
        $Out = Out::getInstance();
        $Connector = Connector::getInstance();

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Attendance` (
              `AttendanceId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `CharacterId` int(10) unsigned NOT NULL,
              `UserId` int(11) unsigned NOT NULL,
              `RaidId` int(10) unsigned NOT NULL,
              `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `Status` enum('ok','available','unavailable','undecided') NOT NULL,
              `Role` char(3) NOT NULL,
              `Class` char(3) NOT NULL,
              `Comment` text NOT NULL,
              PRIMARY KEY (`AttendanceId`),
              KEY `UserId` (`UserId`),
              KEY `CharacterId` (`CharacterId`),
              KEY `RaidId` (`RaidId`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Character` (
              `CharacterId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `UserId` int(10) unsigned NOT NULL,
              `Game` char(4) NOT NULL,
              `Name` varchar(64) NOT NULL,
              `Mainchar` enum('true','false') NOT NULL DEFAULT 'false',
              `Class` varchar(128) NOT NULL,
              `Role1` char(3) NOT NULL,
              `Role2` char(3) NOT NULL,
              PRIMARY KEY (`CharacterId`),
              KEY `UserId` (`UserId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Location` (
              `LocationId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Game` char(4) NOT NULL,
              `Name` varchar(128) NOT NULL,
              `Image` varchar(255) NOT NULL,
              PRIMARY KEY (`LocationId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Raid` (
              `RaidId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `LocationId` int(10) unsigned NOT NULL,
              `Stage` enum('open','locked','canceled') NOT NULL DEFAULT 'open',
              `Size` tinyint(2) unsigned NOT NULL,
              `Start` datetime NOT NULL,
              `End` datetime NOT NULL,
              `Mode` enum('manual','overbook','attend','all') NOT NULL,
              `Description` text NOT NULL,
              `SlotRoles` varchar(24) NOT NULL,
              `SlotCount` varchar(12) NOT NULL,
              PRIMARY KEY (`RaidId`),
              KEY `LocationId` (`LocationId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."Setting` (
              `SettingId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Name` varchar(64) NOT NULL,
              `IntValue` int(11) NOT NULL,
              `TextValue` varchar(255) NOT NULL,
              PRIMARY KEY (`SettingId`),
              FULLTEXT KEY `Name` (`Name`),
              UNIQUE KEY `Unique_Name` (`Name`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."User` (
              `UserId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `Group` enum('admin','raidlead','member','none') NOT NULL,
              `ExternalId` int(10) unsigned NOT NULL,
              `ExternalBinding` char(10) NOT NULL,
              `BindingActive` enum('true','false') NOT NULL DEFAULT 'true',
              `Login` varchar(255) NOT NULL,
              `Password` char(128) NOT NULL,
              `Salt` char(64) NOT NULL,
              `OneTimeKey` char(32) NOT NULL,
              `SessionKey` char(32) NOT NULL,
              `Created` datetime NOT NULL,
              PRIMARY KEY (`UserId`),
              KEY `ExternalId` (`ExternalId`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );

        $Connector->exec( "CREATE TABLE IF NOT EXISTS `".$Prefix."UserSetting` (
              `UserSettingId` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `UserId` int(10) unsigned NOT NULL,
              `Name` varchar(64) NOT NULL,
              `IntValue` int(11) NOT NULL,
              `TextValue` varchar(255) NOT NULL,
              PRIMARY KEY (`UserSettingId`),
              KEY `UserId` (`UserId`),
              FULLTEXT KEY `Name` (`Name`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;" );
    }

    // ------------------------------------------------------------------------

    function InstallDefaultSettings($Prefix)
    {
        $Connector = Connector::getInstance();

        // Add default values for settings table

        $TestQuery = $Connector->prepare( "SELECT * FROM `".$Prefix."Setting`" );
        $ExistingSettings = array();

        $TestQuery->loop( function($Row) use ($ExistingSettings)
        {
            array_push($ExistingSettings, $Row["Name"]);
        });

        if ( !in_array("PurgeRaids", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('PurgeRaids', 7257600, '');" );

        if ( !in_array("LockRaids", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('LockRaids', 3600, '');" );

        if ( !in_array("RaidStartHour", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidStartHour', 19, '');" );

        if ( !in_array("RaidStartMinute", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidStartMinute', 30, '');" );

        if ( !in_array("RaidEndHour", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidEndHour', 23, '');" );

        if ( !in_array("RaidEndMinute", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidEndMinute', 0, '');" );

        if ( !in_array("RaidSize", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidSize', 10, '');" );

        if ( !in_array("RaidMode", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('RaidMode', 0, 'manual');" );

        if ( !in_array("Site", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Site', 0, '');" );

        if ( !in_array("HelpPage", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('HelpPage', 0, '');" );

        if ( !in_array("Theme", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Theme', 0, 'cataclysm');" );

        if ( !in_array("GameConfig", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('GameConfig', 0, 'wow');" );

        if ( !in_array("TimeFormat", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('TimeFormat', 24, '');" );

        if ( !in_array("StartOfWeek", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('StartOfWeek', 1, '');" );

        if ( !in_array("Version", $ExistingSettings) )
            $Connector->exec( "INSERT INTO `".$Prefix."Setting` (`Name`, `IntValue`, `TextValue`) VALUES('Version', 110, '');" );
        else
            $Connector->exec( "UPDATE `".$Prefix."Setting` SET IntValue=110 WHERE Name='Version' LIMIT 1" );
    }
    
    // ------------------------------------------------------------------------
    
    function RemoveLast($aCandidates, $aString)
    {
        for ($i = strlen($aString)-1; $i>0; --$i)
        {
            if ( in_array($aString[$i], $aCandidates) )
            {
                return substr($aString, 0, $i).substr($aString, $i+1);
            }
        }
        
        return $aString;
    }
    
    // ------------------------------------------------------------------------
    
    function StripDuplicates($aString)
    {
        $Result = $aString[0];
        $Last = $aString[0];
        $Chars = Array($Last);
        
        for ($i=1; $i<strlen($aString); ++$i)
        {
            if (($aString[$i] != $Last) && !in_array($aString[$i], $Chars))
            {
                $Result .= $aString[$i];
                array_push($Chars, $aString[$i]);
            }
               
            $Last = $aString[$i];
        }
                
        return $Result;
    }
    
    // ------------------------------------------------------------------------
    
    function IsAlternating($aString, $aChars)
    {
        $State = in_array($aString[0], $aChars);
        for ($i=1; $i<strlen($aString); ++$i)
        {
            $NewState = in_array($aString[$i], $aChars);
            if ($NewState == $State)
                return false;
                
            $State = $NewState;
        }
        
        return true;
    }
    
    // ------------------------------------------------------------------------
    
    function BuildXCC($aName, $aCount)
    {
        $Id = StripDuplicates(strtolower($aName));
        
        while (strlen($Id) < $aCount)
        {
            $Id .= "_";
        }
        
        if (strlen($Id) == 3)
            return $Id;
        
        $Replace = Array("a","e","i","o","u"); 
         
        if (IsAlternating(substr($Id,0,$aCount+1), $Replace))
            return substr($Id,0,$aCount);
        
        while (strlen($Id) > $aCount)
        {
            $Reduced = RemoveLast($Replace, $Id);            
            $Id = ($Reduced == $Id) 
                ? substr($Reduced, 0, $aCount)
                : $Reduced;
        }
        
        return $Id;
    }
    
    // ------------------------------------------------------------------------
    
    function MakeUnqiue($aId, $aFullName, $aNames)
    {
        if (!in_array($aId, $aNames))
            return $aId;
            
        $UniqueId = $aId;
        $CharIdx = intval(strlen($UniqueId) / 2);
        $CandidateIdx = strlen($aFullName)-1;
        
        $UniqueId[strlen($UniqueId)-1] = $aFullName[$CandidateIdx];
        
        while ((in_array($UniqueId, $aNames)) && ($CandidateIdx > 0))
        {
            $UniqueId[$CharIdx] = $aFullName[$CandidateIdx];
            --$CandidateIdx;
        }
            
        return $UniqueId;
    }
    
    // ------------------------------------------------------------------------
    
    function UpdateGameConfig110($aGameConfig100, &$aClassNameToId, &$aRoleIdxToId, &$aGame )
    {
        $StyleMappings = Array(
            "images/roles/slot_role1.png" => "role_melee",
            "images/roles/slot_role2.png" => "role_heal",
            "images/roles/slot_role3.png" => "role_support",
            "images/roles/slot_role4.png" => "role_tank",
        );
        
        include_once($aGameConfig100);
        $NewGameConfig = fopen(dirname(__FILE__)."/../../themes/games/legacy.xml", "w");
        
        if ($NewGameConfig === false)
            return false;
        
        $RoleNameToId   = Array();
        $aRoleIdxToId   = Array();
        $aClassNameToId = Array();
        $aGame = "rp10";
        
        // Header
        
        fwrite($NewGameConfig, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
        fwrite($NewGameConfig, "<game>\n");
        fwrite($NewGameConfig, "\t<id>rp10</id>\n");
        fwrite($NewGameConfig, "\t<name>Raidplaner 1.0.x</name>\n");
        fwrite($NewGameConfig, "\t<family>wow</family>\n");
        fwrite($NewGameConfig, "\t<classmode>single</classmode>\n");
        
        // Roles
        
        fwrite($NewGameConfig, "\n\t<roles>\n");
        
        $RoleIdx = 0;
        while (list($Name, $Loca) = each($gRoles))
        {
            $RoleId = BuildXCC($Loca, 3);
            $RoleId = MakeUnqiue($RoleId, $Loca, $aRoleIdxToId);
               
            $Style = (isset($StyleMappings[$gRoleImages[$RoleIdx]]))
                ? $StyleMappings[$gRoleImages[$RoleIdx]]
                : "role_support";
                
            fwrite($NewGameConfig, "\t\t<role id=\"".$RoleId."\" loca=\"".$Loca."\" style=\"".$Style."\"/>\n");
            
            array_push($aRoleIdxToId, $RoleId);
            $RoleNameToId[$Name] = $RoleId;
            
            ++$RoleIdx;
        }
        
        fwrite($NewGameConfig, "\t</roles>\n");
        
        // Classes
        
        fwrite($NewGameConfig, "\n\t<classes>\n");
        
        $RoleIdx = 0;
        while (list($Name, $ClassDesc) = each($gClasses))
        {
            if ($Name == "empty") continue;
                
            $ClassId = BuildXCC($Name, 3);
            $ClassId = MakeUnqiue($ClassId, $Name, array_values($aClassNameToId));
                        
            $aClassNameToId[$Name] = $ClassId;
                
            fwrite($NewGameConfig, "\t\t<class id=\"".$ClassId."\" loca=\"".$ClassDesc[0]."\" style=\"".$Name."\">\n");
            
            foreach($ClassDesc[2] as $RoleName)
            {
                if ($RoleName == $ClassDesc[1])
                    fwrite($NewGameConfig, "\t\t\t<role id=\"".$RoleNameToId[$RoleName]."\" default=\"true\"/>\n");
                else
                    fwrite($NewGameConfig, "\t\t\t<role id=\"".$RoleNameToId[$RoleName]."\"/>\n");
            }
            
            fwrite($NewGameConfig, "\t\t</class>\n");
        }
        
        fwrite($NewGameConfig, "\t</classes>\n");
        
        // Raidview
        
        fwrite($NewGameConfig, "\n\t<raidview>\n");
        
        $RoleIdx = 0;
        foreach($gRoleColumnCount as $Count)
        {
            fwrite($NewGameConfig, "\t\t<slots role=\"".$aRoleIdxToId[$RoleIdx]."\" order=\"".($RoleIdx+1)."\" columns=\"".$Count."\"/>\n");
            ++$RoleIdx;
        }
        
        fwrite($NewGameConfig, "\t</raidview>\n");
        
        // Groups
        
        fwrite($NewGameConfig, "\n\t<groups>\n");
        
        while(list($Size, $RoleCount) = each($gGroupSizes))
        {
            fwrite($NewGameConfig, "\t\t<group count=\"".$Size."\">\n");
            
            $RoleIdx = 0;
            foreach($RoleCount as $Count)
            {
                fwrite($NewGameConfig, "\t\t\t<role id=\"".$aRoleIdxToId[$RoleIdx]."\" count=\"".$Count."\"/>\n");
                ++$RoleIdx;
            }
            
            fwrite($NewGameConfig, "\t\t</group>\n");
        }
        
        fwrite($NewGameConfig, "\t</groups>\n");
        
        // Clean up
        
        fwrite($NewGameConfig, "</game>\n");
        
        unset($gRoles);
        unset($gRoleImages);
        unset($gRoleColumnCount);
        unset($gClases);
        unset($gGroupSizes);
        
        return true;
    }
?>