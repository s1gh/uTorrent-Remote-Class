<?php

/* PHP Error Reporting */
error_reporting(0);
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
		if (!(isset($_host) && isset($_user) && isset($_passwd))) { die('Error: ' . get_class() . '::construct()' . ' takes exacly 3 arguments.'); }
		
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
		$TorrentsJson = $this->GetContents($this->Hostname . '/gui/?list=1&token=' . $this->AuthToken);
		$Torrents = json_decode($TorrentsJson, true);
		$Torrents = $Torrents['torrents'];
		
		return $Torrents;
	}
	
	public function GrabLabels()
	{
		$LabelsJson = $this->GetContents($this->Hostname . '/gui/?list=1&token=' . $this->AuthToken);
		$Labels = json_decode($LabelsJson, true);
		$Labels = $Labels['labels'];
		
		return $Labels;
	}
	
	public function CheckStatusCode($_torrentStatuscode, $_statuscode)
	{
	
		return ( ($_statuscode == ($_torrentStatuscode & $_statuscode)) ? true : false);
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
	
	private function GetContents($Url)
	{
		curl_setopt($this->Crl, CURLOPT_URL, $Url);
		curl_setopt($this->Crl, CURLOPT_HEADER, true);
		curl_setopt($this->Crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->Crl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->Crl, CURLOPT_USERPWD, $this->Username . ':' . $this->Password);
		curl_setopt($this->Crl, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($this->Crl, CURLOPT_COOKIEFILE, 'cookie.txt');
		
		$Ret = curl_exec($this->Crl);
		
		
		switch (curl_getinfo($this->Crl, CURLINFO_HTTP_CODE))
		{
			case 0:
				die('Error: Could not connect to remote server.');
			
			case 401:
				die('Error: Authorization required. Please enter a valid username and/or password.');
		}
		
		return $Ret;
	}
	
	private function GetToken()
	{
		return strip_tags($this->GetContents('http://' . $this->Username . ":" . $this->Password . "@" . $this->Hostname . '/gui/token.html'));
	}
}
?>