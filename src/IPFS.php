<?php
/*
	This code is licensed under the MIT license.
	See the LICENSE file for more information.
*/

namespace Cloutier\PhpIpfsApi;

use Exception;

class IPFS {
	private $gatewayIP;
	private $gatewayPort;
	private $gatewayApiPort;

	function __construct($ip = "localhost", $port = "8080", $apiPort = "5001") {
		$this->gatewayIP      = $ip;
		$this->gatewayPort    = $port;
		$this->gatewayApiPort = $apiPort;
	}

	public function cat ($hash) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayPort;
		return $this->curl("http://$ip:$port/ipfs/$hash");

	}

	public function add ($content) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$req = $this->curl("http://$ip:$port/api/v0/add?stream-channels=true", $content);
		$req = json_decode($req, TRUE);

        return $req['Hash'];
	}

	public function ls ($hash) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$response = $this->curl("http://$ip:$port/api/v0/ls/$hash");

		$data = json_decode($response, TRUE);

		return $data['Objects'][0]['Links'];
	}

	public function size ($hash) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$response = $this->curl("http://$ip:$port/api/v0/object/stat/$hash");
		$data = json_decode($response, TRUE);

		return $data['CumulativeSize'];
	}

	public function pinAdd ($hash) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$response = $this->curl("http://$ip:$port/api/v0/pin/add/$hash");
		$data = json_decode($response, TRUE);

		return $data;
	}

    public function version () {
        $ip = $this->gatewayIP;
        $port = $this->gatewayApiPort;
        $response = $this->curl("http://$ip:$port/api/v0/version");
        $data = json_decode($response, TRUE);
        return $data["Version"];
    }

    private function curl ($url, $filepath=null) {
        $ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

		if ($filepath !== null) {
			// add the file
			$cfile = curl_file_create($filepath, 'application/octet-stream', basename($filepath));

			// post
			$post_fields = ['file' => $cfile];
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		}

		$output = curl_exec($ch);

		// check HTTP response code
		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$code_category = substr($response_code, 0, 1);
		if ($code_category == '5' OR $code_category == '4') {
			$data = @json_decode($output, true);
			if (!$data AND json_last_error() != JSON_ERROR_NONE) {
				throw new Exception("IPFS returned response code $response_code: ".substr($output, 0, 200), $response_code);
			}
			if (is_array($data)) {
				if (isset($data['Code']) AND isset($data['Message'])) {
					throw new Exception("IPFS Error {$data['Code']}: {$data['Message']}", $response_code);
				}
			}
		}

		// handle empty response
		if ($output === false) {
			throw new Exception("IPFS Error: No Response", 1);
		}

		curl_close($ch);

		return $output;
	}
}
