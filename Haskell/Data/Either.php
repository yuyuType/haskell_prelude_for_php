<?php

require_once(dirname(dirname(__FILE__)).'/GHC/Base.php');

class Either implements Functor, Monad {
    public function fmap(callable $f) {
        if ($this instanceof Left) {
            return $this;
        } else if ($this instanceof Right) {
            return Right(call_user_func($f, $this->value()));
        }
    }
    
    public static function inject($x) { return Right($x); }
    public function fail($x) { exit(); }
    public function bind(callable $f) {
        $args = func_get_args();
        $f = array_shift($args);
        if ($this instanceof Left) {
            return $this;
        } else if ($this instanceof Right) {
            return call_user_func_array($f, array_merge($args, [$this->value()]));
        }
    }
    public function cbind(Monad $m) { return $m; }
}

class Left extends Either {
    private $value;
    public function __construct($value) { $this->value = $value; }
    public function value() { return $this->value; }
}

class Right extends Either {
    private $value;
    public function __construct($value) { $this->value = $value; }
    public function value() { return $this->value; }
}

function Left($x) { return new Left($x); }
function Right($x) { return new Right($x); }
function either(callable $f, callable $g, Either $e) {
    if ($e instanceof Left) {
        return call_user_func($f, $e->value());
    } else if ($e instanceof Right) {
        return call_user_func($g, $e->value());
    }
}
function lefts($a) {
    $ls = [];
    foreach ($a as $x) {
        if ($x instanceof Left) {
            $ls[] = $x->value();
        }
    }
    return $ls;
}
function rights($a) {
    $ls = [];
    foreach ($a as $x) {
        if ($x instanceof Right) {
            $ls[] = $x->value();
        }
    }
    return $ls;
}
function partitionEithers($a) {
    return foldr(function ($e, $r) {
        $left = function ($t) use ($r) { $r[0][] = $t; return $r; };
        $right = function ($t) use ($r) { $r[1][] = $t; return $r; };
        return either($left, $right, $e);
    }, [[], []], $a);
}


