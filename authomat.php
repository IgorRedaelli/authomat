<?php

class Authomat {
  public static function auth($config, $provider) {
    if (!is_array($config) && !file_exists($config)) {
			throw new Exception("The configuration is neighter an array or a valid file.", 1);
		}

		if (!is_array($config)) {
			$config = include $config;
		}

    if (!isset($config["secret"])) {
      throw new Exception("The configuration does not include a secret key.", 2);
    }

    $redirect = isset($config["redirect"]) ? $config["redirect"] : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $eredirect = isset($config["errorredirect"]) ? $config["errorredirect"] : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    if (!isset($_COOKIE["authomatauthkey"])) {
      $authkey = file_get_contents("http://vantezzen.de/authomat/auth.php?m=getkey&secret=".$config["secret"]."&provider=".$provider."&redirect=".htmlentities($redirect)."&eredirect=".htmlentities($eredirect));
      setcookie("authomatauthkey", $authkey);
      header("Location: http://vantezzen.de/authomat/auth.php?m=auth&authkey=".$authkey);
      exit();
    } else {
      $authkey = $_COOKIE["authomatauthkey"];
      $data = file_get_contents("http://vantezzen.de/authomat/auth.php?m=getdata&secret=".$config["secret"]."&authkey=".$authkey);
      if (empty($data)) {
        $authkey = file_get_contents("http://vantezzen.de/authomat/auth.php?m=getkey&secret=".$config["secret"]."&provider=".$provider."&redirect=".htmlentities($redirect));
        setcookie("authomatauthkey", $authkey);
        header("Location: http://vantezzen.de/authomat/auth.php?m=auth&authkey=".$authkey);
        exit();
      }
      $array = json_decode($data, true);
      file_get_contents("http://vantezzen.de/authomat/auth.php?m=authdone&authkey=".$authkey);
      return $array;
    }
  }
  public static function clearcookie() {
    setcookie("authomatauthkey", "", time() - 3600);
  }
}

 ?>
