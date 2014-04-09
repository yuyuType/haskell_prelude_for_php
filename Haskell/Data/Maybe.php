<?php

require_once(dirname(dirname(__FILE__)).'/GHC/Base.php');

class Maybe implements Functor, Monad, MonadPlus {
    public function fmap(callable $f) {
        $args = func_get_args();
        $f = array_shift($args);
        if ($this instanceof Nothing) {
            return $this;
        } else if ($this instanceof Just) {
            return Just(call_user_func_array($f, array_merge($args, [$this->value()])));
        }
    }
    
    public static function inject($x) { return Just($x); }
    public function fail($x) { return Nothing(); }
    public function bind(callable $f) {
        $args = func_get_args();
        $f = array_shift($args);
        if ($this instanceof Nothing) {
            return $this;
        } else if ($this instanceof Just) {
            return call_user_func_array($f, array_merge($args, [$this->value()]));
        }
    }
    public function cbind(Monad $m) { return $m; }
    
    public function mzero() { return new Nothing(); }
    public function mplus(MonadPlus $m) {
        if ($this instanceof Nothing) {
            return $m;
        } else if ($this instanceof Just) {
            return $this;
        }
    }
    
    function guard($b) {
        return $b ? Maybe::inject() : Maybe::mzero();
    }
}

class Just extends Maybe {
    private $value;
    public function __construct($value) { $this->value = $value; }
    public function value() { return $this->value; }
}
class Nothing extends Maybe {
    private static $instance = null;
    public static function getinstance() {
        if (self::$instance === null) {
            self::$instance = new Nothing();
        }
        return self::$instance;
    }
}

function Just($x = null) { return new Just($x); }
function Nothing() { return Nothing::getinstance(); }
function maybe($n, callable $f, Maybe $m) {
    if ($m instanceof Nothing) {
        return $n;
    } else if ($m instanceof Just) {
        return call_user_func($f, $m->value());
    }
}
function isJust(Maybe $m) { return $m instanceof Just; }
function isNothing(Maybe $m) { return $m instanceof Nothing; }
function fromJust(Maybe $m) {
    if ($m instanceof Nothing) {
        exit("Maybe.fromJust: Nothing");
    } else if ($m instanceof Just) {
        return $m->value();
    }
}
function fromMaybe($d, Maybe $m) {
    if ($m instanceof Nothing) {
        return $d;
    } else if ($m instanceof Just) {
        return $m->value();
    }
}
function maybeToList(Maybe $m) {
    if ($m instanceof Nothing) {
        return [];
    } else if ($m instanceof Just) {
        return [$m->value()];
    }
}
function listToMaybe($a) {
    if (!$a) {
        return Nothing();
    } else {
        return head($a);
    }
}
function catMaybes($a) {
    $ls = [];
    foreach ($a as $x) {
        if ($x instanceof Just) {
            $ls[] = $x->value();
        }
    }
    return $ls;
}
function mapMaybe(callable $f, $a) {
    if (!$a) {
        return [];
    }
    $rs = [];
    foreach ($a as $x) {
        $m = call_user_func($f, $x);
        if ($m instanceof Just) {
            $rs[] = $m->value();
        }
    }
    return $rs;
}


