Datamints Kirby-Plugin Ldap
-

Overview
-
With this plugin, you can login into Kirby with your LDAP-credentials (mail + password).
It creates a user for you at your first login and loads your full name into your Kirby-User.
Per default, a user that logged in with ldap, is admin and has all permissions. See part "Configure"
Language of new created user is en as default, but can be changed in backend.

Install
-
to install, just put this plugin-folder into the public/site/plugins folder.

You can also install it with composer or as git submodule if you want to.

Configure
-
configure your ldap-server: 

    public/site/config/config.php
    ---
    
    <?php
        return [
            ...
            'datamints.ldap.host'     => "ldap://subdomain.domain.tld:port", //host of ldap-server
            'datamints.ldap.bind_dn'  => "cn=common-name,dc=domain,dc=tld", //login username for global access
            'datamints.ldap.bind_pw'  => "[password that fits to ldap_bind_dn", //login password for global access
            'datamints.ldap.base_dn'  => "ou=organizational-unit,dc=domain,dc=tld", //path to search for users
            'datamints.ldap.is_admin' => false, //optional. Is every Ldap-user an admin? Default: true
        ];
    ?>

if you want to change specific permissions (not just admin true/false), copy site/plugins/datamints_ldap/blueprints/users/LdapUser.yml to site/blueprints/users/LdapUser.yml and change them in that new file as described in https://getkirby.com/docs/guide/users/permissions

Additional information
-
if you want, you can gitignore all ldap-users

    .gitignore
    ---
    
    public/site/accounts/LDAP_*


## License

[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

- This Kirby-Plugin is licensed under the GPLv3 License
- Copyright 2020 © <a href="http://www.datamints.com" target="_blank">datamints GmbH</a>
