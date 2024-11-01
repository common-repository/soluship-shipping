<?php
/**
 * Plugin Name: SOLUSHIP SHIPPING FOR WOOCOMMERCE
 * Plugin URI: https://soluship.com/soluship.action
 * Description: Soluship Shipping a Complete Shipping  For Mulitiple Carriers with best Shipping rates and Shipping Label.
 * Version: 1.2.4
 * Author: justintegrateIT Devlopers
 * Author URI: https://www.justintegrateit.com/
 * Developer: Justintegrate IT Pvt Ltd.
 * Developer URI: https://www.justintegrateit.com/
 * Text Domain: justintegrateit.com
 * Domain Path: /languages
 * Copyright: © 2009-2017 Justintegrate IT Pvt Ltd.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
/**
 * Check if WooCommerce is active
 */
// echo get_home_url(); exit(); $_SERVER['SERVER_NAME']
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

  
if (!function_exists('is_plugin_active') || !function_exists('is_plugin_active_for_network')) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

//if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
if (is_plugin_active('woocommerce/woocommerce.php') || is_plugin_active_for_network('woocommerce/woocommerce.php')) {
    function ssfwc_soluship_shipping_method_init()
    {
        if (!class_exists('WC_Soluship_Shipping_Manager')) {
            class WC_Soluship_Shipping_Manager extends WC_Shipping_Method
            {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct()
                {
                    $this->id                 = 'soluship_shipping_method'; // Id for your shipping method. Should be uunique.
                    $this->method_title       = __('Soluship Shipping Method'); // Title shown in admin
                    $this->method_description = __('Soluship(SOLUtion based SHIPments) is a shipment based project in which service is made globally. We have integrated different shipment service like FEDEX, UPS, DHL, PUROLATOR using SOAP service to get the rates and creating the pickup & Shipment for their corresponding service.'); // Description shown in admin
                    $this->enabled            = "yes"; // This can be added as an setting but for this example its forced enabled
                    $this->title              = "Soluship Shipping"; // This can be added as an setting but for this example its forced.
                    $this->init();
                }
                
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init()
                { 
                    
                    // Load the settings API
                    
                    $this->ssfwc_soluship_init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
                    $this->enabled              = $this->settings['enabled'];
                    $this->solushipaccesstoekn  = $this->settings['solushipaccesstoekn'];
                    $this->domain               = $this->settings['domain'];
                    $this->pack_type            = $this->settings['packaging_soluship'];
                    $this->free_ship_soluship   = $this->settings['free_ship_soluship'];
                    $this->min_amount_free_ship = $this->settings['min_amount_free_ship'];
                    $this->min_weight_free_ship = $this->settings['min_weight_free_ship'];
                    $this->markup_down          = $this->settings['markup_down'];
                    $this->markup_down_type     = $this->settings['markup_down_type'];
                    $this->markup_value         = $this->settings['markup_value'];
                    $this->max_weight           = $this->settings['max_weight'];
                    
                    // Save settings in admin if you have any defined
                    
                    add_action('woocommerce_update_options_shipping_' . $this->id, array(
                        $this,
                        'process_admin_options'
                    ));
                    
                }
                
               
                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping($package = array())
                {
                    if(isset($package['rates']) && !empty($package['rates']) && !isset($package['rates']['soluship_shipping']) && !array_key_exists('soluship_shipping', $package['rates'])) 
                    return;

                    $current_unit = get_option('woocommerce_dimension_unit');
                    $weight_unit  = get_option('woocommerce_weight_unit');
                    $currency     = get_option('woocommerce_currency');
                    $dimunit      = get_option('woocommerce_dimension_unit');
                    
                    global $WOOCS;
                    if (isset($WOOCS)) {
                        $ses = $WOOCS->storage->get_val('woocs_current_currency');
                        
                        if (isset($ses)) {
                            $currency = $ses;
                            
                        }
                        
                    }
                    
                    
                    
                    // $parcelContents = thiswf_weight_only_shipping($package,30);
                    
                    global $woocommerce;
                    $requests = array();
                    $weight   = 0;
                    $value    = 0;
                    
                    // Get weight of order
                    
                    foreach ($package['contents'] as $item_id => $values) {
                        if (!$values['data']->needs_shipping()) {
                            continue;
                        }
                        
                        if (!$values['data']->get_weight()) {
                             continue;
                        }
                        
                        $weight += wc_get_weight($values['data']->get_weight(), 'lbs') * $values['quantity'];
                        $value += $values['data']->get_price() * $values['quantity'];
                    }
                    $this->contents_cost   = $package['contents_cost'];
                    $this->holeWeight      = $weight;
                    $this->shipingPackages = $package;
                    $baseLocation          = wc_get_base_location();
                    $wcc                   = new WC_Countries();
                    
                    $unit                = array(
                        'currency' => $currency,
                        'dimunit' => $dimunit,
                        'weight_unit' => $weight_unit
                    );
                    $this->unit          = $unit;
                    $this->max_weightLBS = wc_get_weight($this->max_weight, 'lbs');
                    $this->baseLocations = $baseLocation;
                    $this->packes        = $this->ssfwc_getpackageDetails($package);


                    // Code for Shipping Zone Filtration Starts
                    global $wpdb;
                    $solushipShippingMethods = [];
                    //$shippingZones = WC_Shipping_Zone_Data_Store::get_zones();
                    $shippingTempZones = new WC_Shipping_Zone_Data_Store();
                    $shippingZones = $shippingTempZones->get_zones();
                    foreach ($shippingZones as $zone) {
                        $shippingTempMethods = new WC_Shipping_Zone_Data_Store();
                        $shippingMethods = $shippingTempMethods->get_methods($zone->zone_id,1);
                        // $shippingMethods = WC_Shipping_Zone_Data_Store::get_methods($zone->zone_id,1);
                        foreach ($shippingMethods as $shippingMethod) {
                            if($shippingMethod->method_id == 'soluship_shipping'){
                                //
                                $shippingMethod->zone_id = $zone->zone_id;
                                $solushipShippingMethods[] = $shippingMethod;

                            }
                        }
                       

                    }

                    foreach ($solushipShippingMethods as $methods) {
                        $raw_methods_sql = "SELECT location_id, zone_id, location_code, location_type FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE zone_id = %d";
        
                        $shippingLocations = $wpdb->get_results( $wpdb->prepare( $raw_methods_sql, $methods->zone_id) ); 
        
                        $solushipShippingCarriers = get_option('woocommerce_soluship_shipping_'.$methods->instance_id.'_settings');
        
        
                        $solushipCarriers = $solushipShippingCarriers['shippingZoneCarrier'];
        
                        foreach ($shippingLocations as $locations) {
                            $locations->carriers = $solushipCarriers;
                        }
        
                        
                        $methods->locations = $shippingLocations;
                        
                    }

                    $this->solushipZones = $solushipShippingMethods;
                    // Code Shipping Zone Filtration Ends
                
                    $json                = json_encode($this);
                    
                    

                    $response = wp_remote_post($this->domain . '/api/v1/genericrates', array(
                        'method' => 'POST',
                        'timeout' => 70,
                        'sslverify' => 0,
                        'headers' => array(
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'xwoocommercedomain' => 'woocommerce_soluship_shipping',
                            'SolushipAccessKey' => $this->solushipaccesstoekn,
                            'SolushipHost' => $_SERVER['SERVER_NAME']
                        ),
                        'body' => $json
                    ));
                    

                    if ( !is_wp_error($response) && isset($response) ) {
                            $ratebody = $response['body'];
                    }else{
                        return;
                    }
                    
                   
                    $obj      = json_decode($ratebody);
                    $ratesar  = array();
                    $ratesar  = $this->ssfwc_sort_methods($obj->rates);
                    
                    $ratesar = $this->ssfwc_apply_markup_ship($ratesar);
                    
                    $ratesar = $this->ssfwc_apply_free_ship($ratesar);
                     $last = 0;
                     if(is_array($ratesar)){
                         $last = count($ratesar); 
                     } 
                    if ($last > 0) {
                        foreach ($ratesar as $key => $value) {
                            $serviceId = 0;
                            $service   = '';
                            $amount    = 0.0;
                            foreach ($value as $key1 => $value1) {
                                
                                if ($key1 == 'amount') {
                                    $amount = $value1;
                                }
                                
                                if ($key1 == 'service') {
                                    $service = $value1;
                                }
                                
                                if ($key1 == 'serviceId') {
                                    $serviceId = $value1;
                                }
                            }
                            
                             
                            $rate1 = array(
                                'id' => $serviceId,
                                'label' => $service,
                                'cost' => $amount,
                                'calc_tax' => 'per_item'
                            );
                            $this->add_rate($rate1);
                        }
                    }
                    
                }
                public function ssfwc_apply_free_ship($rates)
                {
                    if (!$rates)
                        return;
                    $freeship   = $this->free_ship_soluship;
                    $totalcost  = $this->contents_cost;
                    $minamt     = $this->min_amount_free_ship;
                    $minwigt    = $this->min_weight_free_ship;
                    $holeWeight = $this->holeWeight;
                    
                    if ($freeship == 'nil') {
                        return $rates;
                    } else if ($freeship == 'min_amount') { // by total order amount
                        $tmp = Array();
                        
                        
                        $array = json_decode(json_encode($rates), true);
                        foreach ($array as $key => $value) {
                            if ($totalcost >= $minamt) {
                                
                                $array[0]['amount'] = 0;
                               $array[0]['service'] = $array[0]['service']. '(Free Shipping)'; 
                                break;
                            }
                        }
                        
                        return $array;
                        
                        
                    } else if ($freeship == 'min_weight') { // by total order weight
                        $tmp   = Array();
                        $array = json_decode(json_encode($rates), true);
                        foreach ($array as $key => $value) {
                            if ($holeWeight >= $minwigt) {
                                
                                $array[0]['amount'] = 0;
                                
                                break;
                            }
                        }
                        
                        return $array;
                    }
                    
                    
                    return $rates;
                }
                
                
                
                public function ssfwc_apply_markup_ship($rates)
                {
                    if (!$rates)
                        return;
                    $markup_down = $this->markup_down;
                    
                    $totalcost = $this->contents_cost;
                    
                    $markup_down_type = $this->markup_down_type;
                    $markup_value     = $this->markup_value;
                    
                    if ($markup_down == 'nil') {
                        return $rates;
                    } else if ($markup_down == 'markup') { // by markup
                        
                        $array = json_decode(json_encode($rates), true);
                        
                        foreach ($array as $key => $value) {
                            $array[$key]['amount'] = $this->ssfwc_applyMarkup($array[$key]['amount'], $markup_value, $markup_down_type);
                        }
                        
                        return $array;
                        
                    } else if ($markup_down == 'markdown') { // by markup
                        
                        $array = json_decode(json_encode($rates), true);
                        foreach ($array as $key => $value) {
                            $array[$key]['amount'] = $this->ssfwc_applyMarkdown($array[$key]['amount'], $markup_value, $markup_down_type);
                        }
                        
                        return $array;
                    }
                    
                    return $rates;
                }
                

                public function ssfwc_applyMarkdown($cost, $markvalue, $mType)
                {
                    
                    if ($mType == 'flatmark') { //flat rate;
                        $cost = $cost - $markvalue;
                        
                    } else if ($mType == 'percmark') { // percentage markup
                        
                        $new_width = ($markvalue / 100) * $cost;
                        $cost      = $cost - $new_width;
                    }
                    
                    if ($cost > 0) {
                        return $cost;
                    } else {
                        return 0;
                    }
                    
                }
                
                public function ssfwc_applyMarkup($cost, $markvalue, $mType)
                {
                    if ($mType == 'flatmark') { //flat rate;
                        $cost = $cost + $markvalue;
                        
                    } else if ($mType == 'percmark') { // percentage markup
                        
                        $new_width = ($markvalue / 100) * $cost;
                        $cost      = $cost + $new_width;
                    }
                    
                    if ($cost > 0) {
                        return $cost;
                    } else {
                        return 0;
                    }
                    
                }
                
                
                
                public function ssfwc_sort_methods($rates)
                {
                     
                    if (!$rates)
                        return;
                    $cc  = count($rates);
                    $tmp = Array();
                    foreach ($rates as $key => $value) {
                        
                        $tmp[] = $value->amount;
                        
                    }
                    
                    array_multisort($tmp, $rates);
                    return $rates;
                    
                }
                
                
                public function ssfwc_getpackageDetails($package)
                {
                    
                    
                    $packs = array();
                    
                    $_pf = new WC_Product_Factory();
                    foreach ($package['contents'] as $item_id => $values) {
                        
                        if (!$values['data']->needs_shipping()) {
                            continue;
                        }
                        
                        if (!$values['data']->get_weight()) {
                            return;
                        }
                        
                        
                        $weight              = wc_get_weight($values['data']->get_weight(), 'lbs');
                        $productid           = $values['product_id'];
                        $quantity            = $values['quantity'];
                        
                        $product             = wc_get_product($productid);
                        $product->dimensions = $product->get_dimensions();
                        $product->quantity   = $quantity;
                        $product->weightLBS  = $weight;
                        $length=wc_get_dimension( $product->length, 'in' );
                        $width=wc_get_dimension( $product->width, 'in' );
                        $height=wc_get_dimension( $product->height, 'in' );
                        $product->heightIN  = $height;
                        $product->lengthIN  = $length;
                        $product->widthIN  = $width;
                        $product->price=$product->get_price(); 
                        $product->product=$_pf->get_product($productid);
                        array_push($packs, $product);
                        
                    }
                    
                    return $packs;
                    
                }
                
                
                
                function ssfwc_soluship_init_form_fields()
                {
                    $current_unit = get_option('woocommerce_dimension_unit');
                    $weight_unit  = get_option('woocommerce_weight_unit');
                    $currency     = get_option('woocommerce_currency');
                    $cc           = new WC_Countries();
                    
                    $solushipsetting = get_option('woocommerce_soluship_shipping_method_settings');
                    $domain      = '';
                    $apiusername = '';
                    $apipassword = '';
                    $domain      = '';
                    $state       = '';
                    $accessToken = '';
                    if ($solushipsetting) {
                        
                        
                        
                        foreach ($solushipsetting as $key => $value) {
                            # code...
                            if ($key == 'enabled') {
                                $enabled = $value;
                            } else if ($key == 'apiusername') {
                                $apiusername = $value;
                            } else if ($key == 'apipassword') {
                                $apipassword = $value;
                            } else if ($key == 'domain') {
                                $domain = $value;
                            } else if ($key == 'sender_state') {
                                $state = $value;
                            }
                            else if ($key == 'solushipaccesstoekn') {
                                $accessToken = $value;
                            }
                            

                        }
                    }
                    
                
                  $carriersforinsurance = getcarriersforinsurance($accessToken,$domain);

             //   $carriersforinsurance = array_merge($carriersforinsurance);


                
                    $countries = $cc->get_shipping_countries();
                    
                    $this->form_fields = array(
                        'enabled' => array(
                            'title' => __('Enable/Disable', 'woocommerce_soluship'),
                            'type' => 'checkbox',
                            'description' => __('<span class="errorinput" id="enabled"></span>Enable/Disable soluship   Shipping method', 'woocommerce_soluship'),
                            'default' => 'no'
                        ),
                        'domain' => array(
                            'title' => __('DOMAIN', 'soluship_shipping'),
                            'type' => 'text',
                            'class' => 'domain_c',
                            'description' => __('<span class="errorinput" id="domain"></span>SHIPPING SERVER', 'woocommerce'),
                            'default' => __('https://soluship.com', 'soluship_shipping')                        ),
                        
                        'solushipaccesstoekn' => array(
                            'title' => __('SOLUSHIP ACCESS TOKEN', 'soluship_shipping'),
                            'type' => 'text',
                            'description' => __('<span class="errorinput" id="soluaccestok"></span>API security for soluship', 'woocommerce'),
                            'default' => __(' ', 'soluship_shipping')
                        ),
                        
                        'sender_company_name' => array(
                            'title' => __('Sender Company Name', 'woocommerce_soluship'),
                            'type' => 'text',
                            'description' => __('<span class="errorinput" id="sender_company_name"></span>Company Name to be printed in the shipping label and invoice', 'woocommerce_soluship'),
                            'default' => 'Sender Company Name'
                        ),
                        'sender_contact_phone' => array(
                            'title' => __('Sender Contact Phone', 'woocommerce_soluship'),
                            'type' => 'text',
                            'description' => __('<span class="errorinput" id="sender_contact_phone"></span>Contact Phone to be printed in the shipping label and invoice', 'woocommerce_soluship'),
                            'default' => ''
                        ),
                        'sender_address_line1' => array(
                            'title' => __('Sender Address Line1', 'woocommerce_soluship'),
                            'type' => 'text',
                            'description' => __('<span class="errorinput" id="sender_address_line1"></span>Address Line1 to be printed in the shipping label and invoice', 'woocommerce_soluship'),
                            'default' => ''
                        ),
                        'sender_contact_email' => array(
                            'title' => __('Sender E-mail', 'woocommerce_soluship'),
                            'type' => 'text',
                            'description' => __('<span class="errorinput" id="sender_contact_email"></span>Contact Email to be printed in the shipping label and invoice', 'woocommerce_soluship'),
                            'default' => ''
                        ),
                        
                        'country' => array(
                            'title' => __('Sender Country', 'woocommerce_soluship'),
                            'desc' => __('Sender Country', 'woocommerce_soluship'),
                            'id' => 'woocommerce_shipping_method_Sender_Country',
                            'label' => __('Select a country'),
                            'placeholder' => __('Enter something'),
                            'type' => 'select',
                            'options' => $countries,
                            'description' => __('<span class="errorinput" id="country"></span>', 'woocommerce_soluship'),
                            'desc_tip' => false,
                            'class' => 'countrylist',
                            'autoload' => true
                        ),
                        
                        'origin' => array(
                            'title' => __('Origin Postcode', 'woocommerce_soluship'),
                            'type' => 'text',
                            'description' => __('<span class="errorinput" id="origin"></span>Post code of the Sender, from where shipment needs to be picked', 'woocommerce_soluship'),
                            'default' => 'K6A 3H2'
                        ),
                        'sender_city' => array(
                            'title' => __('Sender City', 'woocommerce_soluship'),
                            'type' => 'text',
                            'description' => __('<span class="errorinput" id="sender_city"></span>City to be printed in the shipping label and invoice', 'woocommerce_soluship'),
                            'default' => 'Hawkesbury'
                        ),
                        'sender_state' => array(
                            'title' => __('Sender State', 'woocommerce_soluship'),
                            'type' => 'select',
                            'id' => 'woocommerce_shipping_method_sender_state',
                            'default' => '',
                            'description' => __('<span class="errorinput" id="sender_state"></span>State to be printed in the shipping label and invoice', 'woocommerce_soluship'),
                            'options' => array(),
                            'autoload' => true
                        ),
                        
                        'packaging_soluship' => array(
                            'title' => __('packaging Method', 'woocommerce_soluship'),
                            'desc' => __('Packaging Methods For the Shipping.', 'woocommerce_soluship'),
                            'id' => 'woocommerce_shipping_method_format1',
                            'default' => '',
                            'type' => 'select',
                            'options' => array(
                                'pack_slice' => __('Package By Maximum Weight', 'woocommerce_soluship'),
                                'unit_pack' => __('Unit Packaging', 'woocommerce_soluship'),
                                'product_group' => __('Product Group Packaging', 'woocommerce_soluship')
                                
                                
                            ),
                            'description' => __('<span class="errorinput" id="packaging_soluship"></span><b>1.By Package Weight </b> (Shipping Package will be Taken Using Maximum single Package value). <br><b>2.Unit Packaging </b> (Each Product in the cart will be Taken as   new Package for Shipment ). <br> <b>3. Product Group Packaging  </b> (Group the Package By Product in cart)) ', 'woocommerce_soluship'),
                            'desc_tip' => false,
                            'class' => 'pk_box1',
                            'autoload' => true
                        ),
                        'max_weight' => array(
                            'title' => __('Max Package Weight  (' . $weight_unit . ') ', 'woocommerce_soluship'),
                            'type' => 'weight',
                            'class' => 'maxvalue',
                            'description' => __('<span class="errorinput" id="max_weight"></span>If the total weight exceeds the max weight then package will be split into different shipping', 'woocommerce_soluship'),
                            'default' => '60'
                        ),
                        
                        'free_ship_soluship' => array(
                            'title' => __('Free Ship Settings', 'woocommerce_soluship'),
                            'type' => 'select',
                            'class' => 'fs_box1',
                            'default' => '',
                            'options' => array(
                                'nil' => __('N/A', 'woocommerce_soluship'),
                                'min_amount' => __('A Minimum order amount (defined below)', 'woocommerce_soluship'),
                                'min_weight' => __('A Minimum Weight of Cart (defined below)', 'woocommerce_soluship')
                            )
                        ),
                        'min_amount_free_ship' => array(
                            'title' => __('<span class="minamt">Minimum Order Amount (' . $currency . ') For Free Shipping</span>', 'woocommerce_soluship'),
                            'type' => 'price',
                            'class' => 'minamt',
                            'placeholder' => wc_format_localized_price(0),
                            'description' => __('<span class="errorinput" id="min_amount_free_ship"></span>Users will need to spend this amount to get free shipping (if enabled above).', 'woocommerce_soluship'),
                            'default' => '0'
                            
                        ),
                        'min_weight_free_ship' => array(
                            'title' => __('<span class="minwigt">Minimum Weight of Cart (' . $weight_unit . ') For Free Shipping</span>', 'woocommerce_soluship'),
                            'type' => 'weight',
                            'class' => 'minwigt',
                            'placeholder' => wc_format_localized_price(0),
                            'description' => __('<span class="errorinput" id="min_weight_free_ship"></span>This is minimum amount total weight ,that user will need add them to cart to by  to get free shipping (if enabled above ).', 'woocommerce_soluship'),
                            'default' => '0'
                            
                        ),
                        
                        'markup_down' => array(
                            'title' => __('Markup And Markup Down Settings ', 'woocommerce_soluship'),
                            'type' => 'select',
                            'class' => 'markupdown',
                            'default' => '',
                            'options' => array(
                                'nil' => __('N/A', 'woocommerce_soluship'),
                                'markup' => __('Mark Up Shipping Cost', 'woocommerce_soluship'),
                                'markdown' => __('Mark Down Shipping Cost', 'woocommerce_soluship')
                            )
                        ),
                        
                        'markup_down_type' => array(
                            'title' => __('Markup And Markup Type ', 'woocommerce_soluship'),
                            'type' => 'select',
                            'class' => 'markuptype',
                            'default' => '',
                            'options' => array(
                                'nil' => __('N/A', 'woocommerce_soluship'),
                                'flatmark' => __('FLATE RATE (' . $currency . ')', 'woocommerce_soluship'),
                                'percmark' => __('PERCENTAGE (%)', 'woocommerce_soluship')
                            ),
                            'description' => '<span class="errorinput" id="markup_down_type"></span>'
                        ),
                        
                        'markup_value' => array(
                            'title' => __('<span class="markup_value">MARKUP VALUE</span> ', 'woocommerce_soluship'),
                            'type' => 'number',
                            'default' => '',
                            'class' => 'mvalue',
                            'Description' => '<span class="errorinput" id="markup_value"></span>'
                        ),
                        'insurance_carrier' => array(
                            'title' => __('<span class="insurance_value">INSURANCE_CARRIER</span> ', 'woocommerce_soluship'),
                            'type'       => 'multiselect',
                            'class'      => 'wc-enhanced-select',
                            'css'        => 'width: 400px;',
                            'default'    => '',
                            'options' => $carriersforinsurance
                        ),
                        'default_dimensions_enabled' => array(
                            'title' => __('Enable Default Package Dimensions', 'woocommerce_soluship'),
                            'type' => 'checkbox',
                            'class' => 'defaultdim',
                            'description' => __('<span class="errorinput" id="enabled"></span>', 'woocommerce_soluship'),
                            'default' => 'no'
                        ),
                         'default_height' => array(
                            'title' => __('<span class="default_height">Default Package Height</span> ', 'woocommerce_soluship'),
                            'type' => 'number',
                            'default' => '',
                            'class' => 'default_package_dim_class',
                            'Description' => '<span class="errorinput" id="default_height"></span>'
                        ),
                         'default_width' => array(
                            'title' => __('<span class="default_width">Default Package Width</span> ', 'woocommerce_soluship'),
                            'type' => 'number',
                            'default' => '',
                            'class' => 'default_package_dim_class',
                            'Description' => '<span class="errorinput" id="default_width"></span>'
                        ),
                         'default_length' => array(
                            'title' => __('<span class="default_length">Default Package Length</span> ', 'woocommerce_soluship'),
                            'type' => 'number',
                            'default' => '',
                            'class' => 'default_package_dim_class',
                            'Description' => '<span class="errorinput" id="default_length"></span>'
                        ),

                    );
                    
                    
                }
            }
        }
    }
    


    add_action('woocommerce_shipping_init', 'ssfwc_soluship_shipping_method_init');

    function ssfwc_add_soluship_shipping_method($methods)
    {
        $methods[] = 'WC_Soluship_Shipping_Manager';
        return $methods;
    }
    

    function soluship_shipping_init() {
        if ( ! class_exists( 'WC_Soluship_Shipping_Method' ) ) {
            class WC_Soluship_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct($instance_id = 0) {
                    $this->id                 = 'soluship_shipping'; // Id for your shipping method. Should be uunique.
                    $this->instance_id           = absint( $instance_id );
                    $this->method_title       = __( 'Soluship Shipping' );  // Title shown in admin
                    $this->method_description = __( 'Shipping method to be used zone' ); // Description shown in admin

                    $this->title = "Soluship Shipping"; // This can be added as an setting but for this example its forced.

                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal'
                    );

                    $this->init();
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                function init_form_fields() {
                    $carriers = soluship_shipping_carrierapi_init();

                    $this->instance_form_fields = array(

                        'shippingZoneCarrier' => array(
                            'title'       => __( 'Carrier', 'soluship_shipping' ),
                            'type'        => 'select',
                            'options' => $carriers,
                            
                            'description' => __( 'Title to be displayed on site', 'woocommerce_soluship' ),
                            'default'     => __( '0')
                        )

                    );

                }

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 *
                 * @param mixed $package
                 *
                 * @return void
                 */

                public function calculate_shipping( $packages = array() ) {
                    $rate = array(
                        'id'       => $this->id,
                        'label'    => $this->title,
                        'cost'     => '0.00',
                        'calc_tax' => 'per_item'
                    );

                    // Register the rate
                    $this->add_rate( $rate );
                }
            }
        }
    }

    add_action( 'woocommerce_shipping_init', 'soluship_shipping_init' );

    function soluship_shipping_method( $methods ) {
        $methods['soluship_shipping'] = 'WC_Soluship_Shipping_Method';

        return $methods;
    }
   function getcarriersforinsurance($accessToken,$domain){
    $json="";
                       
   $response = wp_remote_post($domain . '/api/v1/getecommercecarriers', array(
                'method' => 'POST',
                'timeout' => 70,
                'sslverify' => 0,
                'headers' => array(
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'xwoocommercedomain' => 'woocommerce_soluship_shipping',
                    'SolushipAccessKey' => $accessToken,
                    'SolushipHost' => $_SERVER['SERVER_NAME']
                ),
                'body' => $json
            ));
            

            $ecomCarrier1s = array();
            if(!isset($response->errors)){
                $resultCarriers = array();
                $resultCarriers = explode(",",$response['body']);
           

                if(count($resultCarriers)){
                    foreach ($resultCarriers as $car) {
                        $temp = explode('_', $car);
                        if(isset($temp[1]))
                            $ecomCarrier1s[$temp[0]] =  __($temp[1], 'woocommerce_soluship');
                       
                    }
                }
            }

        return $ecomCarrier1s;
                    }
    function soluship_shipping_carrierapi_init() {

        $soluship          = new WC_Soluship_Shipping_Manager();
        $json                = '';

        $response = wp_remote_post($soluship->domain . '/api/v1/getecommercecarriers', array(
            'method' => 'POST',
            'timeout' => 70,
            'sslverify' => 0,
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'xwoocommercedomain' => 'woocommerce_soluship_shipping',
                'SolushipAccessKey' => $soluship->solushipaccesstoekn,
                'SolushipHost' => $_SERVER['SERVER_NAME']
            ),
            'body' => $json
        ));

        $ecomCarrier = array();
        if(!isset($response->errors)){
            $resultCarriers = array();
            $resultCarriers = explode(",",$response['body']);

            if(count($resultCarriers)){
                foreach ($resultCarriers as $car) {
                    $temp = explode('_', $car);
                    if(isset($temp[1]))
                        $ecomCarrier[$temp[0]] =  __($temp[1], 'woocommerce_soluship');
                }
            }
        }

        return $ecomCarrier;

    }

    add_filter( 'woocommerce_shipping_methods', 'soluship_shipping_method' );
    
    function custom_hide_shipping_methods( $rates, $package ) {

        foreach( WC()->cart->get_cart() as $cart_item  ) {
            $product = $cart_item[ 'data' ]; // The WC_Product object
            $shipping_class_id = $product->get_shipping_class_id();

            if( isset($rates['soluship_shipping'])) { // <== ID OF MY SHIPPING_CLASS
                unset( $rates['soluship_shipping'] ); // Removing specific shipping method
                break; // we stop the loop
            }
        }
        return $rates;
    }
    add_filter( 'woocommerce_package_rates', 'custom_hide_shipping_methods', 10, 2);
    
       
     
    
            add_filter('woocommerce_shipping_methods', 'ssfwc_add_soluship_shipping_method');

            function ssfwc_woocommerce_order_status_completed($order_id, $is_backend_order = false)
            {
                
                $order    = new WC_Order($order_id);
                $customer = new WC_Customer($order_id);

                 $shipping_methods = $order->get_shipping_methods();

                $packs='';
                foreach ($shipping_methods as $key => $value) {
                     $post_data = array('method_id' => $value['method_id'],
                        'total' => $value['total'],
                        'method_title' => $value['method_title']


                        );

                        $packs->$key=$post_data;
                 }    
                
            
                $shipAdress->shipcountry         = "";
                $shipAdress->shipcountry         = $order->shipping_country;
                $shipAdress->shipping_address_2  = $order->shipping_address_2;
                $shipAdress->shipping_address_1  = $order->shipping_address_1;
                $shipAdress->shipping_last_name  = $order->shipping_last_name;
                $shipAdress->shipping_first_name = $order->shipping_first_name;
                $shipAdress->shipping_company    = $order->shipping_company;
                $shipAdress->shipping_city       = $order->shipping_city;
                $shipAdress->shipping_postcode   = $order->shipping_postcode;
                $shipAdress->shipping_state      = $order->shipping_state;
                $shipAdress->email               = $order->billing_email;
                $shipAdress->phone               = $order->billing_phone;
                $order1->shipAdress               = $shipAdress;
                $order1->shippingMethod          = $packs;
                $order1->items                   = $order->get_items();
                $order1->id=$order->get_id();
         
                $order1->packes                   = ssfwc_getPackageDetailsfromOrder($order->get_items());
                
                
                $current_unit = get_option('woocommerce_dimension_unit');
                $weight_unit  = get_option('woocommerce_weight_unit');
                $currency     = get_option('woocommerce_currency');
                $dimunit      = get_option('woocommerce_dimension_unit');
                
                $weight = 0;
                if (sizeof($order->get_items()) > 0) {
                    foreach ($order->get_items() as $item) {
                        if ($item['product_id'] > 0) {
                            $_product = $order->get_product_from_item($item);
                            if (!$_product->is_virtual()) {
                                $weight += $_product->get_weight() * $item['qty'];
                            }
                        }
                    }
                }
                $soluship             = new WC_Soluship_Shipping_Manager();
                $totalWeight          = wc_get_weight($weight, 'lbs');
                $order1->max_weightLBS = wc_get_weight($soluship->max_weight, 'lbs');
                $weight_unit          = get_option('woocommerce_weight_unit');
                $order1->weight_unit   = $weight_unit;
                $order1->totalWeight   = $totalWeight;
                $baseLocation         = wc_get_base_location();
                $order1->baseLocations = $baseLocation;
                $order1->holeWeight    = wc_get_weight($weight, 'lbs');
                
                $unit = array(
                    'currency' => $currency,
                    'dimunit' => $dimunit,
                    'weight_unit' => $weight_unit
                );
                
                $order1->fromAddress = get_option('apipassword');
                $order1->unit        = $unit;
                
                $order1->fromAddress = $soluship->settings;
                $json               = json_encode($order1);
                
                 $response           = wp_remote_post($soluship->domain . '/api/v1/createShipment', array(
                    'method' => 'POST',
                    'timeout' => 70,
                    'sslverify' => 0,
                    'headers' => array(
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'xwoocommercedomain' => 'woocommerce_soluship_shipping',
                        'SolushipAccessKey' => $soluship->solushipaccesstoekn,
                        'SolushipHost' => $_SERVER['SERVER_NAME']
                    ),
                    'body' => $json
                ));
                
            }

    
            function ssfwc_getPackageDetailsfromOrder($items)
            {
                
                $packs = array();
                
                $_pf = new WC_Product_Factory();
                 
                foreach ($items as $key => $value) {
                    
                    
                    $productid           = $value['product_id'];
                    $product             = $_pf->get_product($productid);
                    $product->dimensions = $product->get_dimensions();
                    $product->quantity   = $value['qty'];
                    $product->weightLBS  = wc_get_weight($product->get_weight(), 'lbs');
                    
                   $length=wc_get_dimension( $product->length, 'in' );
                    $width=wc_get_dimension( $product->width, 'in' );
                    $height=wc_get_dimension( $product->height, 'in' );
                    $product->heightIN  = $height;
                    $product->lengthIN  = $length;  
                    $product->widthIN  = $width;
                    $product->weight  = $product->get_weight();
                    $product->price=$product->get_price();
                    $pname                =$product->get_title();
                    $psku                =$product->get_sku();
                    $product->name=$pname;
                    $product->sku=$psku;
                    array_push($packs, $product);
                    
                }
                
                return $packs;
                
            }
    
    
            add_action('woocommerce_thankyou', 'ssfwc_woocommerce_order_status_completed');
    
             add_action('woocommerce_order_status_cancelled', 'ssfwc_woocommerce_order_status_cancelled');
    
            function ssfwc_woocommerce_order_status_cancelled($order_id)
            {
                
                
                $solushipsetting = get_option('woocommerce_soluship_shipping_method_settings');
                
                $domain      = '';
                $apiusername = '';
                $apipassword = '';
                $domain      = '';
                $solushipaccesstoekn = '';

                $order = array(
                    'orderId' => $order_id
                );
                $json  = json_encode($order);
                if ($solushipsetting) {
                    foreach ($solushipsetting as $key => $value) {
                        # code...
                        if ($key == 'enabled') {
                            $enabled = $value;
                        } else if ($key == 'apiusername') {
                            $apiusername = $value;
                        } else if ($key == 'apipassword') {
                            $apipassword = $value;
                        } else if ($key == 'domain') {
                            $domain = $value;
                        }else if ($solushipaccesstoekn == 'solushipaccesstoekn') {
                            $solushipaccesstoekn = $value;
                        }
                    }
                }
                
                $response = wp_remote_post($domain . '/api/v1/cancelshipment', array(
                    'method' => 'POST',
                    'timeout' => 70,
                    'sslverify' => 0,
                    'headers' => array(
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'xwoocommercedomain' => 'woocommerce_soluship_shipping',
                        'SolushipAccessKey' => $solushipsetting['solushipaccesstoekn'],
                        'SolushipHost' => $_SERVER['SERVER_NAME']
                    ),
                    'body' => $json
                ));
                
                
            }
    
    
                function ssfwc_view_print_buttons($order)
                { 
                      wp_enqueue_style('woo-soluship-shipping-admin-css',plugins_url('css/soluship.css',__FILE__));
                    //$soluship = new WC_Soluship_Shipping_Manager();
                   
                    $solushipsetting = get_option('woocommerce_soluship_shipping_method_settings');
                    
                    $domain      = '';
                    $apiusername = '';
                    $apipassword = '';
                    $domain      = '';
                    $solushipaccesstoken='';
                    
                    if ($solushipsetting) {
                        foreach ($solushipsetting as $key => $value) {
                            # code...
                            if ($key == 'enabled') {
                                $enabled = $value;
                            } else if ($key == 'apiusername') {
                                $apiusername = $value;
                            } else if ($key == 'apipassword') {
                                $apipassword = $value;
                            } else if ($key == 'domain') {
                                $domain = $value;
                            } else if ($key == 'solushipaccesstoekn') {
                                $solushipaccesstoken = $value;
                            }
                        }
                    }
                    
                    
                    ?>

             
                        <div style="text-align: center;">
                        <a  href="<?php print_r($domain)?>/ecomPrintLabel.action?ids=<?php print_r($order->id)?>&woocommerceActive=<?php print_r($enabled)?>&solushipaccesstoken=<?php print_r($solushipaccesstoken) ?>" target="_blank"  class="ssfwc_button">PRINT LABEL</a> 

                        <a  href="<?php print_r($domain)?>/ecomOrderRepeat.action?ids=<?php print_r($order->id)?>&woocommerceActive=<?php print_r($enabled)?>&solushipaccesstoken=<?php print_r($solushipaccesstoken) ?>&orderData=true <?php print_r($orderdata)?>" target="_blank"  class="ssfwc_button">CREATE / VIEW SHIPPING STATUS </a> 

                        </div>

                     <?php
                }
    
                
                add_action('woocommerce_admin_order_data_after_billing_address', 'ssfwc_view_print_buttons', 10, 1);
    
  
                add_action('admin_footer-edit.php', 'ssfwc_custom_bulk_admin_footer');
                function ssfwc_custom_bulk_admin_footer()
                {
                    global $post_type;
                    if ($post_type == 'shop_order') {
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function() {

                             // printing status

                             jQuery('<option>').val('printing').text('<?php
                                        _e('PRINT LABEL');
                            ?>').appendTo("select[name='action']");
                             jQuery('<option>').val('printing').text('<?php
                                        _e('PRINT LABEL');
                            ?>').appendTo("select[name='action2']");

                             
                             });
                        </script>
                     <?php
                    }
                }
    
    
                function ssfwc_getOrderData($order_id)
                {
                    
                    
                    
                    $order = new WC_Order($order_id);
                    
                    $shipAdress->shipcountry         = "";
                    $shipAdress->shipcountry         = $order->shipping_country;
                    $shipAdress->shipping_address_2  = $order->shipping_address_2;
                    $shipAdress->shipping_address_1  = $order->shipping_address_1;
                    $shipAdress->shipping_last_name  = $order->shipping_last_name;
                    $shipAdress->shipping_first_name = $order->shipping_first_name;
                    $shipAdress->shipping_company    = $order->shipping_company;
                    $shipAdress->shipping_city       = $order->shipping_city;
                    $shipAdress->shipping_postcode   = $order->shipping_postcode;
                    $shipAdress->shipping_state      = $order->shipping_state;
                    $shipAdress->email               = $order->billing_email;
                    $shipAdress->phone               = $order->billing_phone;
                    $order->shipAdress               = $shipAdress;
                    $order->shippingMethod           = $order->get_shipping_methods();
                    $order->items                    = $order->get_items();
                    $order->packes                   = ssfwc_getPackageDetailsfromOrder($order->get_items());
                    
                    
                    $current_unit = get_option('woocommerce_dimension_unit');
                    $weight_unit  = get_option('woocommerce_weight_unit');
                    $currency     = get_option('woocommerce_currency');
                    $dimunit      = get_option('woocommerce_dimension_unit');
                    
                    $weight = 0;
                    if (sizeof($order->get_items()) > 0) {
                        foreach ($order->get_items() as $item) {
                            if ($item['product_id'] > 0) {
                                $_product = $order->get_product_from_item($item);
                                if (!$_product->is_virtual()) {
                                    $weight += $_product->get_weight() * $item['qty'];
                                }
                            }
                        }
                    }
                    $soluship             = new WC_Soluship_Shipping_Manager();
                    $totalWeight          = wc_get_weight($weight, 'lbs');
                    $order->max_weightLBS = wc_get_weight($soluship->max_weight, 'lbs');
                    $weight_unit          = get_option('woocommerce_weight_unit');
                    $order->weight_unit   = $weight_unit;
                    $order->totalWeight   = $totalWeight;
                    $baseLocation         = wc_get_base_location();
                    $order->baseLocations = $baseLocation;
                    $order->holeWeight    = wc_get_weight($weight, 'lbs');
                    
                    $unit = array(
                        'currency' => $currency,
                        'dimunit' => $dimunit,
                        'weight_unit' => $weight_unit
                    );
                    
                    $order->fromAddress = get_option('apipassword');
                    $order->unit        = $unit;
                    
                    $order->fromAddress = $soluship->settings;
                    $json               = json_encode($order);
                    
                    return $json;
                }
    
                add_action('load-edit.php', 'ssfwc_custom_bulk_action');
                function ssfwc_custom_bulk_action()
                {
                    
                    
                    
                    $solushipsetting = get_option('woocommerce_soluship_shipping_method_settings');
                    
                    $domain      = '';
                    $apiusername = '';
                    $apipassword = '';
                    $domain      = '';
                    
                    $enabled = '';
                    if ($solushipsetting) {
                        foreach ($solushipsetting as $key => $value) {
                            # code...
                            if ($key == 'enabled') {
                                $enabled = $value;
                            } else if ($key == 'apiusername') {
                                $apiusername = $value;
                            } else if ($key == 'apipassword') {
                                $apipassword = $value;
                            } else if ($key == 'domain') {
                                $domain = $value;
                            }
                        }
                    }
                    
                    if ($enabled == 'yes') {
                        
                        
                        global $typenow;
                        $post_type = $typenow;
                        
                        if ($post_type == 'shop_order') {
                            $wp_list_table   = _get_list_table('WP_Posts_List_Table');
                            $action          = $wp_list_table->current_action();
                            $allowed_actions = array(
                                "stock",
                                "printing"
                            );
                            if (!in_array($action, $allowed_actions))
                                return;
                            if (isset($_REQUEST['post'])) {
                                $orderids = array_map('intval', $_REQUEST['post']);
                            }
                            
                            switch ($action) {
                                case "printing":
                                    ssfwc_get_shipping_labels($orderids);
                                    break;
                                
                                default:
                                    return;
                            }
                            
                            $sendback = admin_url("edit.php?post_type=$post_type&success=1");
                            wp_redirect($sendback);
                            exit();
                        }
                        
                        
                    }
                    
                }
    

                function ssfwc_get_shipping_labels($orderids)
                {
                    
                    $order->orders   = $orderids;
                    $comma_separated = implode(",", $orderids);
                    
                    
                    
                    $solushipsetting = get_option('woocommerce_soluship_shipping_method_settings');
                    
                    $domain      = '';
                    $apiusername = '';
                    $apipassword = '';
                    $domain      = '';
                     $solushipaccesstoekn      = '';
                    
                    
                    if ($solushipsetting) {
                        foreach ($solushipsetting as $key => $value) {
                            # code...
                            if ($key == 'enabled') {
                                $enabled = $value;
                            } else if ($key == 'apiusername') {
                                $apiusername = $value;
                            } else if ($key == 'apipassword') {
                                $apipassword = $value;
                            } else if ($key == 'domain') {
                                $domain = $value;
                            }else if ($solushipaccesstoekn == 'solushipaccesstoekn') {
                                $solushipaccesstoekn = $value;
                            }
                        }
                    }
                    
                    
                    
                    $response = wp_remote_post($domain . '/ecomPrintLabel.action?ids=' . $comma_separated . '&woocommerceActive=' . $enabled, array(
                        'method' => 'GET',
                        'timeout' => 70,
                        'sslverify' => 0,
                        'headers' => array(
                            'Accept' => 'application/pdf,application/zpl',
                            'Content-Type' => 'application/pdf,application/zpl',
                            'SolushipAccessKey' => $solushipaccesstoekn,
                            'SolushipHost' => $_SERVER['SERVER_NAME']
                        )
                    ));
                    
                    
                    header("Content-type: application/pdf", true, 200);
                    header("Content-Disposition: attachment; filename= " . "SHIPPING_ORDERS_" . $comma_separated . "_Shipments.pdf");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                    print_r("ddd");
                    print_r($response['body']);
                    exit();
                      
                }
    
    
                add_action('admin_notices', 'ssfwc_custom_bulk_admin_notices');
                function ssfwc_custom_bulk_admin_notices()
                {
                    global $post_type, $pagenow;
                    if ($post_type == 'shop_order' && isset($_GET['success'])) {
                        //echo '<div class="updated"><p>The orders have been successfully update!</p></div>';
                    }
                }
    
    
                add_action('woocommerce_before_calculate_totals', 'ssfwc_fix_currency_conversion');
                function ssfwc_fix_currency_conversion($cart_object)
                {
                     
                    
                    $json = json_encode($cart_object);
                    
                    $solushipsetting = get_option('woocommerce_soluship_shipping_method_settings');
                    
                    $domain      = '';
                    $apiusername = '';
                    $apipassword = '';
                    $domain      = '';
                    
                    
                    if ($solushipsetting) {
                        
                        foreach ($solushipsetting as $key => $value) {
                            # code...
                            if ($key == 'enabled') {
                                $enabled = $value;
                            } else if ($key == 'apiusername') {
                                $apiusername = $value;
                            } else if ($key == 'apipassword') {
                                $apipassword = $value;
                            } else if ($key == 'domain') {
                                $domain = $value;
                            }
                        }
                    }
                    
                }
    

                function ssfwc_load_admin_head()
                {
                    if (isset($_GET['page']) AND isset($_GET['tab']) AND isset($_GET['section'])) {

                        $sec=$_GET['section'];
                         if ($_GET['page'] == 'wc-settings' AND $_GET['tab'] == 'shipping' &&  strpos($sec, 'soluship')!==false) {

                             
                            wp_enqueue_style('woo-soluship-shipping-admin-css',  plugins_url( 'css/soluship.css', __FILE__ ));
                            wp_enqueue_script('woo-soluship-shipping-admin', plugins_url('js/admin.js',__FILE__), array(
                                'jquery'
                            ));
                           
                        }
                    }
                }
    


                add_action('admin_head', 'ssfwc_load_admin_head', 1);
                
                 function ssfwc_test_ajax_load_scripts()
                {
                    // load our jquery file that sends the $.post request
                           
                    wp_enqueue_script("ajax-test", admin_url('admin-ajax.php'), array(
                        'jquery'
                    ));
                    
                    // make the ajaxurl var available to the above script
                    wp_localize_script('ajax-test', 'the_ajax_script', array(
                        'ajaxurl' => admin_url('admin-ajax.php')
                    ));
                }


                add_action('wp_print_scripts', 'ssfwc_test_ajax_load_scripts');
                
                
                function ssfwc_text_ajax_process_request()
                {
                    // first check if data is being sent and that it is the data we want
                    if (isset($_POST["country"])) {
                        // now set our response var equal to that of the POST var (this will need to be sanitized based on what you're doing with with it)
                        $country = $_POST["country"];
                        
                        $cc     = new WC_Countries();
                        $states = $cc->get_states($country);
                        
                        $solushipsetting = get_option('woocommerce_soluship_shipping_method_settings');
                        
                        $domain      = '';
                        $apiusername = '';
                        $apipassword = '';
                        $domain      = '';
                        $state       = '';
                        
                        if ($solushipsetting) {
                            
                            foreach ($solushipsetting as $key => $value) {
                                # code...
                                if ($key == 'enabled') {
                                    $enabled = $value;
                                } else if ($key == 'apiusername') {
                                    $apiusername = $value;
                                } else if ($key == 'apipassword') {
                                    $apipassword = $value;
                                } else if ($key == 'domain') {
                                    $domain = $value;
                                } else if ($key == 'sender_state') {
                                    $state = $value;
                                }
                            }
                            
                            
                            $states['default'] = $state;
                            
                        }
                        
                        
                        echo json_encode($states);
                        
                        // send the response back to the front end
                        
                        die();
                    }
                }

                 add_action('wp_ajax_test_response', 'ssfwc_text_ajax_process_request');   
       
}
?>
