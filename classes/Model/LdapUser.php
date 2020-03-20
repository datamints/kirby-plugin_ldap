<?php

use Kirby\Cms\User;

class LdapUser extends User
{
    /**
     * Compares the given password with ldap
     *
     * @param string $password
     * @return bool
     *
     * @throws \Kirby\Exception\NotFoundException If the user has no password
     * @throws \Kirby\Exception\InvalidArgumentException If the entered password is not valid
     * @throws \Kirby\Exception\InvalidArgumentException If the entered password does not match the user password
     */
    public function validatePassword(string $password = null): bool
    {
        if (Str::length($password) < 8) {
            http_response_code(403);
            throw new InvalidArgumentException(['key' => 'user.password.invalid']);
        }

        if ((LdapUtility::getUtility()->validatePassword($this->email(), $password)) !== true) {
            http_response_code(403);
            throw new InvalidArgumentException(['key' => 'user.password.notSame']);
        }

        return true;
    }

    /**
     * Checks if this user has the admin role
     * Ldap-Users are always admins.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return true;
    }

    /**
     * 
     * 
     * @return string
     */
    public function getLdapDn() {
        return LdapUtility::getUtility()->getLdapDn($this->email());
    }

    /**
     * creates a LdapUser in Kirby, if it does not exist in Kirby but in Ldap
     * if (!kirbyUser && ldapUser) new kirbyUser
     *
     * @param string $email
     *
     * @return \Kirby\Cms\User
     */
    public static function findOrCreateIfLdap($email) {
        //if email not set, return null
        if (empty($email)) {
            return null;
        }

        // if user already exists, return that user
        $user = kirby()->users()->findByKey($email);
        if($user != null) {
            return $user;
        }

        //if user does not exist in Kirby, search in Ldap
        $ldapUser = LdapUtility::getUtility()->getLdapUser($email);

        //if user does not exist in Ldap too, return null
        if(!$ldapUser) {
            return null;
        }

        //if user exists in Ldap
        //create that user in Kirby
        $userProps = [
            'id'        => "LDAP_".$ldapUser['lastname']."_".substr($ldapUser['uid'], 0, 5),
            'name'      => $ldapUser['name'],
            'email'     => $ldapUser['mail'],
            'language'  => 'en',
            'role'      => 'LdapUser'
        ];
        $user = new LdapUser($userProps);

        //save the user
        $user->writeCredentials($userProps);

        // add the user to users collection
        $user->kirby()->users()->add($user);

        //return it
        return $user;
    }
}