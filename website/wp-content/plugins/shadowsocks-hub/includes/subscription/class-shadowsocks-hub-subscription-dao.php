<?php
class Shadowsocks_Hub_Subscription_Dao
{
    /**
     * @return WP_Error|shadowsocks_account_array
     */
    static public function get_shadowsocks_accounts($id)
    {
        $user_id = Shadowsocks_Hub_Subscription_Dao::get_user_id_by_subscription_id($id);

        if (!is_null($user_id)) {
            return Shadowsocks_Hub_Account_Dao::get_accounts_by_user_id($user_id);
        }

        return array();
    }

    /**
     * @return null|subscription_id
     */
    static public function get_subscription_id_by_user_id($user_id)
    {
        global $wpdb;

        $result = $wpdb->get_results(
            'SELECT id FROM ' . $wpdb->prefix . 'sshub_subscription' .
                ' WHERE userId=' . '"' . $user_id . '"'
        );

        if (sizeof($result) == 1) {
            return $result[0]->id;
        } else {
            return null;
        }
    }

    /**
     * @return NULL|userId
     */
    static public function get_user_id_by_subscription_id($id)
    {
        global $wpdb;

        $result = $wpdb->get_results(
            'SELECT userId FROM ' . $wpdb->prefix . 'sshub_subscription' .
                ' WHERE id=' . '"' . $id . '"'
        );

        if (sizeof($result) == 1) {
            return $result[0]->userId;
        } else {
            return null;
        }
    }

    static public function create_or_update_subscription($subscription)
    {
        $id = $subscription['id'];
        $userId = $subscription['user_id'];
        $createdTime = $subscription['created_time'];

        global $wpdb;

        $existing_subscription = $wpdb->get_results(
            'SELECT * FROM ' . $wpdb->prefix . 'sshub_subscription' .
                ' WHERE userId=' . $userId
        );

        if (sizeof($existing_subscription) == 1) {
            $return = $wpdb->update(
                $wpdb->prefix . 'sshub_subscription',
                array(
                    'id' => $id,
                    'userId' => $userId,
                    'createdTime' => $createdTime
                ),
                array(
                    'userId' => $userId
                )
            );
        } else {
            $return = $wpdb->insert(
                $wpdb->prefix . 'sshub_subscription',
                array(
                    'id' => $id,
                    'userId' => $userId,
                    'createdTime' => $createdTime
                )
            );
        }
        return $return;
    }
}
