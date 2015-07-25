<?php
/**
 * Created by PhpStorm.
 * User: Atis
 * Date: 28.06.2015.
 * Time: 10:24
 */
namespace infants\infchat\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    protected $auth;
    protected $config;
    protected $db;
    protected $helper;
    protected $template;
    protected $user;
    protected $table_prefix;
    protected $root_path;
    protected $php_ext;

    public function __construct(\phpbb\auth\auth            $auth,
                                \phpbb\config\config        $config,
                                \phpbb\db\driver\factory    $db,
                                \phpbb\controller\helper    $helper,
                                \phpbb\template\template    $template,
                                \phpbb\user                 $user,
                                $table_prefix, $root_path, $php_ext)
    {
        $this->auth         = $auth;
        $this->config       = $config;
        $this->db           = $db;
        $this->helper       = $helper;
        $this->template     = $template;
        $this->user         = $user;
        $this->table_prefix = $table_prefix;
        $this->root_path    = $root_path;
        $this->php_ext      = $php_ext;
    }

    static public function getSubscribedEvents()
    {
        return array(
            'core.user_setup'                       => 'load_language_on_setup',
            'core.posting_modify_submit_post_after' => 'posting_modify_submit_post_after'
        );
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'infants/infchat',
            'lang_set' => 'language'
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function posting_modify_submit_post_after($event)
    {
        define('ROBOT', 'Vapeforums.lv');
        define('CHAT_MESSAGES_TABLE', $this->table_prefix . 'infchat_messages');
        define('CHAT_SESSIONS_TABLE', $this->table_prefix . 'infchat_sessions');

        // only trigger if mode is post
        $infchat_forums_allowed = array();
        if ($event['mode'] == 'post' ||
            $event['mode'] == 'reply' ||
            $event['mode'] == 'quote'||
            $event['mode'] == 'edit')
        {

            if ($event['mode'] == 'post')
            {
                $infchat_new_data = $this->user->lang['INFCHAT_NEW_TOPIC'];
            }
            else if ($event['mode'] == 'quote')
            {
                $infchat_new_data = $this->user->lang['INFCHAT_NEW_QUOTE'];
            }
            else if ($event['mode'] == 'edit')
            {
                $infchat_new_data = $this->user->lang['INFCHAT_NEW_EDIT'];
            }
            else if ($event['mode'] == 'reply')
            {
                $infchat_new_data = $this->user->lang['INFCHAT_NEW_REPLY'];
            }
            else
            {
                return;
            }

            // Data...
            $message =
                $this->user->data['username'] . ' ' .
                $infchat_new_data . ': [url=' . generate_board_url() .
                '/viewtopic.' . $this->php_ext . '?p=' . $event['data']['post_id'] . '#p' .
                $event['data']['post_id'] . ']' . $event['post_data']['post_subject'] . '[/url] ' .
                $this->user->lang['INFCHAT_IN_SECTION'] . ': [url=' . generate_board_url() .
                '/viewforum.' . $this->php_ext . '?f=' . $event['forum_id'] . ']' .
                $event['post_data']['forum_name']  . ' [/url] ';

            $uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
            generate_text_for_storage($message, $uid, $bitfield, $options, true, false, false);
            $sql_ary = array(
                'user_id'		 => 'null',
                'username'       => ROBOT,
                'user_colour'    => '336600',
                'time'	         => time(),
                'text'   		 => $message,
                'bbcode_uid'	 => $uid,
                'bbcode_bitfield'=> $bitfield,
                'enable_bbcode'  => true,
                'enable_magic_url' => false,
                'enable_smilies'   => false,
                'color'          => '333333'
            );
            $sql = 'INSERT INTO ' . CHAT_MESSAGES_TABLE  . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
            $this->db->sql_query($sql);
        }

    }

}

// EOF