<<<<<<< HEAD
# Min module

This is the minimum module you can extends to build your own module.

But remember you have to edit every part of the module folders and files to match it to your module name. 

## How to extends

* Rename folder and file names to your module.
* Search 'min' and replace to your module name.


## routing

/min
/min/index
/min/index?abc=def

## layout

## twig variables

data.input

data.page

## How To

### How to return JSON data

You can return code like below.

	private static function send( &$data ) {
		$re = [];
        $re['code'] = 0;
        $response = new JsonResponse( $re );
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
	}


