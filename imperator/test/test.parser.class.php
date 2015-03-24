<!--html>
<head>
<meta charset="utf-8" />
</head>
<body-->
<pre><?php
$msg = 'ˆ‚Ëeà«ÈŒ';
		mb_internal_encoding('UTF-8');
$msg = chr(129).'~€12orem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eros eros, convallis et augue vitae, placerat tincidunt sed. ';

class Parser {
	public function __construct() {
		mb_internal_encoding('UTF-8');
	}

	private function readFromBuffer(&$buffer, $length = 1, $format = 'l') {
		if($buffer !== false) {
			$output = substr($buffer, 0, $length);
			if($output !== false) {
				$buffer = substr($buffer, $length);
				if($format !== false) {
					if($length === 1) {
						$output = ord($output);
					} else {
						var_dump($output);
						$output = unpack($format, $output);
						$output = $output[1];
					}
				}
				return $output;
			}
		}
		return false;
	}

	public function readAndHandleMessage($buffer) {
		$byte = $this->readFromBuffer($buffer);
		$fin = $byte >> 7;
		if($this->checkRSV($byte)) {
			return 'Incorrect RSV.';
		}
		$opcode = $byte & 0xf;
		$byte = $this->readFromBuffer($buffer);
		$mask = $byte >> 7;
		$payloadLen = $this->getPayloadLength($byte, $buffer);
		$maskingKey = $this->getMaskingKey($mask, $buffer);
		return $this->handleMessage($opcode, $fin, $payloadLen, $maskingKey, $buffer);
	}

	private function handleMessage($opcode, $fin, $payloadLen, $maskingKey, $buffer) {
		return (array(
			'opcode' => $opcode,
			'fin' => $fin,
			'payloadLength' => $payloadLen,
			'maskingKey' => $maskingKey,
			//'message' => $this->parseMSG($this->readFromBuffer($buffer, $payloadLen, false), $payloadLen, $maskingKey)
		));
	}

	private function parseMSG($input, $length, array $mask) {
		ob_start();
		for($n=0;$n < $length;$n++) {
			echo $input[$n] ^ $mask[$n%4];
		}
		$message .= ob_get_contents();
		ob_end_clean();
		return $message;
	}

	private function checkRSV($byte) {
		$rsv1 = ($byte >> 6) & 1;
		$rsv2 = ($byte >> 5) & 1;
		$rsv3 = ($byte >> 4) & 1;
		return ($rsv1 | $rsv2 | $rsv3) !== 0;
	}

	private function getPayloadLength($byte, &$buffer) {
		$payloadLen = $byte & 0x7f;
		if($payloadLen == 126) {
			$payloadLen = $this->readFromBuffer($buffer, 2, 'n');
		} else if ($payloadLen == 127) {
			$payloadLen = $this->readFromBuffer($buffer, 8, 'N');
		}
		return $payloadLen;
	}

	private function getMaskingKey($masked, &$buffer) {
		$maskingKey = array(0,0,0,0);
		if($masked) {
			for($n=0; $n < 4;$n++) {
				$maskingKey[$n] = $this->readFromBuffer($buffer, 1, false);
			}
		}
		return $maskingKey;
	}
}

var_dump((new Parser())->readAndHandleMessage($msg));
//var_dump(array(chr(0 | 126), chr(128 >> 7), pack('C',128 & 0xff)));
?></pre>