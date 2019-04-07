<script type = "text/javascript">
        google.charts.load('current', {packages: ['corechart']});     
</script>
<?
$accounts = Shadowsocks_Hub_Account_Service::get_accounts_for_current_user();
if (is_wp_error($accounts)) {
        $error_message = $accounts->get_error_message(); ?>
        <div class="error">
                <ul>
                        <?php echo "<li>$error_message</li>\n"; ?>
                </ul>
        </div>
        <?php
        die();
}

if ( empty($accounts) ) { ?>
        <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php _e( 'Go shop', 'woocommerce' ) ?>
		</a>
	</div>
        <?php
        die();
}

function get_purchase_from_account($account) {
        return $account['purchase'];
};

$purchases =  array_map("get_purchase_from_account", $accounts);

// remove possible duplicates
$purchases = array_map("unserialize", array_unique(array_map("serialize", $purchases)));

$user_id = get_current_user_id();
$data_array = array(
        "uiType" => "wordpress",
        "userId" => $user_id,
    );

$return = Shadowsocks_Hub_Helper::call_api("GET", "http://sshub/api/traffic/user", $data_array);

$error = $return['error'];
$http_code = $return['http_code'];
$response = $return['body'];

if ($http_code === 200) {
        $accountUsages = $response;
} elseif ($http_code === 500) {
        $error_message = "Backend system error (getLatestUsageByUserId)";
} elseif ($error) {
        $error_message = "Backend system error: " . $error;
} else {
        $error_message = "Backend system error undetected error.";
};

if ($http_code !== 200) { ?>
        <div class="error">
                <ul>
                        <?php echo "<li>$error_message</li>\n"; ?>
                </ul>
        </div>
        <?php
        die();
};

foreach ($purchases as $purchase) {

        $purchaseAccounts = array();
        foreach($accounts as $account) {
                if ($account['purchase']['id'] === $purchase['id']) {
                        $purchaseAccounts[] = $account;
                }
        };

        $id = str_replace("-", "", $purchase['id']);
        ?>

        <div id = "container<?php echo $id; ?>" style = "width: 100%; height: 400px; margin: 0 0">
        </div>
      <script type = "text/javascript">
             
        function pieChart<?php echo $id;?>() {
            // Define the chart to be drawn.
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Account');
            data.addColumn('number', 'Percentage');
            data.addColumn({type: 'string', role: 'tooltip'});
            data.addRows([
                <?php
                $left = $purchase['traffic'];
                foreach ($purchaseAccounts as $account) {
                        foreach ($accountUsages as $accountUsage) {
                                $usage = 0;
                                if ($accountUsage['accountId'] === $account['id']) {
                                        $usage = $accountUsage['usage'];
                                        break;
                                };
                        $left = $left - $usage;
                        $tooltip = $account['node']['server']['ipAddressOrDomainName'] . ":" . $account['port'];
                        echo "['" . $account['node']['server']['ipAddressOrDomainName'] . ":" . $account['port'] . "', " . $usage . ", '". $tooltip . "'],";
                        };
                };
                echo "['Remaining', " . $left . ", 'remaining'],";
                ?>
            ]);

            <?php 
            $total = $purchase['traffic'];
            if ($total >= 1000 * 1000 * 1000 * 1000) {
                    $totalWithUnit = round(($total / (1000 * 1000 * 1000 * 1000)), 1) . "T";
            } elseif ($total >= 1000 * 1000 * 1000) {
                $totalWithUnit = round(($total / (1000 * 1000 * 1000)), 0) . "G";
            } elseif ($total >= 1000 * 1000) {
                $totalWithUnit = round(($total / (1000 * 1000)), 0) . "M";
            } elseif ($total >= 1000) {
                $totalWithUnit = round(($total / (1000)), 0) . "K";
            } else {
                $totalWithUnit = $total . "B";
            };
            ?>
               
            // Set chart options
            var options = {
                    'title': '<?php echo "Total: "; 
                                echo $totalWithUnit; 
                                ?>',
                    'legend': 'none',
                    'width':'100%', 
                    'height':400,
                    'colors': [
                            <?php
                            foreach ($purchaseAccounts as $account) {
                                    echo "'#e6693e', ";
                            }
                            ?>
                            '#f6c7b6',
                    ]
                };

            // Instantiate and draw the chart.
            var chart = new google.visualization.PieChart(document.getElementById ('container<?php echo $id;?>'));
            chart.draw(data, options);
         };
         google.charts.setOnLoadCallback(pieChart<?php echo $id;?>);
      </script>
<?php
} ?>