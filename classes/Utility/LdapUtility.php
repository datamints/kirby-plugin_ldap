<?php

class LdapUtility {
    private $ldapConn = null;
    private static $utility = null;

    /**
     * LdapUtility constructor.
     * stores itself in a static variable
     */
    function __construct() {
        global $utility;
        $utility = $this;
    }

    /**
     * We dont need more than one Utility.
     * returns the saved one or create a new if no utility object is stored
     *
     * @return LdapUtility
     */
    public static function getUtility() {
        global $utility;
        if ($utility == null) {
            return new LdapUtility();
        }
        return $utility;
    }

    /**
     * get information about the user from the Ldap-Server. returns $user or FALSE
     * $user is an array of strings [uid, dn, name, lastname, givenname, mail]
     *
     * @param string $mail
     *
     * @return array|false
     */
    public function getLdapUser($mail) {
        $ldap_base_dn = option('datamints.ldap.base_dn');
        if(empty($mail)) return false;

        $ldap = $this->getLdapConnection();

        //search for matching user
        $filter = "(mail=$mail)";
        $result = ldap_search($ldap, $ldap_base_dn, $filter);
        
        //get user
        $entries = ldap_get_entries($ldap, $result);
        
        //create user object. Is false on fail.
        $user = false;
        
        //check if user is found
        $count = $entries["count"];
        if(0<$count) {
            $entry = $entries[0];
            
            //beautify user-array
            $user = [
                "uid" => $entry["uid"][0],
                "dn" => $entry["dn"],
                "name" => $entry["cn"][0],
                "lastname" => $entry["sn"][0],
                "givenname" => $entry["givenname"][0],
                "mail" => $entry["mail"][0],
            ];
        }
        
        //return user array or false on fail
        return $user;
    }

    /**
     * gets the LdapConnection generated with ldap_connect()
     * if no Connection is existing, create a new one
     *
     * @return mixed
     */
    private function getLdapConnection() {
        global $ldapConn;
        if($ldapConn != null) return $ldapConn;
        $this->getNewLdapConnection();
        return $ldapConn;
    }

    /**
     * Creates a new LdapConnection generated with ldap_connect(), sets options, starts tls and binds it once that ldap_search works.
     * Sets the new Connection into the global variable $ldapConn
     */
    private function getNewLdapConnection() {
        global $ldapConn;
        $ldap_host = option("datamints.ldap.host");
        $ldap_bind_dn = option("datamints.ldap.bind_dn");
        $ldap_bind_pw = option("datamints.ldap.bind_pw");

        //create uri-element
        //TODO or throw Error
        $ldapConn = ldap_connect($ldap_host) or die("invalid Host: ".$ldap_host." - it should look like ldap://subdomain.domain.tld:port");

        //Ldap-options
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
        
        //connect
        ldap_start_tls($ldapConn) or die("cant connect tls: ".$ldap_host);

        //bind Ldap-server
        $this->getLdapBind($ldap_bind_dn, $ldap_bind_pw);
    }

    /**
     * tries to bind with the given user and password.
     * user is the ldap-dn string
     *
     * @param string $user
     * @param string $password
     * @return bool
     */
    private function getLdapBind($user, $password) {
        set_error_handler(function() { $bind = false; });
        $bind = ldap_bind($this->getLdapConnection(), $user, $password);
        restore_error_handler();
        return $bind;
    }

    /**
     * gets the ldap-dn of a user from his mail
     * throws exception if no email is passed.
     *
     * @param string $mail
     * @return string
     * @throws Exception
     */
    public function getLdapDn($mail) {
        if(strlen($mail)<1) throw new Exception("get Ldap DN without mail");
        $user = $this->getLdapUser($mail);
        return $user["dn"];
    }

    /**
     * checks if the user credentials are correct.
     * params are mail and plain-text password
     * returns boolean if the credentials are correct
     *
     * @param $mail
     * @param $ldap_user_pw
     * @return bool
     * @throws Exception
     */
    public function validatePassword($mail, $ldap_user_pw) {
        if(strlen($mail)<1) throw new Exception("validate Password without mail");
        $ldap_user_dn = $this->getLdapDn($mail);
        $bind = $this->getLdapBind($ldap_user_dn, $ldap_user_pw);
        if($bind != false) {
            $bind = true;
        }
        return $bind;
    }
}
?>