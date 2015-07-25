<?php

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

$lang = array_merge($lang, array(
    'CHAT'					=> 'Chat',
    'COLOR'					=> 'Color',
    'UPDATING'				=> 'Updating',
    'SENDING'				=> 'Sending',
    'SERVER_ERROR'			=> 'Server error',
    'SOUND'					=> 'Sound',
    'SAY'					=> 'Say',
    'PRIVATE'				=> 'Private',
    'LOGIN_EXPLAIN_CHAT'	=> 'Tev jāienāk, lai lietotu čatu.',
    'CHAT_BANNED'			=> 'Pieeja čatam tev ir liegta.',
    'CHAT_BLOCKED'			=> 'Tu esi bloķēts. Bloks beigsies pēc %1$s sec.',
    'USER_JOINED'			=> 'Ienāca:',
    'USER_LEFT'				=> 'Izgāja:',
    'SECONDS'				=> 'sek.',
    'NOW_IN_CHAT'			=> 'Čato:',
    'N_MESSAGES'			=> 'Vēstules',
    'N_UPDATES'				=> 'jaunumi',
    'POWERED_BY'            => 'Infants, 2015'
));
