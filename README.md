IPFS API wrapper library in PHP
======================================

> A client library for the IPFS API.

**Warning:** Changes will be made from user's suggestions and this warning will be removed when everything is stable.  

# Usage

## Installing 

This library requires the cURL module:

```bash
$ sudo apt-get install php5-curl
```

```PHP
include "PathToThisModuleGoesHere/ipfs.class.php";

// connect to ipfs daemon API server
$ipfs = new IPFS("localhost", "8080", "5001"); // leaving out the arguments will default to these values
```

## API


#### add

Adds content to IPFS. 

**Usage**
```PHP
$hash = $ipfs->add("Hello world");
```



#### cat

Retrieves the contents of a single hash.

**Usage**
```PHP
$ipfs->cat($hash);
```

#### ls
Gets the node structure of a hash.

**Usage**
```PHP
$obj = $ipfs->ls($hash);

foreach ($obj as $e) {
	echo $e['Hash'];
	echo $e['Size'];
	echo $e['Name'];
}
```


#### Object size

Returns object size.

**Usage**
```PHP
$size = $ipfs->size($hash);
```

#### Pin

Pins a hash.

**Usage**
```PHP
$ipfs->pinAdd($hash);
```

# License 

Apache 2.0 license. 

