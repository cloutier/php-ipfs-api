IPFS API wrapper library in PHP
======================================

> A client library for the IPFS API.

Good news everyone! [S3r3nity](http://www.s3r3nity.com/) is sponsoring the development of this library.
They will be using it inside one of their (unannounced) projects and its maintainer is hired
to implement all the missing api calls (including the new file api) before the end of September 2016.

**Warning:** Changes will be made from user's suggestions and this warning will be removed when everything is stable.
First stable release is scheduled for the end of September 2016.

# Usage

## Installing 

This library requires the cURL module:

```bash
$ sudo apt-get install php5-curl
$ composer require cloutier/php-ipfs-api
$ composer install
```

```PHP
use Cloutier\PhpIpfsApi\IPFS;

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

The MIT License (MIT)

Copyright (c) 2015-2016 Vincent Cloutier  
Copyright (c) 2016 S3r3nity Technologies 

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
