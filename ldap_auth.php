<?php
function autenticarConAdGrupo($user, $pass) {
    $user = trim($user);
    $pass = trim($pass);
    $ldaprdn = $user . '@cencosud.corp';
    
    $ldapconn = ldap_connect('ldap://g100603svdc4.cencosud.corp', 389);
    if (!$ldapconn) {
        error_log('Error: No se pudo conectar al servidor LDAP.');
        return false;
    }

    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

    if (!@ldap_bind($ldapconn, $ldaprdn, $pass)) {
        error_log('Error: Credenciales LDAP inválidas para ' . $user);
        ldap_close($ldapconn);
        return false;
    }

    // Incluir displayName para obtener el nombre completo
    $filter = '(&(objectClass=user)(sAMAccountName=' . ldap_escape($user, '', LDAP_ESCAPE_FILTER) . '))';
    $search_result = ldap_search($ldapconn, 'DC=cencosud,DC=corp', $filter, ['samaccountname', 'memberOf', 'displayName']);
    
    if (!$search_result) {
        error_log('Error: Fallo en la búsqueda LDAP para ' . $user);
        ldap_close($ldapconn);
        return false;
    }

    $entries = ldap_get_entries($ldapconn, $search_result);
    if ($entries['count'] == 1) {
        ldap_close($ldapconn);
        return $entries[0]; // Retorna los detalles del usuario, incluyendo displayName y memberOf
    }

    error_log('Error: No se encontró el usuario o se encontraron múltiples entradas: ' . $user);
    ldap_close($ldapconn);
    return false;
}
?>