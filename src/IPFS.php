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

	/**
	 * Adds a file directly to IPFS
	 * @param string  $filepath a filepath to upload
	 * @param boolean $pin      pin the file (true)
	 * @return  string The content hash
	 */
	public function add ($filepath, $pin=true) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$query_vars = [
			'pin' => ($pin  ? 'true' : 'false'),
		];

		$response = $this->postFile("/api/v0/add", $filepath, $query_vars);
		return $response['Hash'];
	}

	/**
	 * Adds files to a folder in IPFS
	 * 
	 * Returns data in the form of:
	 * {
	 *     "files": [
	 *         {
	 *             "Name": "brown-square.png",
	 *             "Hash": "Qmb3isYtDpPEAgEBCUBQMU8tqP23h2Rp99zZivWKUmuGMd"
	 *         },
	 *         {
	 *             "Name": "brown-square.jpg",
	 *             "Hash": "QmZc5W8EXWwRid2djYgG8w2MrDM7CyhHArozF5Ro8C5b8W"
	 *         }
	 *     ],
	 *     "FolderHash": "QmWXmr7sxZHVQjMhY9tPgAuHYrXpxKTjW6MYfaTPVCLLgr"
	 * }
	 * @param multi  $filepath_or_filepaths  a single filepath or an array of filepaths to upload
	 * @param boolean $pin      pin the file (true)
	 * @return  array Data with Name, Hash and FolderHash
	 */
	public function addWithWrap ($filepath_or_filepaths, $pin=true) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$query_vars = [
			'pin' => ($pin  ? 'true' : 'false'),
			'w'   => 'true',
		];

		$responses = $this->postFile("/api/v0/add", $filepath_or_filepaths, $query_vars, $expect_multiple = true);

		$return_data = [
			'files'      => [],
			'FolderHash' => '',
		];

		$responses_count = count($responses);
		for ($i=0; $i < $responses_count; $i++) { 
			if ($i === $responses_count - 1) {
				$return_data['FolderHash'] = $responses[$i]['Hash'];
				continue;
			}

			$return_data['files'][$i] = $responses[$i];
		}

		return $return_data;
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

	public function pinRemove ($hash) {
		
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$response = $this->curl("http://$ip:$port/api/v0/pin/rm/$hash");
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

	public function get($api_path, $query_vars=[]) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$query_string = $query_vars ? '?'.http_build_query($query_vars) : '';
		$response = $this->curl("http://{$ip}:{$port}/".ltrim($api_path,'/').$query_string);
		$data = json_decode($response, true);
		return $data;
	}

	public function postFile($api_path, $filepath, $query_vars=[], $expect_multiple=false) {
		$ip = $this->gatewayIP;
		$port = $this->gatewayApiPort;

		$query_string = $query_vars ? '?'.http_build_query($query_vars) : '';
		$response = $this->curl("http://{$ip}:{$port}/".ltrim($api_path,'/').$query_string, $filepath);

		if ($expect_multiple) {
			$data = [];
			foreach (explode("\n", $response) as $json_data_line) {
				if (strlen(trim($json_data_line))) {
					$data[] = json_decode($json_data_line, true);
				}
			}
		} else {
			$data = json_decode($response, true);
		}

		return $data;
	}

	private function curl ($url, $filepath_or_filepaths=null) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 240);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		 
		if ($filepath_or_filepaths !== null) {
			$filepaths = $filepath_or_filepaths;
			if (!is_array($filepaths)) { $filepaths = [$filepath_or_filepaths]; }

			$post_fields = [];
			foreach($filepaths as $offset => $filepath) {
				// add the file
				$cfile = curl_file_create($filepath, 'application/octet-stream', basename($filepath));
				$post_fields['file'.sprintf('%03d', $offset + 1)] = $cfile;
			}

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


