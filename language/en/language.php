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
    'LOGIN_EXPLAIN_CHAT'	=> 'You must login to chat',
    'CHAT_BANNED'			=> 'Access denied.',
    'CHAT_BLOCKED'			=> 'You are blocked. Block will end after %1$s sec.',
    'USER_JOINED'			=> 'Login:',
    'USER_LEFT'				=> 'Logout:',
    'SECONDS'				=> 'sec.',
    'NOW_IN_CHAT'			=> 'Now in chat:',
    'N_MESSAGES'			=> 'Messages',
    'N_UPDATES'				=> 'Reloads',
    'COPYRIGHT'             => '&copy; by Infants, 2015',

    'INFCHAT_NEW_TOPIC'     => 'Made new topic',
    'INFCHAT_NEW_REPLY'     => 'Replied',
    'INFCHAT_NEW_QUOTE'     => 'Replied with quote',
    'INFCHAT_NEW_EDIT'      => 'Edited',
    'INFCHAT_IN'            => 'in',
    'INFCHAT_IN_SECTION'    => 'forum'
));
