<?php
namespace infants\infchat\migrations;

class release_0_0_1 extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_column_exists($this->table_prefix . 'infchat_sessions', 'username');
    }

    public function update_schema()
    {
        return array(
            'add_tables' => array(
                $this->table_prefix . 'infchat_sessions' => array(
                    'COLUMNS' => array(
                        'user_id' => array('UINT', null, 'auto_increment'),
                        'username' => array('VCHAR:100', ''),
                        'last_active' => array('UINT:16', ''),
                        'user_status' => array('UINT:8', ''),
                        'user_activity' => array('UINT:8', ''),
                        'user_blocked' => array('UINT:8', ''),
                        'user_colour' => array('VCHAR:6', '000000')
                    ),
                    'PRIMARY_KEY' => 'user_id'
                ),
                $this->table_prefix . 'infchat_messages' => array(
                    'COLUMNS' => array(
                        'msg_id' => array('UINT', null, 'auto_increment'),
                        'user_id' => array('UINT:10', ''),
                        'username' => array('VCHAR:100', ''),
                        'user_colour' => array('VCHAR:6', '000000'),
                        'time' => array('UINT:16', ''),
                        'text' => array('MTEXT_UNI', ''),
                        'bbcode_uid' => array('MTEXT_UNI', ''),
                        'bbcode_bitfield' => array('MTEXT_UNI', ''),
                        'enable_bbcode' => array('UINT', ''),
                        'enable_magic_url' => array('UINT', ''),
                        'enable_smilies' => array('UINT', ''),
                        'color' => array('VCHAR:6', '000000')
                    )
                )
            )
        );
    }

    public function revert_schema()
    {
        return array(
            'drop_tables' => array(
                $this->table_prefix . 'infchat_messages',
                $this->table_prefix . 'infchat_sessions'
            )
        );
    }
}