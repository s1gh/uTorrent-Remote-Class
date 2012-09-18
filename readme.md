uTorrent Remote PHP Class
=========================

Communicate directly with uTorrent's Web Interface with this easy-to-use PHP Class.

Features
--------

* Get a list containing information about every torrent added in uTorrent.
* Get information about a specific torrent using it's unique hash.
* Use actions to add, delete, start, stop, pause etc. torrents in uTorrent.
* Convert from bytes to kB, MB & GB.
* Convert from bytes per second to kB/s, MB/s & GB/s.

How To Use
----------
Read wiki for more information.

Requirements
------------
* uTorrent.
* Web server w/ PHP installed.

Configuration, uTorrent
-----------------------
1. Options -> Advanced -> Web UI.
2. Enable Web UI.
3. Enter the username and password you want to use.
4. Optional: Enter an alternative listening port.

How To Use
----------
`<?php $uTorrent = new uTorrentRemote(<Hostname>, <uTorrent-Username>, <uTorrent-Password>); ?>`  
`<?php $uTorrent->FunctionOfYourChoice(); ?>`

List Of Methods
---------------
`GrabTorrents()`  
    Returns an array with a detailed list of all torrents.  
    The list contains the following information:
* HASH (string)
* STATUS (integer)
* NAME (string)
* SIZE (integer in bytes)
* PERCENT PROGRESS (integer in per mils)
* DOWNLOADED (integer in bytes)
* UPLOADED (integer in bytes)
* RATIO (integer in per mils)
* UPLOAD SPEED (integer in bytes per second)
* DOWNLOAD SPEED (integer in bytes per second)
* ETA (integer in seconds)
* LABEL (string)
* PEERS CONNECTED (integer)
* PEERS IN SWARM (integer)
* SEEDS CONNECTED (integer)
* SEEDS IN SWARM (integer)
* AVAILABILITY (integer in 1/65535ths)
* TORRENT QUEUE ORDER (integer)
* REMAINING (integer in bytes)

`GrabLabes()`  
    Returns an array with labels used by uTorrent.

`GrabListOfFiles($_torrentHash)`  
    Uses a torrent hash to return a list of all files (parts).
    
`GrabTorrentProperties($_torrentHash)`  
    Returns a list of properties for a specific torrent.  
    The list contains the following information:
* "hash" : HASH (string)
* "trackers" : TRACKERS (string)
* "ulrate" : UPLOAD LIMIT (integer in bytes per second)
* "dlrate" : DOWNLOAD LIMIT (integer in bytes per second)
* "superseed" : INITIAL SEEDING (integer)
* "dht" : USE DHT (integer)
* "pex" : USE PEX (integer)
* "seed_override" : SEED QUEUEING (integer)
* "seed_ratio" : SEED RATIO (integer in per mils)
* "seed_time" : SEED TIME (integer in seconds)
* "ulslots" : UPLOAD SLOTS (integer)

`ExecAction($_action, $_torrentHash [, $_priority [, $_fileIndex [, $_torrentURl ]]])`  
    Sends a specific get request (action) to uTorrent's Web Interface.  
    List of available actions:
###### start  
    This action tells µTorrent to start the specified torrent job(s). 
    Multiple hashes may be specified to act on multiple torrent jobs.
###### stop  
    This action tells µTorrent to stop the specified torrent job(s). 
    Multiple hashes may be specified to act on multiple torrent jobs.
###### pause  
    This action tells µTorrent to pause the specified torrent job(s). 
    Multiple hashes may be specified to act on multiple torrent jobs.
###### forcestart  
    This action tells µTorrent to force the specified torrent job(s) to start. 
    Multiple hashes may be specified to act on multiple torrent jobs.
###### unpause  
    This action tells µTorrent to unpause the specified torrent job(s). 
    Multiple hashes may be specified to act on multiple torrent jobs.
###### recheck  
    This action tells µTorrent to recheck the torrent contents for the specified torrent job(s). 
    Multiple hashes may be specified to act on multiple torrent jobs.
###### remove  
    This action removes the specified torrent job(s) from the torrent jobs list. 
    Multiple hashes may be specified to act on multiple torrent jobs. 
    This action respects the option "Move to trash if possible".
###### removedata  
    This action removes the specified torrent job(s) from the torrent jobs list 
    and removes the corresponding torrent contents (data) from disk. 
    Multiple hashes may be specified to act on multiple torrent jobs. 
    This action respects the option "Move to trash if possible".
###### setprio [$_priority = PRIORITY] [$_fileIndex = FILEINDEX]  
    This action sets the priority for the specified file(s) in the torrent job. 
    The possible priority levels are the values returned by "getfiles". 
    A file is specified using the zero-based index of the file in the inside the list returned by "getfiles". 
    Only one priority level may be specified on each call to this action, but multiple files may be specified.
###### add-url [$_torrentUrl]  
    This action adds a torrent job from the given URL. 
    For servers that require cookies, cookies can be sent with the :COOKIE: method. 
    The string must be URL-encoded.
###### add-file  
    N/A