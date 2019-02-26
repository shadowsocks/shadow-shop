![logo](https://github.com/shadowsocks/shadow-shop/blob/master/screen_shots/logo.png =48x48)

# Shadow Shop
A website selling shadowsocks services, using Wordpress and WooCommerce.

## Environment
Mainstream Linux distributions are supported. Mac OS and Windows are not supported yet.

## Initialization

1. Download:
    ```
    cd ~
    git clone https://github.com/shadowsocks/shadow-shop.git
    ```

2. Initialize:
    ```
    cd ~/shadow-shop
    sudo sh init.sh
    ```
    During this step, you have to specify the `state` and `doamin name` of your website. You may choose to run your website either in `development` or `production` state. Then you need input the `domain name` to be used by the your website. 
    
    Before proceeding to this step, if you plan to choose `production` state, then you have to make sure that the `domain name` you are going to input indeed points to the IP address of the server that is running this software. Otherwise, the sofeware will NOT be able to get a valid digital certificate for you website later on.

## Website Maintainence 

1. Create and run website:
    ```
    cd ~/shadow-shop
    docker-compose up -d
    ```

2. Stop website:
    ```
    cd ~/shadow-shop
    docker-compose stop
    ```

3. Start previously stopped website:
    ```
    cd ~/shadow-shop
    docker-compose start
    ```

4. Restart website:
    ```
    cd ~/shadow-shop
    docker-compose restart
    ```

5. Stop and remove website:
    ```
    cd ~/shadow-shop
    docker-compose down
    ```

6. Show website running status:
    ```
    cd ~/shadow-shop
    docker-compose ps
    ```

## Install Wordpress

Once you have brought up your website for the first time, you may install Wordpress by typing in a web browser the domain name that your provided during the `Initialize` step. 

If you selected `development` state during the `Initialize` step, the website digital certificate is self-signed. Thus your website will show you a warning. You have to accept the digital certificate before proceeding.

Then the famous Wordpress 5-minute install process will guide you to customize the installation of Wordpress as you like.

1. Select the language you want to use;
2. Enter your site details: the site title, admin username, password and email address. If you want search engines, such as Google, to find your site, leave the Privacy box unchecked.

## Setup WooCommerce

Now you can log in to your site by clicking the **Log In** button and entering the admin credentials you provided when you were installing WordPress. Then you may go to its admin panel by typing in the web browser the url:
  ```
  https://your-domain-name/wp-admin
  ```
After that, you may perform any admin tasks, including setting up and configuring WooCommerce, in the admin panel. Official documentation for configuring WooCommerce settings can be found [here](https://docs.woocommerce.com/document/configuring-woocommerce-settings/).

## Theme and Customization

By default, the theme StoreFront is installed and activiated as the active theme. You may choose a different theme as you wish. However it is highly recommanded to choose a WooCommerce-compatable theme.

Once you have chosen a theme, you may start to customize how your website looks as you wish.

## Plugins

There are two compulsory plugins - WooCommerce and Shadow Shop - for this website to function properly. They are installed and activiated by default. Do not deactiviate or delete them, or the website will malfunction. If you are not familiar with WooCommerce before, then you might need a bit of googling.

You may install and activiate other plugins as you wish to customize your website functionality. In effect, WooCommerce may automatically install and activiate a few plugins for you during the previous configuration step, such as Jetpack, Mailchimp, and etc.

If you are familiar with Wordpress, then you should be comforatble with finding and using plugins that will help to meet your requierments.

If you are new to Wordpress, then there might be a learning curve. But in all honesty, they are fairly intuitive and easy to learn.

## Products (IMPORTANT)

Once you have setup WooCommerce, you may start to create your products. It is IMPORTANT that for EVERY product, you have to choose one and only one value for each of following attributes: `Life Span`, `Traffic`, and `Encryption Method`. It is IMPORTANT that failure to comply will result in no creation of shadowsocks account for paid orders. 

The `Life Span` attribute of a product specifies how long the shadowsocks accounts created for the product will last for. The options are: `Monthly`, `Bimonthly`, `Quarterly`, `Semiannually`, `Annually`. Once a shadowsocks account has reached its life span, it will be stopped and deleted.

The `Traffic` attribute of a product specifies the maximum number of bits that a user can consume by using the product. The traffic is calculated as the sum of the usages from all the shadowsocks acccounts created for the product for the user. The options are: `100M`, `200M`, `300M`, `400M`, `500M`, `600M`, `700M`, `800M`, `900M`, `1G`, `2G`, `3G`, `4G`, `5G`, `6G`, `7G`, `8G`, `9G`, `10G`, `20G`, `30G`, `40G`, `50G`, `60G`, `70G`, `80G`, `90G`, `100G`, `200G`, `300G`, `400G`, `500G`, `600G`, `700G`, `800G`, `900G`, `1T`, `2T`, `3T`, `4T`, `5T`, `6T`, `7T`, `8T`, `9T`, `10T`. Once a product has reached its life span, unused traffic will NOT be carried forward to the next product. It will simply be discarded.

The `Encryption Method` attribute of a product specifies the default encryption algorithm to be used by all the shadowsocks accounts created for this account. The options are: `aes-128-gcm`, `aes-192-gcm`, `aes-256-gcm`, `aes-128-cfb`, `aes-192-cfb`, `aes-256-cfb`, `aes-128-ctr`, `aes-192-ctr`, `aes-256-ctr`, `camellia-128-cfb`, `camellia-192-cfb`, `camellia-256-cfb`, `bf-cfb`, `chacha20-ietf-poly1305`, `xchacha20-ietf-poly1305`, `salsa20`, `chacha20`, `chacha20-ietf`.

## Shadowsocks Management 

Management of shadowsocks related resources can be done from the `Shadow Shop` menu of the admin panel. 

1. Servers
    A `server` is a remote machine acting as a shadowsocks exit point. A `server` may have more than one `node` (see below). Admin has to provide its IP address or domain name when adding a `server`. Admin may add, edit, and delete a `server`. Note that a `server` cannot be deleted if a `node` has been created using this `server`. Admin has to delete all its `nodes` before successfully deleting the `server`.

2. Nodes
    A `node` is a virtual shadowsocks exit point. The difference between `server` and `node` is that a `server` is an indpendent machine where a `node` is a logical machine that relies on `server`. There can be multiple `nodes` residing on a single `server`. From users' perspective, `nodes` using the same `server` are different shadowsocks exit points. Before adding a `node`, the underlying `server` has to be added into the system first. When adding a new `node`, the admin has to select a server, give it a descriptive name, and provide its managing port, managing password, and a range of port numbers to be used in a form of lower and upper bound of port numbers. Admin may also edit and delete a `node`. Note that a `node` cannot be deleted if an `account` has been created using this `node`. Admin has to delete all its `accounts` before successfully deleting the `node`.

    Shadow Shop uses [shadowsocks-restful-api](https://github.com/shadowsocks/shadowsocks-restful-api) to manage nodes. Install it on every server acting as a node. The managing port number and managing password chosen for [shadowsocks-restful-api](https://github.com/shadowsocks/shadowsocks-restful-api) for a node have to be consistent with the managing port number and managing password fields for the node in Shadow Shop, so that Shadow Shop will be able to control the node. Otherwise, Shadow Shop will not be able to manage the node. 

3. Accounts
    An `account` is a shadowsocks account. The admin may manually add a new `account` by selecting a user, a node, the life span of this account, the ecryption method to be used by this account, and inputing the maximum traffic to be allocated to this account.

## Orders

When a user has made a payment for a product, an order will be created. The system then will create a shadowsocks account from every existing node for the user for this order.

Note the system currently does not backtrack previous orders. When a new node is added into the system, no new shadowsocks account will be created from this new node for previously created orders.

## Guest Users

In order to support guest user payment, go to **WooCommerce** > **Settings** > **Accounts & Privacy**, then configure the settings as the following screen shot:

![account_configuration](https://github.com/shadowsocks/shadow-shop/blob/master/screen_shots/account_configuration.png)

## Languages

Despite the fact that Wordpress and WooCommerce support many languages, Shadow Shop currently supports only English. In the near future, multi-language support for Shadow Shop will be brought to life.

## Update and Backup

The system will perform daily automatic update of code for Wordpress core files, the theme StoreFront, and the plugins WooCommerce and Shadow Shop. Ohter themes and plugins installed by users have to be updated by yourself from the Wordpress admin panel.

The system will perform daily local backup of all the code. In addition, when the website is up running, the system will perform daily local backup of the entire database. 

## Bug Report, Feature Request and Feedback

Please do not hesitate to raise an issue if you would like make a feature request, bug report or provide some feedback. Bugs have a high priority to get addressed while feedback and feature requests will be considered depending on their popularity and importance.
