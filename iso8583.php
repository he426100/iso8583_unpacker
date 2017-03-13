<?php
class iso8583 {
	// TYPE
	const ASC = 1;
	const BCD = 2;
	const R_BCD = 3;
	const BIN = 4;
	const UTF8 = 5;
	// LEN
	const LLVAR = -1;
	const LLLVAR = -2;

	private $iso8583_fields = [
		[], //0
		['type' => self::ASC, 'len' => 8], // 1
		['type' => self::BCD, 'len' => self::LLVAR], // 2
		['type' => self::BCD, 'len' => 6], // 3
		['type' => self::BCD, 'len' => 12], // 4
		['type' => self::ASC, 'len' => 8], // 5
		['type' => self::ASC, 'len' => 8], // 6
		['type' => self::ASC, 'len' => 8], // 7
		['type' => self::ASC, 'len' => 8], // 8
		['type' => self::ASC, 'len' => 8], // 9
		['type' => self::ASC, 'len' => 8], // 10
		['type' => self::BCD, 'len' => 6], // 11
		['type' => self::BCD, 'len' => 6], // 12
		['type' => self::BCD, 'len' => 4], // 13
		['type' => self::BCD, 'len' => 4], // 14
		['type' => self::BCD, 'len' => 4], // 15
		['type' => self::ASC, 'len' => 8], // 16
		['type' => self::ASC, 'len' => 8], // 17
		['type' => self::ASC, 'len' => 8], // 18
		['type' => self::ASC, 'len' => 8], // 19
		['type' => self::ASC, 'len' => 8], // 20
		['type' => self::ASC, 'len' => 8], // 21
		['type' => self::BCD, 'len' => 3], // 22
		['type' => self::R_BCD, 'len' => 3], // 23
		['type' => self::ASC, 'len' => 8], // 24
		['type' => self::BCD, 'len' => 2], // 25
		['type' => self::BCD, 'len' => 2], // 26
		['type' => self::ASC, 'len' => 8], // 27
		['type' => self::ASC, 'len' => 8], // 28
		['type' => self::ASC, 'len' => 8], // 29
		['type' => self::ASC, 'len' => 8], // 30
		['type' => self::ASC, 'len' => 8], // 31
		['type' => self::BCD, 'len' => self::LLVAR], // 32
		['type' => self::ASC, 'len' => 8], // 33
		['type' => self::ASC, 'len' => 8], // 34
		['type' => self::BCD, 'len' => self::LLVAR], // 35
		['type' => self::BCD, 'len' => self::LLLVAR], // 36
		['type' => self::ASC, 'len' => 12], // 37
		['type' => self::ASC, 'len' => 6], // 38
		['type' => self::ASC, 'len' => 2], // 39
		['type' => self::ASC, 'len' => 8], // 40
		['type' => self::ASC, 'len' => 8], // 41
		['type' => self::ASC, 'len' => 15], // 42
		['type' => self::ASC, 'len' => 8], // 43
		['type' => self::ASC, 'len' => self::LLVAR], // 44
		['type' => self::ASC, 'len' => 8], // 45
		['type' => self::ASC, 'len' => 8], // 46
		['type' => self::ASC, 'len' => 8], // 47
		['type' => self::BCD, 'len' => self::LLLVAR], // 48
		['type' => self::ASC, 'len' => 3], // 49
		['type' => self::ASC, 'len' => 8], // 50
		['type' => self::ASC, 'len' => 8], // 51
		['type' => self::BIN, 'len' => 8], // 52
		['type' => self::BCD, 'len' => 16], // 53
		['type' => self::BCD, 'len' => self::LLLVAR], // 54
		['type' => self::BIN, 'len' => self::LLLVAR], // 55
		['type' => self::ASC, 'len' => 8], // 56
		['type' => self::ASC, 'len' => 8], // 56
		['type' => self::ASC, 'len' => self::LLLVAR], // 58
		['type' => self::ASC, 'len' => 8], // 59
		['type' => self::BCD, 'len' => self::LLLVAR], // 60
		['type' => self::BCD, 'len' => self::LLLVAR], // 61
		['type' => self::ASC, 'len' => self::LLLVAR], // 62
		['type' => self::ASC, 'len' => self::LLLVAR], // 63
		['type' => self::BIN, 'len' => 8], // 64
	];
	//域数据
	private $data = [];
	//消息类型
	private $mti = '';
	//iso8583报文
	private $iso = '';
	//报文图
	private $map = [];
	//报文头
	private $header = "";
	//TPDU
	private $tpdu = "";
	//报文长度
	private $length = 0;

	function __construct($data = null) {
		if ($data !== null) {
			$this->loadPackage($data);
		}
	}

	//var FIX = n
	public function loadPackage($data) {
		if (is_string($data)) {
			$this->iso = $data;
			$buf = $this->stringToBuffer(str_replace(' ', '', $data));
			if ($buf) {
				$this->map = $buf;
			}
		} else if (is_array($data)) {
			//data instanceof Uint8Array
			$this->map = $data;
			$this->iso = $this->bufferToString($data);
		} else {
			throw new Exception('loadData data只能为string或UInt8Array');
		}
	}

	// format [ {field:3,type:ASC,len:8},{field:4,type:BCD,len:8} ]
	public function setFormatTable($format) {
		if ($format && is_string($format)) {
			$json = json_decode($format, true);
			if ($json && is_array($json)) {
				for ($i = 0, $j = count($json); $i < $j; $i++) {
					$item = $json[$i];
					$this->iso8583_fields[$item['field']] = ['type' => $item['type'], 'len' => $item['len']];
				}
			}
		} else if ($format && is_array($format)) {
			for ($i = 0, $j = count($format); $i < $j; $i++) {
				$item = $format[$i];
				$this->iso8583_fields[$item['field']] = ['type' => $item['type'], 'len' => $item['len']];
			}
		}
	}
	/**
	 * 解包
	 * @param  string 保温
	 * @return array
	 */
	public function unpack($package = '') {
		if (!empty($package)) {
			$this->loadPackage($package);
		}
		$result = [];
		try {
			$iso = $this->map;
			$this->length = $iso[0] * 256 + $iso[1];
			$this->tpdu = $this->bufferToString($iso, 2, 7);
			$this->header = $this->bufferToString($iso, 7, 13);
			$this->mti = $this->bufferToString($iso, 13, 15);
			$this->data = [];
			$index = 23;
			for ($i = 0; $i < 8; $i++) {
				$bitmask = 0x80;
				for ($j = 0; $j < 8; $j++, $bitmask >>= 1) {
					if ($i == 0 && $bitmask == 0x80) {
						continue;
					}
					if (($iso[$i + 15] & $bitmask) == 0) {
						continue;
					}
					$n = ($i << 3) + $j + 1;
					$dataLen = 0;
					$offset = $index;
					$fm = $this->iso8583_fields[$n];
					if ($fm['len'] == self::LLVAR) {
						$dataLen = intval($this->bufferToString($iso, $offset, $offset + 1));
						$offset += 1;
					} else if ($fm['len'] == self::LLLVAR) {
						$dataLen = intval($this->bufferToString($iso, $offset, $offset + 2));
						$offset += 2;
					} else {
						$dataLen = $fm['len'];
					}
					if ($fm['type'] == self::ASC) {
						$f = [];
						$f['len'] = $dataLen;
						$f['content'] = $this->stringFromBuffer($iso, $offset, $offset + $dataLen);
						$this->data[$n] = $f;
						$offset += $dataLen;
					} else if ($fm['type'] == self::BCD) {
						$f = [];
						$f['len'] = $dataLen;
						$f['content'] = $this->bufferToString($iso, $offset, $offset + ceil($dataLen / 2));
						if (strlen($f['content']) == $f['len'] + 1) {
							$f['content'] = substr($f['content'], 0, strlen($f['content']) - 1);
						}
						$this->data[$n] = $f;
						$offset += ceil($dataLen / 2);
					} else if ($fm['type'] == self::R_BCD) {
						$f = [];
						$f['n'] = n;
						$f['len'] = $dataLen;
						$f['content'] = $this->bufferToString($iso, $offset, $offset + ceil($dataLen / 2));
						if (strlen($f['content']) == $f['len'] + 1) {
							$f['content'] = substr($f['content'], 1, strlen($f['content']));
						}
						$this->data[$n] = $f;
						$offset += ceil($dataLen / 2);
					} else if ($fm['type'] == self::BIN) {
						$f = [];
						$f['len'] = $dataLen;
						$f['content'] = $this->bufferToString($iso, $offset, $offset + $dataLen);
						$this->data[$n] = $f;
						$offset += $dataLen;
					} else if ($fm['type'] == self::UTF8) {
						$f = [];
						$f['len'] = $dataLen;
						$f['content'] = $this->utf8StringFromBuffer($iso, $offset, $offset + $dataLen);
						$this->data[$n] = $f;
						$offset += $dataLen;
					} else {
						throw new Exception('域信息错误:' . $n . '域 type:' . $fm['type']+' len:' . $fm['len']);
					}
					if ($offset > $this->length + 2) {
						throw new Exception('8583解包错误:' . $n . '域长度错误');
					}
					$index = $offset;
				}
			}
			return $result;
		} catch (Exception $e) {
			$result['err'] = $e->getMessage();
			return $result;
		}
	}

	private function stringFromBuffer($buf = [], $start = -1, $end = -1) {
		if (empty($buf) || !is_array($buf)) {
			return null;
		}
		if ($start == -1) {
			$start = 0;
		}
		if ($end == -1) {
			$end = count($buf);
		}
		$str = '';
		for ($i = $start; $i < $end; $i++) {
			$item = $buf[$i];
			$str += chr($item);
		}
		return $str;
	}
	private function utf8StringFromBuffer($buf = [], $start = -1, $end = -1) {
		if (empty($buf) || !is_array($buf)) {
			return null;
		}
		if ($start == -1) {
			$start = 0;
		}
		if ($end == -1) {
			$end = count($buf);
		}
		$str = '';

		for ($i = $start; $i < $end; $i++) {
			$item = $buf[$i];
			$h = floor($item / 16);
			$l = $item % 16;
			if ($h > 9) {
				$str += chr($h + 55);
			} else {
				$str += chr($h + 48);
			}
			if ($l > 9) {
				$str += chr($l + 55);
			} else {
				$str += chr($l + 48);
			}
		}
		$str = preg_replace('/([A-F0-9]{2})/i', '%$1', $str);
		return urldecode($str);
	}
	private function stringToBuffer($str) {
		if (empty($str) || !is_string($str)) {
			return null;
		}
		if (strlen($str) % 2 != 0) {
			$str = $str . '0';
		}
		$buf = array_fill(0, floor(strlen($str) / 2), 0);
		for ($i = 0, $j = strlen($str); $i < $j; $i += 2) {
			$h = ord(substr($str, $i, 1));
			$l = ord(substr($str, $i + 1, 1));
			$v = 0;
			if ($h >= 65 && $h <= 70) {
				$v += $h - 55;
			} else if ($h >= 97 && $h <= 102) {
				$v += $h - 87;
			} else if ($h >= 48 && $h <= 57) {
				$v += $h - 48;
			}
			$v = $v * 16;
			if ($l >= 65 && $l <= 70) {
				$v += $l - 55;
			} else if ($l >= 97 && $l <= 102) {
				$v += $l - 87;
			} else if ($l >= 48 && $l <= 57) {
				$v += $l - 48;
			}
			$buf[floor($i / 2)] = $v;
		}
		return $buf;
	}

	private function bufferToString($buf = [], $start = -1, $end = -1) {
		if (empty($buf) || !is_array($buf)) {
			return null;
		}
		if ($start == -1) {
			$start = 0;
		}
		if ($end == -1) {
			$end = count($buf);
		}
		$str = '';
		for ($i = $start; $i < $end; $i++) {
			$item = $buf[$i];
			$h = floor($item / 16);
			$l = $item % 16;
			if ($h > 9) {
				$str .= chr($h + 55);
			} else {
				$str .= chr($h + 48);
			}
			if ($l > 9) {
				$str .= chr($l + 55);
			} else {
				$str .= chr($l + 48);
			}
		}
		return $str;
	}

	/* -----------------------------------------------------
		        method
	*/
	//method: add data element
	public function addData($bit, $data) {
		$this->data[$bit] = $data;
	}
	//method: set MTI
	public function setMti($mti) {
		if (strlen($mti) == 4 && ctype_digit($mti)) {
			$this->mti = $mti;
		}
	}

	//method: retrieve data element
	public function getData() {
		return $this->data;
	}

	//method: retrieve mti
	public function getMti() {
		return $this->mti;
	}
	//method: retrieve iso with all complete data
	public function getIso() {
		return $this->iso;
	}

	public function getTpdu(){
		return $this->tpdu;
	}
	public function getHeader(){
		return $this->header;
	}

	public function getLength(){
		return $this->bufferToString($this->map, 0, 2);
	}
}
