<?php

/* PHP Error Reporting */
error_reporting(-1);
/* ------------------- */

/* Status codes used by uTorrent*/
define('STARTED',1);
define('CHECKING', 2);
define('START_AFTER_CHECK', 4);
define('CHECKED', 8);
define('ERROR', 16);
define('PAUSED', 32);
define('QUEUED', 64);
define('LOADED', 128);
/* --------------------------- */

/* Torrent/Label list */
define('HASH',0); // (string)
define('STATUS', 1); // (integer)
define('NAME', 2); // (string)
define('SIZE', 3); // (integer in bytes)
define('PERCENT_PROGRESS', 4); // (integer in bytes)
define('DOWNLOADED', 5); // (integer in bytes)
define('UPLOADED', 6); // (integer in bytes)
define('RATIO', 7); // (integer in pr mils)
define('UPLOAD_SPEED',8); // (integer in bytes per second)
define('DOWNLOAD_SPEED', 9); // (integer in bytes per second)
define('ETA', 10); // (integer in seconds)
define('LABEL', 11); // (string)
define('PEERS_CONNECTED', 12); // (integer)
define('PEERS_IN_SWARM', 13); // (integer)
define('SEEDS_CONNECTED', 14); // (integer)
define('SEEDS_IN_SWARM', 15); // (integer)
define('AVAILABILITY',16); // (integer in 1/65535ths)
define('TORRENT_QUEUE_ORDER', 17); // (integer)
define('REMAINING', 18); // (integer in bytes)
/* ----------------- */

class uTorrentRemote
{

	private $Hostname;
	private $Username;
	private $Password;
	private $AuthToken;
	private $Crl;
	
	
	public function __construct($_host, $_user, $_passwd)
	{
		if (!(isset($_host) && isset($_user) && isset($_passwd))) { die('Error: ' . get_class() . '::construct()' . ' takes exactly 3 arguments.'); }
		
		$this->Hostname = $_host; /* uTorrent WebUI's hostname/ip address. Ex: 127.0.0.1:4321 */
		$this->Username = $_user; /* Username for uTorrent WebUI */
		$this->Password = $_passwd; /* Password for uTorrent WebUI */
	
		$this->Crl = curl_init();
		$this->AuthToken = $this->GetToken();
	}
	
	public function __destruct()
	{
		if (isset($this->Crl))
			curl_close($this->Crl);
	}
	
	public function GrabTorrents()
	{
		$JsonResponse = $this->SendRequest($this->Hostname . '/gui/?list=1&token=' . $this->AuthToken);
		$Torrents = json_decode($JsonResponse, true);
		
		return $Torrents['torrents'];
	}
	
	public function GrabLabels()
	{
		$JsonResponse = $this->SendRequest($this->Hostname . '/gui/?list=1&token=' . $this->AuthToken);
		$Labels = json_decode($JsonResponse, true);
		
		return $Labels['labels'];
	}
	
	public function GrabListOfFiles($_torrentHash)
	{
		$JsonResponse = $this->SendRequest($this->Hostname . '/gui/?action=getfiles&hash=' . $_torrentHash . '&token=' . $this->AuthToken);
		$TorrentFiles = json_decode($JsonResponse, true);
		
		return $TorrentFiles['files'];
	}
	
	public function GrabTorrentProperties($_torrentHash)
	{
		$JsonResponse = $this->SendRequest($this->Hostname . '/gui/?action=getprops&hash=' . $_torrentHash . '&token=' . $this->AuthToken);
		$Properties = json_decode($JsonResponse, true);
		
		return $Properties['props'];
	}
	
	public function CheckStatusCode($_torrentStatuscode, $_statuscode)
	{
	
		return ( ($_statuscode == ($_torrentStatuscode & $_statuscode)) ? true : false);
	}
	
	public function GrabSettings()
	{
		$JsonResponse = $this->SendRequest($this->Hostname . '/gui/?action=getsettings&token=' . $this->AuthToken);
		$Settings = json_decode($JsonResponse, true);
		
		return $Settings['settings'];
	}
	
	public function ExecAction($_action, $_torrentHash, $_prority = 0, $_fileIndex = 0, $torrentUrl = '')
	{
		switch ($_action)
		{
			case 'start':
				$this->SendRequest($this->Hostname . '/gui/?action=start&hash=' . $_torrentHash .'&token=' . $this->AuthToken);
				break;
			case 'stop':
				$this->SendRequest($this->Hostname . '/gui/?action=stop&hash=' . $_torrentHash .'&token=' . $this->AuthToken);
				break;
			case 'forcestart':
				$this->SendRequest($this->Hostname . '/gui/?action=forcestart&hash=' . $_torrentHash .'&token=' . $this->AuthToken);
				break;
			case 'unpause':
				$this->SendRequest($this->Hostname . '/gui/?action=unpause&hash=' . $_torrentHash .'&token=' . $this->AuthToken);
				break;
			case 'recheck':
				$this->SendRequest($this->Hostname . '/gui/?action=recheck&hash=' . $_torrentHash .'&token=' . $this->AuthToken);
				break;
			case 'remove':
				$this->SendRequest($this->Hostname . '/gui/?action=remove&hash=' . $_torrentHash .'&token=' . $this->AuthToken);
				break;
			case 'removedata':
				$this->SendRequest($this->Hostname . '/gui/?action=removedata&hash=' . $_torrentHash .'&token=' . $this->AuthToken);
				break;
			case 'setprio':
				$this->SendRequest($this->Hostname . '/gui/?action=setprio&hash=' . $_torrentHash . '&p=' . $_priority . '&f=' . $_fileIndex .'&token=' . $this->AuthToken);
				break;
			case 'add-url':
				$this->SendRequest($this->Hostname . '/gui/?action=add-url&s=' . $_torrentUrl .'&token=' . $this->AuthToken);
				break;
		}
	}
	
	public function SpeedConvert($SpeedInBytes)
	{
		if ($SpeedInBytes == 0)
		{
			return;
		}
		if ($SpeedInBytes < 1024)
		{
			return round($SpeedInBytes, 2) . ' b/s';
		}
		if ($SpeedInBytes >= 1024 && $SpeedInBytes < 1048576)
		{
			return round(($SpeedInBytes / 1024), 2) . ' kB/s';
		}
		if ($SpeedInBytes >= 1048576 && $SpeedInBytes < 1073741827)
		{
			return round(($SpeedInBytes / 1024) / 1024, 2) . ' MB/s';
		}
		if ($SpeedInBytes >= 1073741827 && $SpeedInBytes < 1099511627776)
		{
			return round(($SpeedInBytes / 1024) / 1024 / 1024, 2) . ' GB/s';
		}
	}
	
	public function SizeConvert($SizeInBytes)
	{
		if ($SizeInBytes < 1024)
		{
			return round($SizeInBytes, 2) . ' B';
		}
		if ($SizeInBytes >= 1024 && $SizeInBytes < 1048576)
		{
			return round(($SizeInBytes / 1024), 2) . ' KB';
		}
		if ($SizeInBytes >= 1048576 && $SizeInBytes < 1073741827)
		{
			return round(($SizeInBytes / 1024) / 1024, 2) . ' MB';
		}
		if ($SizeInBytes >= 1073741827 && $SizeInBytes < 1099511627776)
		{
			return round(($SizeInBytes / 1024) / 1024 / 1024, 2) . ' GB';
		}
	}
	
	private function SendRequest($Url)
	{
		curl_setopt($this->Crl, CURLOPT_URL, $Url);
		curl_setopt($this->Crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->Crl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->Crl, CURLOPT_USERPWD, $this->Username . ':' . $this->Password);
		curl_setopt($this->Crl, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($this->Crl, CURLOPT_COOKIEFILE, 'cookie.txt');
		
		$Ret = curl_exec($this->Crl);
		
		return $Ret;
	}
	
	private function GetToken()
	{
		return strip_tags($this->SendRequest('http://' . $this->Username . ":" . $this->Password . "@" . $this->Hostname . '/gui/token.html'));
	}
}
?>