<?php
/**
 * Created by PhpStorm.
 * User: Atis
 * Date: 26.06.2015.
 * Time: 19:44
 */
namespace infants\infchat\controller;

class main
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

    public function __construct(\phpbb\auth\auth $auth,
                                \phpbb\config\config $config,
                                \phpbb\db\driver\factory $db,
                                \phpbb\controller\helper $helper,
                                \phpbb\template\template $template,
                                \phpbb\user $user,
                                $table_prefix, $root_path, $php_ext)
    {
        $this->auth = $auth;
        $this->config = $config;
        $this->db = $db;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->table_prefix = $table_prefix;
        $this->root_path = $root_path;
        $this->php_ext   = $php_ext;
    }

    public function ajax() // Currently not in use. Future mind
    {
        global $request;
        if (!$request->is_ajax()) die("Ajax only");
        $request->header('cache-control', 'no-cache, must-revalidate');
        $request->header('Content-Type', 'application/json');
        return $this->helper->render('result.js', 'Ajax result');
    }

    public function handle()
    {
        header("cache-control: no-cache, must-revalidate");
        // Define tables
        define('CHAT_MESSAGES_TABLE', $this->table_prefix . 'infchat_messages');
        define('CHAT_SESSIONS_TABLE', $this->table_prefix . 'infchat_sessions');

        // Settings
        define('SESSION_LIFE', 180);            // Session lifetime
        define('MESSAGES_LIMIT', 200);            // Store messages limit
        define('JOIN_MESSAGES', false);            // Display join messages
        define('LEFT_MESSAGES', false);            // Display left messages
        define('ANTIFLOOD_SENSITIVITY', 8);                // Antiflood sensitivity (less is more sensitive)
        define('ANTIFLOOD_EXTINCTION', 3);                // Antiflood extinction (less is faster)
        define('ANTIFLOOD_DURATION', 15);            // Antiflood ban duration in seconds
        define('BUILD_TIME', filemtime(__FILE__));        // Internal version

        // Statuses (currently unused) TODO Maybe try to wake up this thing?
        define('STATUS_ONLINE', 0); // Online
        define('STATUS_CHAT', 1);   // Chat with me!
        define('STATUS_AWAY', 2);   // Away
        define('STATUS_DND', 3);    // Do not disturb

        // Actions
        define('ACT_LOAD', 'load'); // Rāda lapu html
        define('ACT_SYNC', 'sync'); // Dabū datus javascript
        define('ACT_SAY', 'say');   // Postē tekstu javascript

        // Special messages
        define('MSG_JOIN', '/hello');
        define('MSG_LEFT', '/bye');


        $action = request_var('action', ACT_LOAD); // ACT_LOAD kā defaultais
        header('content-type: text/' . (($action == ACT_LOAD) ? 'html' : 'javascript') . '; charset=UTF-8');

        $this->user->add_lang_ext('infants/infchat', 'language');

        // Check internal version
        if ($action != ACT_LOAD && request_var('build', BUILD_TIME) != BUILD_TIME) {
            echo('FullReset();');
            exit;
        }

        $full_page = request_var('full_page', 'no');
        if ($full_page == 'yes')
        {
            die('Error');
        }

        // Auth check
        if (!$this->user->data['is_registered']) {
            if ($action != ACT_LOAD) {
                echo('FullReset();');
            } else {
                $this->template->assign_var('COPYRIGHT', $this->user->lang['POWERED_BY']);
                login_box('index.php', $this->user->lang['LOGIN_EXPLAIN_CHAT']);
            }
            exit;
        }

        /*
        // Ban check (unused)
        if($chat_session['user_status']==STATUS_BANNED)
        {
            if($action!=ACT_LOAD) echo('FullReset();');
            else trigger_error($user->lang['CHAT_BANNED']);
            exit;
        }
        */

        // Detect left users
        $die_time = time() - SESSION_LIFE;

        if (LEFT_MESSAGES) {
            $sql = "SELECT *
		FROM " . CHAT_SESSIONS_TABLE . "
		WHERE last_active < '{$die_time}'
		ORDER BY last_active";
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
                // Add message that user is left
                $message = array(
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                    'time' => $row['last_active'] + SESSION_LIFE,
                    'text' => MSG_LEFT,
                    'color' => '000000'
                );
                $sql = "INSERT INTO " . CHAT_MESSAGES_TABLE . " " . $this->db->sql_build_array('INSERT', $message);
                $this->db->sql_query($sql);
            }
        }

        // Remove left users
        $sql = "DELETE
	        FROM " . CHAT_SESSIONS_TABLE . "
	        WHERE last_active < '{$die_time}'";
        $this->db->sql_query($sql);

        // Create new or prolong old session
        $sql = 'SELECT *
	      FROM ' . CHAT_SESSIONS_TABLE . '
	      WHERE user_id = ' . (int)$this->user->data['user_id'];
        $result = $this->db->sql_query($sql);
        $chat_session = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        if (!$chat_session) {
            // Add new user if needed
            $chat_session = array(
                'user_id' => $this->user->data['user_id'],
                'username' => $this->user->data['username'],
                'last_active' => time(), // if user is banned - time to unban, time of the last message if else
                'user_status' => STATUS_ONLINE,
                'user_activity' => 0,
                'user_blocked' => 0,
                'user_colour'  => $this->user->data['user_colour']
            );
            $sql = 'INSERT INTO ' . CHAT_SESSIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $chat_session);
            $this->db->sql_query($sql);

            if (JOIN_MESSAGES) {
                // Add message that new user is joined
                $message = array(
                    'user_id' => $this->user->data['user_id'],
                    'username' => $this->user->data['username'],
                    'user_color' => $this->user->data['user_colour'],
                    'time' => time(),
                    'text' => MSG_JOIN,
                    'color' => '000000'
                );
                $sql = "INSERT INTO " . CHAT_MESSAGES_TABLE . " " . $this->db->sql_build_array('INSERT', $message);
                $this->db->sql_query($sql);
            }
        } else {
            // Update user activity time and antiflood ban necessity detection
            $chat_session['user_activity'] -= time() - $chat_session['last_active'];
            if ($chat_session['user_activity'] < 0) $chat_session['user_activity'] = 0;
            if (!$chat_session['user_blocked'] && $action == ACT_SAY) {
                $chat_session['user_activity'] += ANTIFLOOD_EXTINCTION;
                if ($chat_session['user_activity'] > ANTIFLOOD_SENSITIVITY) {
                    $chat_session['user_activity'] = ANTIFLOOD_DURATION;
                    $chat_session['user_blocked'] = 1;
                }
            }
            if ($chat_session['user_activity'] == 0) {
                $chat_session['user_blocked'] = 0;
            }
            $chat_session['last_active'] = time();
            $sql = 'UPDATE ' . CHAT_SESSIONS_TABLE . '
		SET ' . $this->db->sql_build_array('UPDATE', $chat_session) . '
		WHERE user_id = ' . $this->user->data['user_id'];
            $this->db->sql_query($sql);
        }

        // Handle commands
        switch ($action) {
            // Load chat body
            case ACT_LOAD:
                require($this->root_path . 'includes/functions_posting.' . $this->php_ext);
                page_header($this->user->lang['CHAT']);

                $this->template->set_filenames(array('body' => 'chat_body.html'));

                $this->template->assign_vars(array(
                    'COPYRIGHT' => $this->user->lang['POWERED_BY'],
                    'BUILD_TIME' => BUILD_TIME
                ));
                generate_smilies("inline", false);
                page_footer();
                exit;

            // Add new message
            case ACT_SAY:

                // Ja jūzeris bloķēts
                if ($chat_session['user_blocked']) {
                    $time = date("H:i", time());
                    $name = "";
                    $text = sprintf($this->user->lang['CHAT_BLOCKED'], $chat_session['user_activity']);
                    echo("LogMessage(0,'$time','$name','$text','000000');\n");
                    exit;
                }

                $text = trim(utf8_normalize_nfc(request_var('text', '', true)));

                /* // Words longer than 70 symbols are not allowed
                $data = explode(' ', $text);
                $text = "";
                foreach($data as $word)
                {
                    if(utf8_strlen($word) > 70) $word = utf8_substr($word, 0, 70);
                    $text .= $word . " ";
                }
                $text = trim($text); */

                // Messages longer than 255 symbols are not allowed
                if (utf8_strlen($text) > 255) $text = utf8_substr($text, 0, 255);
                $color = request_var('color', '000000');
                if (!preg_match('#^[0-9a-f]{6}$#', $color)) $color = '000000';

                if ($text != '') {
                    $uid = $bitfield = $options = '';
                    $allow_bbcode = $allow_urls = $allow_smilies = true;
                    generate_text_for_storage($text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

                    $sql_ary = array(
                        'user_id'          => $this->user->data['user_id'],
                        'username'         => $this->user->data['username'],
                        'user_colour'      => $this->user->data['user_colour'],
                        'time'             => time(),
                        'text'             => $text,
                        'bbcode_uid'       => $uid,
                        'bbcode_bitfield'  => $bitfield,
                        'enable_bbcode'    => $allow_bbcode,
                        'enable_magic_url' => $allow_urls,
                        'enable_smilies'   => $allow_smilies,
                        'color'            => $color
                    );
                    $sql = "INSERT INTO " . CHAT_MESSAGES_TABLE . " " . $this->db->sql_build_array('INSERT', $sql_ary);
                    $this->db->sql_query($sql);
                }
                exit;

            // Chat sync
            case ACT_SYNC:
                // Users list
                $sql = "SELECT user_id as id, username as name, user_status as status, user_colour as color FROM " . CHAT_SESSIONS_TABLE; // . " WHERE status != " . STATUS_HIDDEN;
                $json = json_encode($this->db->sql_fetchrowset($this->db->sql_query($sql)));
                echo("SetUsers($json);\n");

                // Output new messages
                $last_id = request_var('lastid', 0); // No kurienes šito ņem???
                $sql = "SELECT *
			      FROM " . CHAT_MESSAGES_TABLE . "
			      WHERE msg_id > " . $last_id . "
			      ORDER BY msg_id";
                $result = $this->db->sql_query($sql);
                while ($row = $this->db->sql_fetchrow($result)) {
                    if ($row['msg_id'] > $last_id) $last_id = $row['msg_id'];
                    $msg_id = $row['msg_id'];
                    $username = addslashes($row['username']);
                    $user_colour = $row['user_colour'];
                    $user_id = $row['user_id'];
                    $color = addslashes($row['color']);
                    $time = addslashes($this->user->format_date($row['time'], "H:i", true));

                    $text             = trim($row['text']);
                    $uid              = $row['bbcode_uid'];
                    $bitfield         = $row['bbcode_bitfield'];
                    $enable_bbcode    = ($row['enable_bbcode']) ? OPTION_FLAG_BBCODE : 0;
                    $enable_magic_url = ($row['enable_magic_url']) ? OPTION_FLAG_LINKS : 0;
                    $enable_smilies   = ($row['enable_smilies']) ? OPTION_FLAG_SMILIES : 0;
                    $row['bbcode_options'] = $enable_bbcode + $enable_smilies + $enable_magic_url;

                    // Ja enablēti, iepeisto 'Ienāca', 'Izgāja'.
                    if ($text == MSG_JOIN) {
                        echo("LogUserJoin($msg_id, '$time', '$username');\n");
                        continue;
                    }
                    if ($text == MSG_LEFT) {
                        echo("LogUserLeft($msg_id, '$time', '$username');\n");
                        continue;
                    }

                    // Handle private messages. JĀPAPĒTA!! TODO
                    $show = true;
                    if (utf8_substr($text, 0, utf8_strlen("private [")) == "private [") {
                        $show = false;
                        $tmp = $text;
                        while (utf8_strpos($tmp, "private [") === 0) {
                            $endp = utf8_strpos($tmp, "]");
                            $to = str_replace("private [", "", utf8_substr($tmp, 0, $endp));
                            if ($to == $this->user->data['username']) $show = true;
                            $tmp = trim(utf8_substr($tmp, $endp + 1));
                        }
                        $msgpriv = trim(utf8_substr($text, 0, utf8_strlen($text) - utf8_strlen($tmp)));
                        $text = "<span class=\"private\">" . $msgpriv . "</span> " . $tmp;
                    }

                    if ((!$show) && ($this->user->data['username'] != $row['username'])) continue;

                    // Parse smilies and links in the message
                    if (utf8_strlen($text) > 1) {

                        $text = generate_text_for_display($text, $uid, $bitfield, $row['bbcode_options']);

                        $text = str_replace("<a ", "<a target='_blank' ", $text);
                        //$text = str_replace("{SMILIES_PATH}", "$this->root_path . $this->config['smilies_path']", $text);

                    }

                    $text = str_replace("to [" . $this->user->data['username'] . "]", "<span class=\"to\">-> [" . $this->user->data['username'] . "]</span>", $text);
                    $text = addslashes(str_replace(array("\r", "\n"), ' ', $text));

                    echo("LogMessage($msg_id, '$time', '$username', '$user_colour', '$text', '$color');\n");
                }
                echo("SetLastId($last_id);\n");

                // Delete obsolete messages
                $sql = "DELETE FROM " . CHAT_MESSAGES_TABLE . " WHERE msg_id < " . ($last_id - MESSAGES_LIMIT);
                $this->db->sql_query($sql);
                exit;
        }
    }
}