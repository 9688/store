<?php
define ( 'SEPARATOR', '\\' );
define ( 'ROOT', dirname ( __FILE__ ) . SEPARATOR );
define ( 'APLICATION_ROOT', ROOT );
define ( 'TEMPLATES_ROOT', 'templates' . SEPARATOR );
define ( 'TEMPLATING_ROOT', 'system' . SEPARATOR );
define ( 'CONTENTS_OF_THE_CLASS_NAME', 'A-Za-z0-9' );
define ( 'CONTENTS_OF_THE_PARAM_NAME', 'A-Za-z0-9_' );
define ( 'CONTENTS_OF_THE_SEPARATOR', '_|-|\.' );
define ( 'POSTFIX_CONTROLLER_NAME', 'Controller' );
define ( 'POSTFIX_ACTION_NAME', 'Action' );

define ( 'DEBUG', TRUE );
define ( 'ERROR_LOG_FILE', ROOT.'logs.txt');

define ( 'NAME_PROJECT', 'store' );

define ( 'DB_HOST' , 'localhost' );
define ( 'DB_USERNAME', 'root' );
define ( 'DB_PASSWORD', '');
define ( 'DB_NAME', 'STORE' );
define ( 'DB_TYPE', 'mysql' );


define ( 'MEDIA_ROOT', ROOT.'/media' );
define ( 'MEDIA_URL' , '/media');
define ( 'AVATAR_DIR', '/avatars' );
define ( 'AVATAR_URL', MEDIA_URL.'/avatars');

define ( 'MAX_SIZE_AVATAR', 20 );
define ( 'DEFAULT_AVATAR', '');

define ( 'IMAGE_DIR', '/images' );
define ( 'IMAGE_URL', MEDIA_URL.'/images' );
define ( 'DEFAULT_IMAGE', 'no-image.jpg' );

define ( 'HTTP_404', '/error_404');

