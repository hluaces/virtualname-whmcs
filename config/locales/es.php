<?php
// *************************************************************************
// * VIRTUALNAME TCPANEL - WHMCS REGISTRAR MODULE
// * PLUGIN Api v1
// * WHMCS version 7.10.X
// * @copyright Copyright (c) 2020, Virtualname
// * @version 1.2.1
// * @link http://whmcs.virtualname.net
// * @package WHMCSModule
// * @subpackage TCpanel
// * @common true
// * File description: VIRTUALNAME ENGLISH CUSTOM LANGS
// *************************************************************************

    if (!defined('WHMCS'))
        die('This file cannot be accessed directly');

    $langs_vn['successSync']            = 'Dominio sincronizado correctamente';
    $langs_vn['errorContactCreated']    = 'Contacto no válido';
    $langs_vn['errorContactNotFound']   = 'Contactos no encontrado';
    $langs_vn['errorAPIKEY']            = 'APIKey inválido. Cámbialo en \'Configuración > Productos/Servicios > Registradores de dominios\' y asigna tu APIKey.';
    $langs_vn['errorModuleInstall']     = 'Instalación del módulo no válida. Instálalo en \'Configuración > Productos/Servicios > Registradores de dominios\' y haz click en \'Instalar Registrador\'.';
    $langs_vn['errorRegisterAvailable'] = 'Este dominio no está disponible para registrar';
    $langs_vn['errorRegisterFree']      = 'Dominios gratuitos de WHMCS no se registran. Puede deshabilitarlo en la configuración de virtualname.';
    $langs_vn['errorRegister']          = 'Error al registrar el dominio. Por favor revisa los logs de registro.';
    $langs_vn['errorRegisterDNS']       = 'Error al registrar el dominio. Las DNS del dominio no existen.';
    $langs_vn['errorTransferAvailable'] = 'Este dominio no está disponible para transferir';
    $langs_vn['errorTransfer']          = 'Error al transferir el dominio. Por favor revisa los logs de registro.';
    $langs_vn['errorRenewSecure']       = 'Periodo de seguridad para renovar: Este dominio no se puede renovar porque fue renovado en las últimas ';
    $langs_vn['errorRenewSecureHour']   = ' horas';
    $langs_vn['errorRenewAvailable']    = 'Este dominio no está disponible para renovar';
    $langs_vn['errorRenewFree']         = 'Dominios gratuitos de WHMCS no se renuevan. Puede deshabilitarlo en la configuración de virtualname.';
    $langs_vn['errorRenewExpiration']   = 'El año de expiración es inválido. Debe coincidir con el año de expiración real del dominio.';
    $langs_vn['errorRenew']             = 'Error al renovar el dominio. Por favor revisa los logs de registro.';
    $langs_vn['module-0'] = 'No es posible conectar con el registrador. Por favor vuelva a intentarlo pasados unos minutos.';
    $langs_vn['module-1'] = 'Respuesta desconocida del registrador';
    $langs_vn['200']  = 'acción exitosa';
    $langs_vn['201']  = 'elemento creado';
    $langs_vn['202']  = 'sincronización exitosa';
    $langs_vn['400']  = 'ERROR 400 Acción fallida';
    $langs_vn['401']  = 'ERROR 401 TOKEN no válido o IP no autorizada';
    $langs_vn['402']  = 'ERROR 402 No hay fondos suficientes';
    $langs_vn['404']  = 'ERROR 404 Elemento no encontrado';
    $langs_vn['422']  = 'ERROR 422 Petición imposible de procesar por favor compruebe los logs';
    $langs_vn['429']  = 'ERROR 429 Límite de peticiones';
    $langs_vn['500']  = 'ERROR 500 Error interno';
    $langs_vn['502']  = 'ERROR 502 Acción desconocida';
    $langs_vn['503']  = 'ERROR 503 Modo mantenimiento';
    $langs_vn['name'] = 'Módulo Administración de Dominios';
    $langs_vn['description'] = 'Módulo Administración de Dominios Virtualname - WHMCS';
    $langs_vn['autoRenew'] = 'Establece la autorrenovación activa al registrar y transferir dominios';
    $langs_vn['hideicnumber'] = 'Ocultar el campo \'Documento Identificativo\' y \'Forma Jurídica\' de los formularios de contacto y cliente';
    $langs_vn['freeRegisterDomains'] = 'No registrar dominios gratuitos';
    $langs_vn['freeRenewDomains'] = 'No renovar dominios gratuitos';
    $langs_vn['secureRenovation'] = 'Número de horas antes de que se pueda volver a renovar un dominio desde WHMCS';
    $langs_vn['templateVersion'] = 'Establece la plantilla que se va a integrar a la vista de cliente';
    $langs_vn['defaultvatnumber'] = 'Establece el campo personalizado del cliente que será utilizado como documento identificativo (en caso de que este se envíe en blanco en cualquiera de los contactos del cliente). <span style=\'color:red\'>Si el anterior campo VAT está marcado este no se aplicará.</span>';
    $langs_vn['disablelocktlds'] = 'Establece que extensiones de dominios van a mostrar un error al intentar ejecutar el bloqueo de transferencias. Para añadir nuevas extensiones introducir la extensión y un espacio como separador entre extensiones.';
    $langs_vn['devMode'] = 'Establece entorno de desarrollo';
    $langs_vn['debug'] = 'Establece el modo debug (se recomienda usar en modo mantenimiento)';
    $langs_vn['errorFileNotFound']      = 'Falta subir archivo';
    $langs_vn['errorIncorrectVersion']  = 'Versión incorrecta del archivo';
    $langs_vn['errorIncorrectPerm1']    = 'Permisos';
    $langs_vn['errorIncorrectPerm2']    = 'incorrectos del archivo';
    $langs_vn['errorIncorrectPerm3']    = 'incorrectos del directorio';
    $langs_vn['errorLinesNotFound']     = 'Faltan las líneas adicionales del archivo';
    $langs_vn['errorModuleVariables']   = 'Falta la variable de configuración';
    $langs_vn['errorModuleVariablesSave']   = 'Guarda la configuración de este módulo para solucionar el error.';
    $langs_vn['moduleVersion'] = 'Versión del módulo';
    $langs_vn['autoRenewField'] = 'Autorenovación';
    $langs_vn['hideicnumberField'] = 'Ocultar número IC';
    $langs_vn['freeRegisterDomainsField'] = 'Registro de dominios gratuitos';
    $langs_vn['freeRenewDomainsField'] = 'Renovación de dominios gratuitos';
    $langs_vn['templateVersionField'] = 'Versión de plantilla';
    $langs_vn['secureRenovationField'] = 'Renovación de seguridad';
    $langs_vn['defaultvatnumberField'] = 'Nº VAT por defecto';
    $langs_vn['disablelocktldsField'] = 'Desactivar bloqueo para TLDs';
    $langs_vn['devModeField'] = 'Modo desarrollo';
    $langs_vn['debugField'] = 'Modo debug';
    $langs_vn['installVirtualname'] = 'Instalar virtualname';
    $langs_vn['updateModule'] = 'Actualizar módulo';
    $langs_vn['checkWarnings'] = 'Advertencias';
    $langs_vn['uninstallconf'] = 'Desinstalar el módulo de Virtualname-Tcpanel?';
    $langs_vn['uninstall'] = 'Desinstalar Registrador';
    $langs_vn['installRegistrar'] = 'Instalar Registrador';
    $langs_vn['updateconf'] = 'Actualizar el módulo de Virtualname-Tcpanel? Esta acción reinstalará el módulo.';
    $langs_vn['updateRegistrar'] = 'Actualizar Módulo Virtualname';
    $langs_vn['updateAvailable'] = 'Hay una actualización disponible para descargar';
    $langs_vn['disableLockError'] = 'Esta extensión de dominio no dispone de bloqueo de registro';
    $langs_vn['filesWriteDisable'] = 'No se puede utilizar la instalación automática por los permisos de usuarios de PHP';
    $langs_vn['filesWriteDisableUpdate'] = 'No se puede utilizar la actualización automática por los permisos de usuarios de PHP';
    $langs_vn['cronoutbound']   = 'Transferencias salientes';
    $langs_vn['cronpending']    = 'Dominios pendientes';
    $langs_vn['crons']          = 'Crons';
    $langs_vn['outboundTransferMailing']      = 'Enviar aviso a los clientes cuando se cancela un dominio que ha sido transferido a otro registrador';
    $langs_vn['outboundTransferMailingField'] = 'Aviso dominios salientes';
    $langs_vn['download']       = 'Descargar';
    $langs_vn['errors']         = 'Registro de Errores';
    $langs_vn['sendErrors']     = 'Enviar a Virtualname';
    $langs_vn['confirmErrors']  = 'Confirma el envío a Virtualname-Desarrollo del registro de errores?';
    $langs_vn['confirm_errors_clean']  = 'Confirma la limpieza del registro de errores?';
    $langs_vn['errors_clean']  = 'Limpiar registro de errores';
    $langs_vn['installTables']       = 'Crear tablas';
    $langs_vn['installTablesErrors'] = 'Faltan algunas de las tablas para el correcto funcionamiento del módulo. Para solucionarlo hago click en \'Crear tablas\'';
    $langs_vn['defaultNameserversError']      = 'Utilizar las DNS por defecto establecidas en caso de que el cliente intente registrar con unas DNS no válidas';
    $langs_vn['defaultNameserversErrorField'] = 'Error DNS';
    $langs_vn['new_module_update'] = 'Nueva actualización disponible.';
    $langs_vn['current_version'] = 'Versión actual';
    $langs_vn['get_update'] = 'Obtener Actualización';
    $langs_vn['hide_popup'] = 'Ocultar popup';
    $langs_vn['domain_status'] = 'Estado del dominio';
    $langs_vn['auto_renew'] = 'Auto renovación';
    $langs_vn['privacy'] = 'Privacidad';
    $langs_vn['protection'] = 'Protección';
    $langs_vn['reg_handle'] = 'Handle registrador';
    $langs_vn['adm_handle'] = 'Handle administrador';
    $langs_vn['bill_handle'] = 'Handle facturación';
    $langs_vn['tech_handle'] = 'Handle técnico';
    $langs_vn['exp_date'] = 'Fecha de expiración';
    $langs_vn['crea_date'] = 'Fecha de creación';
    $langs_vn['domain_data'] = 'Datos del dominio';
    $langs_vn['active'] = 'Activo';
    $langs_vn['inactive'] = 'Inactivo';
    $langs_vn['transfer_waiting_unlocked'] = 'Transferencia pendiente del desbloqueo del dominio';
    $langs_vn['transfer_waiting_admin'] = 'Transferencia de dominio pendiente de aceptacion por el contacto administrativo';
    $langs_vn['transfer_waiting_registrar'] = 'Transferencia de dominio pendiente de liberacion por el registrador';
    $langs_vn['transfer_order_locked'] = 'Transferencia en proceso bloqueada momentaneamente';
    $langs_vn['transfer_waiting_authcode'] = 'Transferencia a la espera de authcode';
    $langs_vn['transfer_email_not_sent'] = 'Transferencia pendiente de enviar el email al contacto administrativo';
    $langs_vn['transfer_resend_authcode'] = 'Reenviar Authcode';
    $langs_vn['transfer_waiting_pending_registrant_approval'] = 'Cambios pendientes de aprobación del titular';
    $langs_vn['outbound_transfer'] = 'Transferencia saliente';
    $langs_vn['expired'] = 'Expirado';
    $langs_vn['redemption'] = 'Periodo de redención';
    $langs_vn['active_pending_registrant_approval'] = 'Activo pendiente de aprobación del titular';
    $langs_vn['pending'] = 'Pendiente';
    $langs_vn['transferring'] = 'Transfiriendo';
    $langs_vn['transfer_requested'] = 'Transferencia solicitada';
    $langs_vn['transfer_initiated'] = 'Transferencia iniciada';
    $langs_vn['transfer_email_sent'] = 'Correo electrónico enviado para la aprobación de la transferencia';
    $langs_vn['transfer_rejected'] = 'Transferencia rechazada';
    $langs_vn['transfer_approved'] = 'Transferencia aceptada';
    $langs_vn['transfer_expired'] = 'Transferencia expirada';
    $langs_vn['paid'] = 'Pagado';
    $langs_vn['legal_form_field'] = 'TCPanel Forma jurídica';
    $langs_vn['identification_number'] = 'TCPanel Nº de Identificación (CIF, DNI, NIE)';
    $langs_vn['legal_form']['natural_person_or_individual'] = 'Persona física o particular';
    $langs_vn['legal_form']['economic_interest_group'] = 'Agrupación de interés económico';
    $langs_vn['legal_form']['association'] = 'Asociación';
    $langs_vn['legal_form']['sports_association'] = 'Asociación deportiva';
    $langs_vn['legal_form']['trade_association'] = 'Asociación gremial';
    $langs_vn['legal_form']['savings'] = 'Caja de ahorros';
    $langs_vn['legal_form']['community_property'] = 'Comunidad de bienes';
    $langs_vn['legal_form']['community_of_owners'] = 'Comunidad de propietarios';
    $langs_vn['legal_form']['congregation_or_religious_institution'] = 'Congregación o institución religiosa';
    $langs_vn['legal_form']['consulate'] = 'Consulado';
    $langs_vn['legal_form']['public_corporation'] = 'Corporación de derecho público';
    $langs_vn['legal_form']['embassy'] = 'Embajada';
    $langs_vn['legal_form']['local_organization'] = 'Entidad local';
    $langs_vn['legal_form']['sports_federation'] = 'Federación deportiva';
    $langs_vn['legal_form']['foundation'] = 'Fundación';
    $langs_vn['legal_form']['mutual_insurance'] = 'Mutua de seguros';
    $langs_vn['legal_form']['organ_of_the_regional_administration'] = 'Órgano de la administración pública';
    $langs_vn['legal_form']['organ_of_the_state_administration'] = 'Órgano de la administración del estado';
    $langs_vn['legal_form']['political_party'] = 'Partido político';
    $langs_vn['legal_form']['union'] = 'Sindicato';
    $langs_vn['legal_form']['agrarian_transformation'] = 'Sociedad agraria de la transformación';
    $langs_vn['legal_form']['corporation'] = 'Sociedad anónima';
    $langs_vn['legal_form']['sports_corporation'] = 'Sociedad anónima deportiva';
    $langs_vn['legal_form']['civil_society'] = 'Sociedad civil';
    $langs_vn['legal_form']['partnership'] = 'Sociedad colectiva';
    $langs_vn['legal_form']['limited_partnership'] = 'Sociedad comanditaria';
    $langs_vn['legal_form']['cooperative_society'] = 'Sociedad cooperativa';
    $langs_vn['legal_form']['labour_society_limited'] = 'Sociedad laboral limitada';
    $langs_vn['legal_form']['limited_society'] = 'Sociedad limitada';
    $langs_vn['legal_form']['branch_in_spain'] = 'Sucursal en España';
    $langs_vn['legal_form']['consortium'] = 'Unión temporal de empresas';
    $langs_vn['legal_form']['education_corporation'] = 'Sociedad anónima laboral';
    $langs_vn['legal_form']['autonomous_public_organization'] = 'Organismo público autonómico';
    $langs_vn['legal_form']['state_public_agency'] = 'Organismo público estatal';
    $langs_vn['legal_form']['local_public_agency'] = 'Organismo público local';
    $langs_vn['legal_form']['other'] = 'Otras';
    $langs_vn['legal_form']['designation_of_origin_control_board'] = 'Consejo regulador de denominación de origen';
    $langs_vn['legal_form']['natural_space_agency_manager'] = 'Organismo gestor de espacio natural';
    $langs_vn['transfer_not_available'] = 'Este dominio no está disponible para transferir';
    $langs_vn['resource_not_found'] = 'Recurso no encontrado';
    $langs_vn['documentation'] = 'Documentación';
    $langs_vn['disableAdvanceContacts']      = 'Advertencia: NO RECOMENDADO. Deshabilita la gestión avanzada de contactos.';
    $langs_vn['disableAdvanceContactsField'] = 'Desactivar gestión avanzada';
    $langs_vn['transfer_on_renewal'] = 'TCPanel: Transferir al renovar';
    $langs_vn['status_transfer_on_renewal'] = 'Estado';
    $langs_vn['type_transfer_on_renewal'] = 'Tipo de transferencia';
    $langs_vn['info_transfer_on_renewal'] = 'Asignar email es válido para agilizar las transferencias de dominios de ESNIC y sin AUTHCODE: .es, .com.es, .org.es, .edu.es';
    $langs_vn['second_info_transfer_on_renewal'] = 'Solo podrán ser transferidos dominios sin bloqueo de transferencia o el bloqueo de 60 días de IRTP';
    $langs_vn['add_contact_error'] = 'Error de contactos: No se puede registrar/transferir este dominio.';
    $langs_vn['registrar'] = 'Contacto registrador';
    $langs_vn['admin'] = 'Contacto administrativo';
    $langs_vn['billing'] = 'Contacto facturación';
    $langs_vn['technical'] = 'Contacto técnico';
    $langs_vn['addDefaultDomainsMail'] = 'Email utilizado por defecto para las transferencias en renovación.';
    $langs_vn['addDefaultDomainsMailField'] = 'Email transferencias';
    $langs_vn['defaultAdminRoles'] = 'Rol de administrador asignado para recibir las notificaciones del módulo de virtualname (crons).';
    $langs_vn['defaultAdminRolesField'] = 'Rol de avisos';
    $langs_vn['launch_transfer'] = 'Lanzar transferencia';
    $langs_vn['empty_authcode'] = 'No es posible obtener el authcode del dominio';
    $langs_vn['error_on_update_mail'] = 'No se puede cambiar el email para transferir. Compruebe los contactos en el registrador actual';
    $langs_vn['error_not_mail_transfer_available'] = 'Este registrador no admite la transferencia por aceptación del correo administrativo';
    $langs_vn['error_not_authcode_transfer_available'] = 'Este registrador no admite la transferencia con authcode';
    $langs_vn['error_not_transfer_available'] = 'Esta transferencia no puede volver a ser ejecutada porque ya está en proceso';
    $langs_vn['unknow_transfer_status'] = 'Estado de transferencia desconocido';
    $langs_vn['in_progress'] = 'en proceso';
    $langs_vn['transfer_on_renewal_active'] = 'Activa';
    $langs_vn['transfer_on_renewal_inactive'] = 'Inactiva';
    $langs_vn['validationNewClient'] = 'No validar los datos de clientes nuevos durante el proceso de alta en caso de que haga un registro o transferencia de dominios';
    $langs_vn['validationNewClientField'] = 'Validar altas';
    $langs_vn['taxidField'] = 'Nº VAT WHMCS';
    $langs_vn['taxid'] = 'Utiliza el campo de cliente/contacto TAX ID (VAT NUMBER) que usa el WHMCS por defecto para la gestión de los documentos identificativos de los contactos. Solo para versiones de WHMCS 7.7 y superiores.';
    $langs_vn['disableContactVerificationField'] = 'Desactivar verificación';
    $langs_vn['disableContactVerification'] = 'Desactivar verificación de datos de contactos y clientes si no están vinculados';
    $langs_vn['enableDomainRecordsField'] = 'Habilitar registros DNS';
    $langs_vn['enableDomainRecords'] = 'Habilitar gestión de registros DNS';
    $langs_vn['error_fields']['phone'] = 'Teléfono';
    $langs_vn['error_fields']['state'] = 'Provincia';
    $langs_vn['error_fields']['name'] = 'Nombre';
    $langs_vn['error_fields']['lastname'] = 'Apellidos';
    $langs_vn['error_fields']['company'] = 'Compañia';
    $langs_vn['error_fields']['ic'] = 'Documento identificativo';
    $langs_vn['error_fields']['email'] = 'Correo';
    $langs_vn['error_fields']['country'] = 'País';
    $langs_vn['error_fields']['name'] = 'Nombre';
    $langs_vn['error_fields']['city'] = 'Ciudad';
    $langs_vn['error_fields']['address'] = 'Dirección';
    $langs_vn['error_fields']['zipcode'] = 'Código postal';
    $langs_vn['error_fields']['phonecc'] = 'Código telefónico';
    $langs_vn['error']['field'] = 'Campo';
?>