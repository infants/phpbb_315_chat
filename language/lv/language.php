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
    'CHAT'					=> 'Čats',
    'COLOR'					=> 'Krāsa',
    'UPDATING'				=> 'Atjauno',
    'SENDING'				=> 'Sūta',
    'SERVER_ERROR'			=> 'Servera kļūda',
    'SOUND'					=> 'Skaņa',
    'SAY'					=> 'Tvaicēt domu',
    'PRIVATE'				=> 'Privāta vēstule',
    'LOGIN_EXPLAIN_CHAT'	=> 'Tev jāienāk, lai lietotu čatu.',
    'CHAT_BANNED'			=> 'Pieeja čatam tev ir liegta.',
    'CHAT_BLOCKED'			=> 'Tu esi bloķēts. Bloks beigsies pēc %1$s sec.',
    'USER_JOINED'			=> 'Ienāca:',
    'USER_LEFT'				=> 'Izgāja:',
    'SECONDS'				=> 'sek.',
    'NOW_IN_CHAT'			=> 'Šobrīd čatā:',
    'N_MESSAGES'			=> 'Ieraksti',
    'N_UPDATES'				=> 'Pārlādes',
    'COPYRIGHT'             => '&copy; by Infants, 2015',

    'INFCHAT_NEW_TOPIC'     => 'Izveidoja jaunu rakstu',
    'INFCHAT_NEW_REPLY'     => 'Atbildēja',
    'INFCHAT_NEW_QUOTE'     => 'Atbildēja, iekļaujot',
    'INFCHAT_NEW_EDIT'      => 'Laboja rakstu',
    'INFCHAT_IN'            => 'in',
    'INFCHAT_IN_SECTION'    => 'forumā'
));
