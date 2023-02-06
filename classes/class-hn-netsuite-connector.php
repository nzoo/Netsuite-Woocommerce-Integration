<?php 

/**
 ***************************
 ******* Connector *********
 ***************************
 */

function getHnsfAccessToken(){
	$access_token = get_option( 'hnsf_access_token' );
	$instance_url = get_option( 'hnsf_instance_url' );
	if( $access_token ) {
		return array(
				'access_token' => $access_token,
				'instance_url' => $instance_url
		);
	} else {
		return updateHnsfAccessToken();
	}
}

function updateHnsfAccessToken(){
	$url = 'https://login.salesforce.com/services/oauth2/token';
	$client_id = get_option('hnsf_client_id', true);
	$client_secret = get_option('hnsf_client_secret', true);
	$username = get_option('hnsf_username', true);
	$password = get_option('hnsf_password', true);
	$security_token = get_option('hnsf_security_token', true);
	
	$params = "grant_type=password"
			. "&client_id=" . $client_id
			. "&client_secret=" . $client_secret
			. "&username=" . $username
			. "&password=". $password . $security_token;

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

	$json_response = curl_exec($curl);
	$response = json_decode($json_response, true);
	if( ! isset( $response['error'] ) ) {
		update_option('hnsf_access_token', $response['access_token']);
		update_option('hnsf_instance_url', $response['instance_url']);
	} else {
		die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
	}
	return $response;
}

/**
 * Insert data
 * 
 * @param string $module
 * @param array $param
 * @return mixed
 */
function hnsfInsert($module, $param){
	$data = getHnsfAccessToken();
	$url = $data['instance_url'] . "/services/data/v28.0/sobjects/" . $module . "/";
	
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth " . $data['access_token'], "Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	$response = json_decode( $json_response );
	curl_close($curl);
// 	if($module == 'OrderItem'){
// 		var_dump($response);exit();
// 	}
	if ( $status != 201 ) {
		var_dump($response);exit();
		updateHnsfAccessToken();
		hnsfInsert($module, $param);
	}

	return $response->id;
}

function hnsfQuery( $query ) {
	$data = getHnsfAccessToken();
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
		hnsfQuery( $query );
	}

	return $response;
}

function hnsfUpdate($module, $param, $id){
	$data = getHnsfAccessToken();
	$url = $data['instance_url'] . "/services/data/v28.0/sobjects/" . $module . "/" . $id;
	
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: OAuth " . $data['access_token'], "Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
	curl_exec($curl);
}

?>