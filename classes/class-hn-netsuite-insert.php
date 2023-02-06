<?php

/**
 ***************************
 ******* Insert ************
 ***************************
 */

/**
 * Insert lead
 * 
 * @param number $user_id
 */
 function hnsfInsertLead( $user_id = 1 ){
	$user_data = get_userdata ( $user_id );
	$lead_data = array ();
// 	$email = $user_data->data->user_email;
// 	// var_dump($user_data->data);
	
// 	$customer_data = get_user_meta ( $user_id, null, 'true' );
	
// 	$lastname = get_user_meta ( $user_id, 'last_name', 'true' );
// 	$firstname = get_user_meta ( $user_id, 'first_name', 'true' );
// 	if (! $lastname) {
// 		$lastname = $user_data->data->display_name;
// 	}
	
// 	$lead_data['LastName'] = $lastname;
// 	$lead_data['FirstName'] = $firstname;
// 	$lead_data['Name'] = $firstname . ' ' . $lastname;
// 	$lead_data['Email'] = $email;
	// compare with fields mappping setting
	
// 	$lead_data ['Title'] = $options ['value'];
// 	$lead_data ['Company'] = $customer_data [$woo] [0];
// 	$lead_data ['Phone'] = $customer_data [$woo] [0];
// 	$lead_data ['MobilePhone'] = $customer_data [$woo] [0];
// 	$lead_data ['Street'] = $customer_data [$woo] [0];
// 	$lead_data ['City'] = $customer_data [$woo] [0];
// 	$lead_data ['State'] = $customer_data [$woo] [0];
// 	$lead_data ['PostalCode'] = $customer_data [$woo] [0];
// 	$lead_data ['Country'] = $customer_data [$woo] [0];
// 	$lead_data ['Description'] = $description;
 	$lead_data['Company'] = 'NA';
 	$lead_data['LastName'] = $user_data->data->display_name;
 	$lead_data['Email'] = $user_data->data->user_email;
	
	$param = json_encode( $lead_data );
	
 	hnsfInsert('Lead', $param);
 }
 add_action('user_register', 'hnsfInsertLead');
 
 
/**
 * Insert account
 * 
 * @param int $order_id
 */
function hnsfInsertAccount( $order_id = null ){
	$crm_data = array();
	/** @var $order WC_Order */
	$order = wc_get_order($order_id);
	
	/** @var $user WC_User */
	$user = $order->get_user();
	//var_dump($user);exit();
	if ( ! $user) {
		// order is by guest
	}
	
	$billing_add = $order->get_formatted_billing_address();
	$billing_street = $order->billing_address_1 . ' ' .$order ->  billing_address_1;
	$billing_city = $order->billing_city;
	$billing_state  = $order->billing_state;
	$billing_postcode   =  $order->billing_postcode;
	$billing_country    = $order->billing_country ;
	$billing_email 		= $order->billing_email;
	$billing_phone 		= $order->billing_phone;
	
	$crm_data['Name'] = $user->data->user_login;
	$crm_data['Phone'] = $billing_phone;
	
	// check duplicate item
	$response = hnsfDuplicateItem( 'Account', array( 'field' => 'name', 'value' => $crm_data['Name'] ) );
	if( ! empty( $response->records ) ) {
		return;
	}
	
	if ($billing_street)
		$crm_data['BillingStreet']  = $billing_street;
	
	if ($billing_city) {
		$crm_data['BillingCity'] = $billing_city;
	}
	
	if ($billing_state) {
		$crm_data['BillingState'] = $billing_state;
	}
	
	if ($billing_postcode) {
		$crm_data['BillingPostalCode'] = $billing_postcode;
	}
	
	if ($billing_country) {
		$crm_data['BillingCountry'] = $billing_country;
	}
	
	/** Shipping address */
	$crm_data['ShippingStreet'] = $order->shipping_address_1 . ' ' .$order ->  shipping_address_1;
	$crm_data['ShippingCity'] = $order->shipping_city;
	$crm_data['ShippingState']  = $order->shipping_state;
	$crm_data['ShippingPostalCode']   =  $order->shipping_postcode;
	$crm_data['ShippingCountry']    = $order->shipping_country ;
	$param = json_encode( $crm_data );
	hnsfInsert('Account', $param);
	
	// insert contact
	$contact_data['Lastname'] = $user->data->user_login;
	$contact_data['Phone'] = $billing_phone;
	$contact_data['Email'] = $billing_email;
	$contact_data['MobilePhone'] = $billing_phone;
	$contact_data['MailingStreet'] = $billing_street;
	$contact_data['MailingCity'] = $billing_city;
	$contact_data['MailingState'] = $billing_state;
	$contact_data['MailingCountry'] = $billing_country;
	$contact_data['MailingPostalCode'] = $billing_postcode;
	$param = json_encode( $contact_data );
	hnsfInsert('Contact', $param);
}
add_action('woocommerce_checkout_order_processed', 'hnsfInsertAccount');
add_action('woocommerce_checkout_order_on-hold', 'hnsfInsertAccount');


/**
 * Insert product
 * @param string $id
 */
function hnsfInsertProduct( $product_data, $pricebook_id ){
	$product_id = $product_data['product_id'];
	$data['Name'] = $product_data['name'];
	
	// check duplicate item
// 	$response = hnsfDuplicateItem( 'Product2', array( 'field' => 'name', 'value' => $data['Name'] ) );
// 	$records = $response->records;
// 	if( ! empty( $records ) ) {
// 		$records = $records[0];
// 		$Product2Id = $records->Id; // salesforece product's id
// 	} else {
		$data['ProductCode'] = get_post_meta( $product_id, '_sku', true );
		$param = json_encode( $data );
		$Product2Id = hnsfInsert('Product2', $param);
	//}
	
	/**
	 * insert PricebookEntry
	 */ 
	$PricebookEntry_data['Product2Id'] = $Product2Id;
	$PricebookEntry_data['Pricebook2Id'] = $pricebook_id;
	$PricebookEntry_data['UnitPrice'] = get_post_meta( $product_id, '_price', true );
	$param = json_encode( $PricebookEntry_data );
	//var_dump($param);exit();
	$PricebookEntryId = hnsfInsert('PricebookEntry', $param);
	return array(
			'Quantity' => $product_data['qty'],
			'PricebookEntryId' => $PricebookEntryId,
			'UnitPrice' => get_post_meta( $product_id, '_price', true )
	);
}

/**
 * Insert order
 * 
 * @param int $order_id
 */
function hnsfInsertOrder( $order_id = null ){
	$order = wc_get_order($order_id);
	$user = $order->get_user();
	$phone = $user->data->user_email;
	

	// get standard pricebook id
	$query = "select Id from Pricebook2 where isStandard = true";
	$response = hnsfQuery( $query );
	$records = $response->records;
	$records = $records[0];
	$pricebook_id = $records->Id;
	
	// get account id
	$query = "select id, name from Account where name = '" . $user->data->user_login . "' AND phone = '" . $order->billing_phone . "'";
	$response = hnsfQuery( $query );
	$records = $response->records;
	$records = $records[0];
	$account_id = $records->Id;
	
	$order_data['EffectiveDate'] = date( 'Y-m-d', strtotime( $order->post->post_date ) );
	$order_data['AccountId'] = $account_id;
	$order_data['BillingCity'] = $order->billing_city;
	$order_data['BillingState'] = $order->billing_state;
	$order_data['BillingPostalCode'] = $order->billing_postcode;
	$order_data['BillingCountry'] = $order->billing_country;
	$order_data['ShippingCity'] = $order->shipping_city;
	$order_data['ShippingState'] = $order->shipping_state;
	$order_data['ShippingPostalCode'] = $order->shipping_postcode;
	$order_data['ShippingCountry'] = $order->shipping_country;
	$order_data['Status'] = "Draft";
	$order_data['Pricebook2Id'] = $pricebook_id;
	
	$param = json_encode( $order_data );
	$order_id = hnsfInsert('Order', $param);
	
	foreach($order->get_items() as $item){
		$results = hnsfInsertProduct( $item, $pricebook_id );
		//var_dump($results);var_dump($order_id);exit();
		$param = json_encode(
				array(
						'PricebookEntryId' => $results['PricebookEntryId'],
						'Quantity' => $results['Quantity'],
						'UnitPrice' => $results['UnitPrice'],
						'OrderId' => $order_id
				)
		);
		hnsfInsert('OrderItem', $param);
	}
	
}
add_action('woocommerce_checkout_order_processed', 'hnsfInsertOrder');
add_action('woocommerce_checkout_order_on-hold', 'hnsfInsertOrder');
add_action('woocommerce_order_status_completed', 'hnsfInsertOrder');


/**
 * Check for duplicate items
 * 
 * @param string $module
 * @param array $param
 * 
 * @return array
 */
function hnsfDuplicateItem( $module, $param ) {
	$data = getHnsfAccessToken();
	$query = "SELECT Id FROM " . $module . " WHERE " . $param['field'] . " = '" . $param['value'] . "'";
	$url = $data['instance_url'] . "/services/data/v28.0/query?q=" . urlencode( $query );
	
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth " . $data['access_token']));
	
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$response = json_decode( $json_response );
	
	curl_close($curl);
	if ( $status != 200 ) {
		updateHnsfAccessToken();
		hnsfDuplicateItem( $module, $param );
	}
	
	return $response;
}

/**
 * Update account
 * 
 * @param unknown $param
 * @param unknown $id
 */
function hnsfUpdateProduct($param, $id){
	hnsfUpdate('Account', $param, $id);
}