<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 28/07/2017
 * Time: 11:44
 */

namespace CrisaSoft\FabCMS;

class shop
{
    public $cartItems;
    public $cartPrice;
    public $cartID;

    public $config = [];

    function __construct()
    {
        global $db;
        global $core;
        global $user;
        global $relog;

        $this->loadConfig();

        // Check if a cookie is set but the cart is closed (status = 1). If the cart is present unset the cookie!
        if (isset($_COOKIE['cart_ID']) && $this->getCartStatus((int) $_COOKIE['cart_ID']) === 1) {
            $relog->write(['type'      => '3',
                           'module'    => 'SHOP',
                           'operation' => 'shop_construct_validate_cart',
                           'details'   => 'Cart (ID: ' . (int) $_COOKIE['cart_ID'] . ') looks closed but still persists. Removing cookies',
                          ]);

            setcookie('cart_ID', '0', time() - 60 * 24, '/');
            setcookie('cart_secure_hash', '0', time() - 60 * 24, '/');

        }

        // If user is logged check for custom values
        if ($user->logged){

            $query = 'SELECT * FROM ' . $db->prefix . 'shop_users_customizations WHERE user_ID = ' . $user->ID . ' 
                      LIMIT 1';

            

            if (!$result = $db->query($query)){
                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_construct_get_anonymous',
                               'details'   => 'Query error while getting anonymous cart. ' . $query,
                              ]);
                echo 'Unable to fetch user customizations. ' . $query;
                return;
            }

            if ($db->affected_rows){
                $row = mysqli_fetch_assoc($result);

                $this->config['customHeader']       = $row['custom_header'];
                $this->config['customFooter']       = $row['custom_footer'];
                $this->config['globalDiscount']     = $row['global_discount'];
                $this->config['instantCheckout']    = $row['instant_checkout'];
            }

        }

        if ($user->logged && isset($_COOKIE['cart_ID']) && isset($_COOKIE['cart_secure_hash'])) {
            $query = 'UPDATE ' . $db->prefix . 'shop_carts 
                      SET anonymous_hash = NULL,
                          user_ID = ' . $user->ID . ' 
                      WHERE ID = ' . (int) $_COOKIE['cart_ID'] . '
                         AND anonymous_hash = \'' . $core->in($_COOKIE['cart_secure_hash'], true) . '\'
                      LIMIT 1;';

            
            if (!$db->query($query)) {

                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_construct_get_anonymous',
                               'details'   => 'Query error while getting anonymous cart. ' . $query,
                              ]);

                echo 'Query error. ' . $query;
            } else {
                setcookie('cart_secure_hash', 0, time() . 60 * 60);
                setcookie('cart_ID', 0, time() . 60 * 60);
            }

        }

        $this->updateCartStatus();
    }

    private function loadConfig()
    {
        global $db;
        global $core;
        global $relog;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'shop_config 
                  WHERE lang = \'' . $core->shortCodeLang . '\';';

        

        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_load_config',
                           'details'   => 'Cannot load config. ' . $query,
                          ]);

            die ('Unable to load Shop config. Aborting.');
        }

        if (!$db->affected_rows) {
            $relog->write(['type'      => '3',
                           'module'    => 'SHOP',
                           'operation' => 'shop_load_config_missing',
                           'details'   => 'Configuration is missing. ' . $query,
                          ]);

            die ("Generic error. Shop configuration is missing. FabCMS looks like corrupted. Please reinstall FabCMS or set required fields in the DB.");
        }

        while ($row = mysqli_fetch_array($result)) {
            $this->config[$row['param']] = $row['value'];
        }

    }

    function getCartStatus(int $ID): int
    {
        global $db;
        global $conf;
        global $relog;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'shop_carts 
                  WHERE ID = ' . $ID . '
                  LIMIT 1';

        

        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_get_cart_status',
                           'details'   => 'Query error while getting cart status. ' . $query,
                          ]);

            return -1;
        }

        if (!$db->affected_rows)
            return 0;

        $row = mysqli_fetch_assoc($result);

        return (int) $row['status'];
    }

    function updateCartStatus()
    {
        global $core;
        global $user;
        global $ID;
        global $db;
        global $relog;

        if ($user->logged || (isset($_COOKIE['cart_ID']) && isset($_COOKIE['cart_secure_hash']))) {

            $query = 'UPDATE ' . $db->prefix . 'shop_carts 
                          SET has_global_discount = ' . ( $this->config['globalDiscount'] > 0 ? '1' : 'null' ) .'
                          WHERE status = 0 
                          AND ' . ($user->logged === true
                                  ? 'user_ID = ' . $user->ID
                                  : ' anonymous_hash = \'' . $core->in($_COOKIE['cart_secure_hash'], true) . '\''
                                 ) . ' 
                          LIMIT 1;';

            

            if (!$db->query($query)){
                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_update_cart_global_discount_error',
                               'details'   => 'Query error while updating cart status. ' . $query,
                              ]);

                die("Query error while updating global discount." . $query);
                return -1;
            }

            $query = '
            SELECT ' . $db->prefix . 'shop_carts.latest_update AS latest_update,
                SUM(' . $db->prefix . 'shop_cart_items.item_qty) AS total_qty,
                SUM(' . $db->prefix . 'shop_cart_items.final_price) AS final_price
            FROM ' . $db->prefix . 'shop_carts
            LEFT OUTER JOIN ' . $db->prefix . 'shop_cart_items
                ON ' . $db->prefix . 'shop_carts.ID = ' . $db->prefix . 'shop_cart_items.cart_ID
            WHERE ' . $db->prefix . 'shop_carts.status = 0 
                AND ' . ($user->logged === true
                    ? 'user_ID = ' . $user->ID
                    : ' anonymous_hash = \'' . $core->in($_COOKIE['cart_secure_hash'], true) . '\''
                ) . '
            GROUP BY ' . $db->prefix . 'shop_carts.latest_update';

            

            if (!$result = $db->query($query)) {
                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_update_cart_status',
                               'details'   => 'Query error while updating cart status. ' . $query,
                              ]);

                return -1;
            }

            if (!$db->affected_rows)
                return -2;


            $row = mysqli_fetch_assoc($result);

            $this->cartItems = $row['total_qty'];
            $this->cartPrice = $row['final_price'];
        }

    }

    /**
     * @param int $cart_ID
     *
     * @return bool
     */
    public function processTriggers(int $cart_ID): bool
    {
        global $query;
        global $relog;
        global $db;

        // ===========================
        // 1) Check if cart_ID exists
        // ===========================

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'shop_carts AS CART
                  WHERE CART.ID = ' . $cart_ID;

        

        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_ipn_triggers_select_cart_error',
                           'details'   => 'Query error while selecting cart. ' . $query,
                          ]);

            return false;
        }

        if (!$db->affected_rows) {
            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_ipn_triggers_select_cart_no_cart',
                           'details'   => 'No cart found while selecting carts for triggering. ' . $query,
                          ]);

            return true;
        }


        // 2) Loop through the items and collect the triggers, then execture them
        return true;
    }

    public function getFilesByArticleID($ID, $type = 0)
    {
        global $db;
        global $relog;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'shop_item_files AS F 
                  WHERE 
                    F.item_ID = ' . $ID . ' 
                    AND F.type = ' . $type . ' 
                  ORDER BY F.ordering ASC;';

        
        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_uget_files_by_article_ID',
                           'details'   => 'Query error while getting files from article ID. ' . $query,
                          ]);

            return -1;
        }

        if (!$db->affected_rows)
            return -2;

        $fileList = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $fileList[$row['file']] = $row['name'];
        }

        return $fileList;
    }

    public function addDownload($file)
    {
        global $db;
        global $core;
        global $user;
        global $relog;

        $file = $core->in($file);

        $query = 'INSERT INTO 
                 ' . $db->prefix . 'shop_downloads
                 (
                    user_ID,
                    file,
                    IP,
                    download_date
                 ) VALUES
                 (
                 \'' . $user->ID . '\',
                 \'' . $file . '\',
                 \'' . $_SERVER['REMOTE_ADDR'] . '\',
                 NOW()
                 )
                 ';

        

        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_add_download',
                           'details'   => 'Unable to add a download. ' . $query,
                          ]);


            return false;
        } else {
            return true;
        }
    }

    public function updateDbCartStatus($options)
    {
        global $core;
        global $db;
        global $user;
        global $log;
        global $relog;

        $query = 'UPDATE ' . $db->prefix . 'shop_carts 
                  SET status    = ' . ((int) $options['status']) . ',
                  latest_update = NOW()
                  WHERE ID      = ' . ( (int) $options['ID']) . ' LIMIT 1;';

        

        if (!$db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_update_db_cart_status',
                           'details'   => 'Unable to update DB cart. ' . $query,
                          ]);

            $log->write('shop', 'update_cart_error', 'Unable to update cart. ' . $query);
        } else {
            if (!$db->affected_rows) {
                $log->write('shop', 'update_cart_no_rows', 'Unable to update cart. No rows.  ' . $query);
            } else {
                $log->write('shop', 'update_cart_success', 'Cart was updated. ' . $query);
            }
        }
    }

    /**
     * Check if an item is inside a cart, then returns the cart ID and the quantity of the item.
     *
     * @param $item_ID
     * @param $user_ID
     *
     * @return array|bool
     */
    function findCartByItem($item_ID, $user_ID)
    {
        global $db;
        global $core;
        global $user;
        global $relog;

        $item_ID = (int) $user_ID;

        $query = 'SELECT ID 
                  FROM ' . $db->prefix . 'shop_cart_items
                  WHERE item_ID = ' . $item_ID . ';';
        

        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_find_cart_by_item',
                           'details'   => 'Query error while finding an item inside the cart. ' . $query,
                          ]);

            echo 'Query error in findCartByItem. ';

            return false;
        }

        if (!$db->affected_rows)
            return false;

        $row = mysqli_fetch_assoc($result);

        return ['ID' => $row['ID'], 'QTY' => $row['item_qty']];
    }

    function addToCart($item_ID, $qty = 1)
    {
        global $user;
        global $db;
        global $relog;


        $item_ID = (int) $item_ID;

        if ($item_ID === 0)
            return false;

        // Get the item
        $query = 'SELECT I.*,
                    V.value AS vat_value
                  FROM ' . $db->prefix . 'shop_items AS I
                  LEFT JOIN ' . $db->prefix . 'shop_vat AS V
                  ON I.vat_ID = V.ID
                  WHERE I.ID = ' . $item_ID . '
                  AND I.enabled = 1
                  LIMIT 1;';


        
        if (!$resultItem = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_add_to_cart',
                           'details'   => 'Query error while adding item to cart. ' . $query,
                          ]);

            echo 'Query error in addToCart. Unable to select the article';

            return -1;
        }

        if (!$db->affected_rows) {
            echo 'Item was not found. Query is  ' . $query;

            return -2;
        }

        $rowArticle = mysqli_fetch_assoc($resultItem);

        echo 'Stackable flag is: ' .  $rowArticle['is_stackable'] . PHP_EOL;

        if ($user->logged === true) {
            $cart_ID = $this->userHasCart($user->ID);
            echo 'Cart status logged is: ' . print_r($cart_ID, true) . PHP_EOL;
        } else {
            $cart_ID = $this->anonymousHasCart();
            echo 'Cart status anonymous is: ' . print_r($cart_ID, true) . PHP_EOL;
        }

        if ($cart_ID > 0) {
            echo 'User has a cart (' . $cart_ID . ')' . PHP_EOL;

            $this->cartID = $cart_ID;

            // Check if the cart has the item
            $cartStatus = $this->cartHasItem($item_ID, $cart_ID);
            echo 'Cart status is ' . $cartStatus . PHP_EOL;

            if ($cartStatus === -1) // Query error
                return false;

            if (isset($cartStatus['QTY']) && (int) $rowArticle['is_stackable'] === 1) {
                echo 'An item exists in the cart and it is stackable' . PHP_EOL;

                $query = 'UPDATE ' . $db->prefix . 'shop_cart_items 
                              SET item_qty = item_qty + ' . $qty . '
                              WHERE cart_ID = ' . $cart_ID . ' LIMIT 1';

                
                if (!$db->query($query)) {

                    $relog->write(['type'      => '4',
                                   'module'    => 'SHOP',
                                   'operation' => 'shop_add_to_cart_update_items',
                                   'details'   => 'Query error while updating cart items. ' . $query,
                                  ]);

                    echo '<pre> Query error while updating item in the cart</pre>' . PHP_EOL;

                    return false;
                }

                if (!$db->affected_rows) {

                    $relog->write(['type'      => '3',
                                   'module'    => 'SHOP',
                                   'operation' => 'shop_add_to_cart_update_items_no_rows',
                                   'details'   => 'No rows while updating cart on items. ' . $query,
                                  ]);

                    echo '<pre>No rows</pre>';

                    return;
                }

                echo 'Calling update cart values from addToCart() 1. Cart ID is ' . $cart_ID . '<br/>';

                $this->updateCartValues($cart_ID);
                $this->updateCartStatus();

            } else {
                echo 'Item is not present in the cart. Adding.';

                $query = 'INSERT INTO ' . $db->prefix . 'shop_cart_items 
                              (cart_ID, 
                               item_ID, 
                               item_QTY,
                               add_date) 
                              VALUES (
                                ' . $cart_ID . ',
                                ' . $item_ID . ',
                                ' . $qty . ',
                                NOW()
                              );';

                

                if (!$db->query($query)) {
                    $relog->write(['type'      => '4',
                                   'module'    => 'SHOP',
                                   'operation' => 'shop_update_cart_add_item',
                                   'details'   => 'Query error while inserting new item. ' . $query,
                                  ]);

                    echo 'Query error while inserting new item. ' . $query . PHP_EOL;

                    return false;
                }

                $line_ID = $dbinsert_id;

                echo 'Calling update cart values from addToCart() 2<br/>';

                $this->updateCartValues($cart_ID);
                $this->cartInsertAdditionalInfo($line_ID);

            }

        } else {

            if ($user->logged) {

                // Create a new cart
                $query = 'INSERT INTO ' . $db->prefix . 'shop_carts 
                          (
                            user_ID, 
                            status, 
                            start_date
                          )
                          VALUES 
                          (' . $user->ID . ', 
                            0,
                            NOW()
                          );';
            } else {
                $secureHash = md5(time() . 'allyourbasearebelongtous'); // For the great justice!

                // Create a new "anonymous" cart
                $query = 'INSERT INTO ' . $db->prefix . 'shop_carts 
                          (
                            user_ID, 
                            anonymous_hash, 
                            status, 
                            start_date
                          )
                          VALUES 
                          (  0,
                            \'' . $secureHash . '\', 
                            0,
                            NOW()
                          );';
            }

            
            if (!$db->query($query)) {

                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_create_new_cart',
                               'details'   => 'Query error while creating new cart. ' . $query,
                              ]);

                echo 'Query error while creating new cart. ' ;
                echo $query;

                return false;
            }

            $cart_ID = $dbinsert_id;
            $this->cartID = $cart_ID;

            echo 'Cart ID is ' . $cart_ID . PHP_EOL;

            if (!$user->logged) {
                // Set the cookoes
                if (!setcookie('cart_ID', $cart_ID, time() + 60 * 60 * 24 * 30, '/')) {

                    $relog->write(['type'      => '4',
                                   'module'    => 'SHOP',
                                   'operation' => 'shop_create_new_cart_cookie_error',
                                   'details'   => 'Unable to create the cookie.',
                                  ]);

                    echo 'Unable to create the cookie ID. <br/>';
                }

                if (!setcookie('cart_secure_hash', $secureHash, time() + 60 * 60 * 24 * 30, '/')) {
                    echo 'Unable to create the secure hash. <br/>';
                }
            }

            if (isset($this->config['globalDiscount']) && (int) $this->config['globalDiscount'] > 0) {
                $rowArticle['final_price'] = $rowArticle['final_price'] - ($rowArticle['final_price'] / 100 )* $this->config['globalDiscount'];
                $rowArticle['vat_amount'] = $rowArticle['final_price'] / 100 * $rowArticle['vat_value'];
                $rowArticle['net_price'] = $rowArticle['vat_amount'];
            }

            $query = 'INSERT INTO ' . $db->prefix . 'shop_cart_items
                          (
                              cart_ID,
                              item_ID,
                              item_qty,
                              public_price,
                              discount_1,
                              discount_2,
                              discount_3,
                              global_discount,
                              final_price,
                              net_price,
                              cost_price,
                              vat_ID,
                              vat_amount,
                              is_promo
                          )
                          VALUES
                          (
                              ' . $cart_ID . ',
                              ' . $item_ID . ',
                              ' . $qty . ',
                              ' . $rowArticle['public_price'] . ',
                              ' . (!is_null($rowArticle['discount_1']) ? $rowArticle['discount_1'] : 'null') . ',
                              ' . (!is_null($rowArticle['discount_2']) ? $rowArticle['discount_2'] : 'null') . ',
                              ' . (!is_null($rowArticle['discount_3']) ? $rowArticle['discount_3'] : 'null') . ',
                              ' . (!is_null($this->config['globalDiscount']) ? $this->config['globalDiscount'] : 'null') . ',
                              ' . ($rowArticle['final_price'] ?? 0) . ',
                              ' . ($rowArticle['net_price'] ?? 0) . ',
                              ' . ($rowArticle['cost_price'] ?? 0) . ',
                              ' . ($rowArticle['vat_ID'] ?? 0) . ',
                              ' . ($rowArticle['vat_amount'] ?? 0) . ',
                              ' . (!is_null($rowArticle['is_promo']) ? $rowArticle['is_promo'] : 'null') . '
                          )
                          ;';

            echo "\r\n ********** Inserting the line \r\n $query \r\n ***********";

            // Adds the line
            
            if (!$db->query($query)) {

                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_update_add_items',
                               'details'   => 'Query error while inserting items. ' . $query,
                              ]);

                echo 'Query error while inserting. ' . $query;

                return;
            }

            $this->updateCartValues($cart_ID);
            $this->cartInsertAdditionalInfo($dbinsert_id);
            $this->updateDbCartStatus($cart_ID);
            echo 'Item was inserted into the cart.' . PHP_EOL;

            return true;

        }

    }


    /**Check if user has any cart. If yes, returns the ID of the cart
     *
     * @param $user_ID
     *
     * @return bool
     */
    function userHasCart($user_ID)
    {
        global $core;
        global $db;
        global $relog;

        $user_ID = (int) $user_ID;

        $query = 'SELECT * 
                  FROM ' . $db->prefix . 'shop_carts 
                  WHERE user_ID = ' . $user_ID . ' 
                  AND status = 0
                  LIMIT 1';

        

        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_user_has_cart',
                           'details'   => 'Query error while checking if user has a cart. ' . $query,
                          ]);

            return -1;
        }

        if (!$db->affected_rows)
            return -2;

        $row = mysqli_fetch_assoc($result);

        return $row['ID'];
    }

    function anonymousHasCart()
    {
        global $db;
        global $core;
        global $relog;

        if (isset($_COOKIE['cart_ID']) && isset($_COOKIE['cart_secure_hash'])) {
            $cart_ID = (int) $_COOKIE['cart_ID'];
            $cart_secure_hash = $core->in($_COOKIE['cart_secure_hash']);

            // Check if cart exists in the db
            $query = 'SELECT * 
                      FROM ' . $db->prefix . 'shop_carts 
                      WHERE ID = ' . $cart_ID . ' AND anonymous_hash = \'' . $cart_secure_hash . '\';';

            
            if (!$result = $db->query($query)) {

                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_anonymous_has_cart',
                               'details'   => 'Query error while checking if anonymous has a cart. ' . $query,
                              ]);

                echo 'Query error. ' . PHP_EOL;

                return -1;
            }


            if (!$db->affected_rows) {
                // Cart doesn't exists so we have to remove the cookies
                setcookie('cart_ID', '0', time() - 60 * 24);
                setcookie('cart_secure_hash', '0', time() - 60 * 24);

                return -2;
            }

            return (int) $_COOKIE['cart_ID'];
        } else {

            return -2;
        }
    }

    /**
     * Check if an item is inside a cart, then returns the cart ID and the quantity of the item.
     *
     * @param $item_ID
     * @param $user_ID
     *
     * @return array|bool
     */
    function cartHasItem($item_ID, $cart_ID)
    {
        global $db;
        global $core;
        global $user;
        global $relog;

        $item_ID = (int) $item_ID;
        $cart_ID = (int) $cart_ID;

        $query = 'SELECT item_qty 
                  FROM ' . $db->prefix . 'shop_cart_items
                  WHERE item_ID = ' . $item_ID . ' AND cart_ID = ' . $cart_ID;

        

        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_cart_has_item',
                           'details'   => 'Query error while checking if a cart has an item. ' . $query,
                          ]);

            return -1;
        }


        if (!$db->affected_rows)
            return -2;

        $row = mysqli_fetch_assoc($result);

        return ['QTY' => $row['item_qty']];
    }

    function cartInsertAdditionalInfo(int $line_ID)
    {
        global $core;
        global $db;
        global $relog;

        echo 'Checking for additional info for line ID: ' . $line_ID . PHP_EOL;
        echo 'POST is ' . print_r($_POST, 1) . ' and has ' . count($_POST) . ' entries' . PHP_EOL;

        $queryInsert = '';
        reset($_POST);
        for ($i = 0; $i < count($_POST); $i++) {
            $key = key($_POST);
            $key = explode('_', $key);

            echo 'Key is ' . ((int) $key[0]) . PHP_EOL;

            if ($key[0] === 'custom') {
                $queryInsert .= '(' . $line_ID . ', ' . ((int) $key[1]) . ',  \'' . $core->in(current($_POST)) . '\'), ';
            }


            next($_POST);

        }

        if ($queryInsert === '') {
            echo 'No additional data!';

            return;
        }

        $queryInsert = substr($queryInsert, 0, -2);

        $query = 'INSERT INTO ' . $db->prefix . 'shop_cart_items_additional_info 
                    (cart_item_ID, 
                    item_additional_ID, 
                    value) 
                    
                    VALUES ' . $queryInsert;

        

        if (!$db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_update_cart_add_item',
                           'details'   => 'Unable to insert additional infos. ' . $query,
                          ]);

            echo 'Query error while inserting new item. ' . $query . PHP_EOL;

            return false;
        }



    }

    function getCartValues($cart_ID = null)
    {
        global $db;
        global $user;
        global $core;
        global $relog;

        if (!is_null($cart_ID))
            $cart_ID = (int) $cart_ID;

        $query = 'SELECT I.* 
                    FROM ' . $db->prefix . 'shop_carts AS C
                    LEFT JOIN ' . $db->prefix . 'shop_cart_items AS I
                    ON I.cart_ID = C.ID
                    WHERE 
                    C.status = 0
                    ' . (!is_null($cart_ID) ? ' AND C.ID = ' . $cart_ID : '') . ' 
                    ' . ($user->logged === true ? ' AND C.user_ID = ' . $user->ID . '' : '');

        

        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_get_cart_values',
                           'details'   => 'Query error while getting cart values. ' . $query,
                          ]);

            return ['error' => 1, 'query' => $query];

        }

        if (!$db->affected_rows)
            return ['error' => 2];

        $totalTaxable = 0;
        $totalVat = 0;
        $totalNet = 0;

        $i = 0;

        while ($row = mysqli_fetch_assoc($result)) {

            $gross = (float) $row['public_price'];
            $discount_1 = (int) $row['discount_1'];
            $discount_2 = (int) $row['discount_2'];
            $discount_3 = (int) $row['discount_3'];

            $taxable = $gross;

            if ($discount_1 > 0)
                $taxable = $taxable - ($taxable / 100 * $discount_1);

            if ($discount_2 > 0)
                $taxable = $taxable - ($taxable / 100 * $discount_2);

            if ($discount_3 > 0)
                $taxable = $taxable - ($taxable / 100 * $discount_3);


            $taxable = round($taxable, 2);

            $totalTaxable += $taxable;

            $totalVat += $row['vat_amount'];
            $totalNet += $row['net'];

            $i++;
        }

        return [
            'taxable' => $totalTaxable,
            'vat'     => $totalVat,
            'net'     => $totalNet,
            'items'   => $i,
        ];
    }

    function getItemDataByIDTrackback($article_ID, $trackback)
    {
        global $db;
        global $conf;
        global $core;
        global $relog;

        $article_ID = (int) $article_ID;
        $trackback = $core->in($trackback);

        $query = 'SELECT I.ID,
                         I.master_ID,
                         I.lang,
                         I.final_price,
                         I.tags,
                         I.vat_amount,
                         I.public_price,
                         I.title,
                         I.net_price,
                         I.vat_ID,
                         I.short_description,
                         I.description,
                         I.product_image,
                         I.enabled,
                         I.dismissed,
                         V.code AS vat_code,
                         V.value AS vat_value
                  FROM ' . $db->prefix . 'shop_items AS I
                  
                  LEFT JOIN ' . $db->prefix . 'shop_vat AS V
                  ON I.vat_ID = V.ID
                  
                  WHERE I.ID = ' . $article_ID . '
                  AND I.trackback = \'' . $trackback . '\'
                  AND I.lang = \'' . $core->shortCodeLang . '\' 
                  LIMIT 1';

        

        if (!$result = $db->query($query)) {
            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_get_item_data_by_ID_trackback',
                           'details'   => 'Query error while getting data from ID and trackback. ' . $query,
                          ]);

            return -1;
        }

        if (!$db->affected_rows) {
            return -2;
        }


        return mysqli_fetch_assoc($result);
    }


    /**
     * @param int $cart_ID
     *
     * @return int
     */
    function updateCartValues($cart_ID)
    {
        global $db;
        global $core;
        global $relog;

        $cart_ID = (int) $cart_ID;

        $query = 'SELECT CI.ID,
                         CI.item_qty,
                         I.public_price,
                         I.discount_1,
                         I.discount_2,
                         I.discount_3,
                         I.final_price,
                         I.net_price,
                         I.is_promo,
                         I.vat_amount,
                         V.ID AS vat_ID,
                         V.value AS vat_value
                  FROM ' . $db->prefix . 'shop_cart_items AS CI
                  LEFT JOIN ' . $db->prefix . 'shop_items AS I
                    ON CI.item_ID = I.ID
                  LEFT JOIN ' . $db->prefix . 'shop_vat AS V
                    ON I.vat_ID = V.ID
                  WHERE CI.cart_ID = ' . $cart_ID;

        

        // echo $query . '<br>' . PHP_EOL;
        if (!$result = $db->query($query)) {

            $relog->write(['type'      => '4',
                           'module'    => 'SHOP',
                           'operation' => 'shop_update_cart_values',
                           'details'   => 'Query error while updating cart values. ' . $query,
                          ]);


            return -1;
        }

        if (!$db->affected_rows) {
            echo 'Cart is empty. ' . PHP_EOL;
            return -2;
        }

        $itemsToBeDeleted = [];

        while ($rowItem = mysqli_fetch_assoc($result)) {

            if ((int) $rowItem['item_qty'] < 1) {
                $itemsToBeDeleted[] = $rowItem['ID'];
            }

            $public_price = $rowItem['public_price'];
            $public_price = $public_price * $rowItem['item_qty'];

            // Compute the taxable
            $taxable = $rowItem['public_price'];

            if ($rowItem['discount_1'] > 0)
                $taxable = $taxable - ($taxable / 100) * $rowItem['discount_1'];

            if ($rowItem['discount_2'] > 0)
                $taxable = $taxable - ($taxable / 100) * $rowItem['discount_2'];

            if ($rowItem['discount_3'] > 0)
                $taxable = $taxable - ($taxable / 100) * $rowItem['discount_3'];

            /*
            echo '*************** ' . "\r\n";
            echo 'Item ID is: ' . $rowItem['ID'] . "\r\n";
            echo 'Taxable is: ' . $taxable . '<br/>' . "\r\n";
            echo 'QTY IS: ' . $rowItem['item_qty'] . '<br/>' . "\r\n";
            echo 'Global discount IS: ' . $this->config['globalDiscount'] . "\r\n";

            echo '*************** ' . "\r\n";
            */
            if ( $this->config['globalDiscount'] > 0)
            {
                $final_price    =   ($taxable     - (( $taxable / 100) * $this->config['globalDiscount'] )) * $rowItem['item_qty'];
                $vat_amount     =  (( $taxable / 100) * $rowItem['vat_value']) * $rowItem['item_qty'];
                $net_price      =   $final_price - $vat_amount;
            } else {

                $final_price = $taxable * $rowItem['item_qty'];

                $vat_amount     =   (( $taxable / 100) * $rowItem['vat_value']) * $rowItem['item_qty'];
                $net_price      =   $final_price - $vat_amount;
            }


            /*
            echo "\r\nFinal price is " . $final_price;
            echo "\r\nVat amount is "  . $vat_amount;
            echo "\r\nNet amount is "  . $net_price;
            */
            $query = '
            UPDATE ' . $db->prefix . 'shop_cart_items 
            SET
            
            public_price      = ' . $public_price . ',
            discount_1 = ' . (is_null($rowItem['discount_1']) ? 'null' : $rowItem['discount_1']) . ',
            discount_2 = ' . (is_null($rowItem['discount_2']) ? 'null' : $rowItem['discount_2']) . ',
            discount_3 = ' . (is_null($rowItem['discount_3']) ? 'null' : $rowItem['discount_3']) . ',
            global_discount = ' . (is_null( $this->config['globalDiscount']) ? 'null' : $this->config['globalDiscount']) . ',
            final_price = ' . $final_price . ',
            net_price =   ' . $net_price . ',
            vat_ID     = ' . $rowItem['vat_ID'] . ',
            vat_amount = ' . $vat_amount . ',
            is_promo = ' . (is_null($rowItem['is_promo']) ? 'null' : $rowItem['is_promo']) . '
            
            WHERE ID = ' . $rowItem['ID'] . ' 
            LIMIT 1';


            
            # echo "\r\n" . $query . "\r\n";
            if (!$db->query($query)) {
                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_update_cart_values_delete',
                               'details'   => 'Query error while updating cart values. ' . $query,
                              ]);


                return -1;

            }

        }

        // Delete items with less than 1 QTY
        if (count($itemsToBeDeleted) > 0) {

            $items = '';
            foreach ($itemsToBeDeleted as $item) {
                $items .= '  ID = ' . $item . ' OR ';
            }
            $items = substr($items, 0, -4);

            $query = 'DELETE 
                      FROM ' . $db->prefix . 'shop_cart_items 
                      WHERE ' . $items;

            
            if (!$db->query($query)) {

                $relog->write(['type'      => '4',
                               'module'    => 'SHOP',
                               'operation' => 'shop_user_has_cart',
                               'details'   => 'Query error while deleting cart items. ' . $query,
                              ]);

                echo 'Query error. ';
            }
        }

    }
}