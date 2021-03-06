<?php

function msgchangePassword( $aRequest )
{
    if ( validUser() && ($_REQUEST["id"] != 0) )
    {
        if ( UserProxy::getInstance()->validateCredentials($aRequest["passOld"]) )
        {
            // User authenticated with valid password
            // change the password of the given id. ChangePassword does a check
            // for validity (e.g. only admin may change other user's passwords)
            
            $Salt = UserProxy::generateKey128();
            $HashedPassword = NativeBinding::hash( $aRequest["passNew"], $Salt, "none" );
        
            if ( !UserProxy::changePassword($_REQUEST["id"], $HashedPassword, $Salt) )
            {
                $Out = Out::getInstance();
                $Out->pushError(L("PasswordLocked"));
            }
            
            msgQueryProfile( $_REQUEST );
        }
        else
        {
            $Out = Out::getInstance();
            $Out->pushError(L("WrongPassword"));
        }
    }
    else
    {
        $Out = Out::getInstance();
        $Out->pushError(L("AccessDenied"));
    }
}

?>