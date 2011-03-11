<?php

function msgQueryLocations( $Request )
{
	if ( ValidRaidlead() )
    {
    	$Connector = Connector::GetInstance();
    	
    	$ListLocations = $Connector->prepare("Select * FROM `".RP_TABLE_PREFIX."Location` ORDER BY Name");
        
        
        if ( !$ListLocations->execute() )
        {
        	postErrorMessage( $ListLocations );
        	$ListLocations->closeCursor();
            return;
        }
        
        while ( $Data = $ListLocations->fetch() )
        {
        	echo "<location>";
        	echo "<id>".$Data["LocationId"]."</id>";
        	echo "<name>".$Data["Name"]."</name>";
        	echo "<image>".$Data["Image"]."</image>";
        	echo "</location>";
        }
        
        $ListLocations->closeCursor();                               
		
    	$images = scandir("../images/raidsmall");
    	
    	foreach ( $images as $image )
    	{
    		if ( strripos( $image, ".png" ) !== false )
    		{
    			echo "<locationimage>".$image."</locationimage>";
    		}
    	}
    }
    else
    {
        echo "<error>".L("Access denied")."</error>";
    }
}
   
?>