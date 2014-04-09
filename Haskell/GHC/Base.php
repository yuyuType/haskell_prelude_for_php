<?php

class StringIterator implements Iterator, ArrayAccess {
    private $pos = 0;
    private $str;
    
    public function __construct($str) { $this->str = $str; }
    public function rewind() { $this->pos = 0; }
    public function current() { return $this->str[$this->pos]; }
    public function key() { return $this->pos; }
    public function next() { ++$this->pos; }
    public function valid() { return isset($this->str[$this->pos]); }

    public function offsetSet($offset, $value) {}
    public function offsetExists($offset) { return isset($this->str[$offset]); }
    public function offsetUnset($offset) {}
    public function offsetGet($offset) { return $this->str[$offset]; }
}
function iterator_to_string(Traversable $iterator, $use_keys = true) {
    return implode(iterator_to_array($iterator, $use_keys));
}

class IterateIterator implements Iterator, ArrayAccess {
    private $pos = 0;
    private $f;
    private $d;
    private $x;
    
    public function __construct(callable $f, $x) {
        $this->f = $f;
        $this->d = $x;
        $this->x = $x;
    }
    public function new_iterator() {
        return  new self($this->f, $this->x);
    }
    public function rewind() {
        $this->pos = 0;
        $this->x = $this->d;
    }
    public function current() { return $this->x; }
    public function key() { return $this->pos; }
    public function next() {
        ++$this->pos;
        $this->x = call_user_func($this->f, $this->x);
    }
    public function valid() { return true; }

    public function offsetSet($offset, $value) {}
    public function offsetExists($offset) { return true; }
    public function offsetUnset($offset) {}
    public function offsetGet($offset) {
        $this->rewind();
        for ($i = 0; $i < $offset; ++$i) {
            $this->next();
        }
        return $this->current();
    }
}
class RepeatIterator implements Iterator, ArrayAccess {
    private $pos = 0;
    private $x;
    
    public function __construct($x) { $this->x = $x; }
    public function rewind() { $this->pos = 0; }
    public function current() { return $this->x; }
    public function key() { return $this->pos; }
    public function next() { ++$this->pos; }
    public function valid() { return true; }

    public function offsetSet($offset, $value) {}
    public function offsetExists($offset) { return true; }
    public function offsetUnset($offset) {}
    public function offsetGet($offset) { return $this-x; }
}
class CycleIterator implements Iterator, ArrayAccess {
    private $pos = 0;
    private $len;
    private $xs;
    
    public function __construct($xs) {
        $this->xs = $xs;
        $this->len = length($xs);
    }
    public function new_iterator() {
        $xs = $this->xs;
        for($i = 0; $i < $this->len; ++$i) {
            $xs[$i] = $this->xs[$this->pos++ % $this->len];
        }
        return new self($xs);
    }
    public function rewind() { $this->pos = 0; }
    public function current() { return $this->xs[$this->pos % $this->len]; }
    public function key() { return $this->pos; }
    public function next() { ++$this->pos; }
    public function valid() { return true; }
    
    public function offsetSet($offset, $value) {}
    public function offsetExists($offset) { return true; }
    public function offsetUnset($offset) {}
    public function offsetGet($offset) { return $this->xs[$offset % $this->len]; }
}
class MapedIterator implements Iterator, ArrayAccess {
    private $f;
    private $iterator;
    
    public function __construct($f, $iterator) {
        $this->f = $f;
        $this->iterator = $iterator;
    }
    public function rewind() { $this->iterator->rewind(); }
    public function current() { return call_user_func($this->f, $this->iterator->current()); }
    public function key() { return $this->iterator->key(); }
    public function next() { $this->iterator->next(); }
    public function valid() { return $this->iterator->valid(); }

    public function offsetSet($offset, $value) { $this->iterator->offsetSet($offset, $value); }
    public function offsetExists($offset) { return $this->iterator->offsetExists($offset); }
    public function offsetUnset($offset) { $this->iterator->offsetUnset($offset); }
    public function offsetGet($offset) { return call_user_func($this->f, $this->iterator->offsetGet($offset)); }
}
class FilteredIterator implements Iterator, ArrayAccess {
    private $pos = 0;
    private $p;
    private $iterator;
    
    public function __construct($p, $iterator) {
        $this->p = $p;
        $this->iterator = $iterator;
    }
    public function rewind() {
        $this->pos = 0;
        $this->iterator->rewind();
        while ($this->iterator->valid() && !call_user_func($this->p, $this->iterator->current())) {
            $this->iterator->next();
        }
    }
    public function current() {
        return $this->iterator->current();
    }
    public function key() {
        return $this->pos;
    }
    public function next() {
        ++$this->pos;
        $this->iterator->next();
        while ($this->iterator->valid() && !call_user_func($this->p, $this->iterator->current())) {
            $this->iterator->next();
        }
    }
    public function valid() { return $this->iterator->valid(); }

    public function offsetSet($offset, $value) { $this->iterator->offsetSet($offset, $value); }
    public function offsetExists($offset) { return $this->iterator->offsetExists($offset); }
    public function offsetUnset($offset) { $this->iterator->offsetUnset($offset); }
    public function offsetGet($offset) {
        $this->rewind();
        for ($i = 0; $i < $offset; ++$i) {
            $this->next();
        }
        return $this->current();
    }
}
class ZipedIterator implements Iterator, ArrayAccess {
    private $pos = 0;
    private $f;
    private $iterators;

    public function __construct($f, $iterators) {
        $this->f = $f;
        $this->iterators = $iterators;
    }
    public function rewind() {
        foreach($this->iterators as $iterator) {
            if (is_iterator($iterator)) { $iterator->rewind(); }
        }
        $this->pos = 0;
    }
    public function current() {
        $r = [];
        foreach($this->iterators as $iterator) {
            $r[] = $iterator[$this->pos];
        }
        return is_null($this->f) ? $r : call_user_func_array($this->f, $r);
    }
    public function key() {
        return $this->pos;
    }
    public function next() {
        ++$this->pos;
        foreach($this->iterators as $iterator) {
            if (is_iterator($iterator)) { $iterator->next(); }
        }
    }
    public function valid() {
        foreach($this->iterators as $iterator) {
            if (not(is_iterator($iterator))) {
                return not($this->pos >= length($iterator));
            }
        }
        return true;
    }

    public function offsetSet($offset, $value) {}
    public function offsetExists($offset) {
        $cond = false;
        foreach($this->iterators as $iterator) {
            if (is_iterator($iterator)) {
                $cond |= $iterator->offsetExists($offset);
            } else {
                $cond |= $offset < length($iterator);
            }
        }
        return not($cond);
    }
    public function offsetUnset($offset) {}
    public function offsetGet($offset) {
        $this->rewind();
        for ($i = 0; $i < $offset; ++$i) {
            $this->next();
        }
        return $this->current();
    }
}
class IndicesIterator implements Iterator, ArrayAccess {
    private $pos = 0;
    private $p;
    private $iterator;
    
    public function __construct($p, $iterator) {
        $this->p = $p;
        $this->iterator = $iterator;
    }
    public function rewind() {
        $this->pos = 0;
        $this->iterator->rewind();
        while (!call_user_func($this->p, $this->iterator->current())) {
            $this->iterator->next();
        }
    }
    public function current() { return $this->iterator->key(); }
    public function key() { return $this->pos; }
    public function next() {
        ++$this->pos;
        $this->iterator->next();
        while (!call_user_func($this->p, $this->iterator->current())) {
            $this->iterator->next();
        }
    }
    public function valid() { return $this->iterator->valid(); }

    public function offsetSet($offset, $value) { $this->iterator->offsetSet($offset, $value); }
    public function offsetExists($offset) { return $this->iterator->offsetExists($offset); }
    public function offsetUnset($offset) { $this->iterator->offsetUnset($offset); }
    public function offsetGet($offset) {
        $this->rewind();
        for ($i = 0; $i < $offset; ++$i) {
            $this->next();
        }
        return $this->current();
    }
}


interface Functor {
    public function fmap(callable $f);
}

interface Monad {
    public static function inject($x);
    public function fail($x);
    public function bind(callable $f);
    public function cbind(Monad $m);
}

interface MonadPlus {
    public function mzero();
    public function mplus(MonadPlus $m);
}

function map(callable $f, $iterator) {
    if (is_string($iterator)) {
        return iterator_to_string(new MapedIterator($f, new StringIterator($iterator)), false);
    } else if (is_array($iterator)) {
        return iterator_to_array(new MapedIterator($f, new ArrayIterator($iterator)), false);
    }
    return new MapedIterator($f, $iterator);
}
function foldl(callable $f, $z, $a) {
    $len = length($a);
    for ($i = 0; $i < $len; ++$i) {
        $z = call_user_func($f, $z, $a[$i]);
    }
    return $z;
}
function foldr(callable $f, $z, $a) {
    for ($i = length($a) - 1; $i >= 0; --$i) {
        $z = call_user_func($f, $a[$i], $z);
    }
    return $z;
}

function EQ ($a, $b) {
    return $a === $b;
}

function bind() {
    $args = func_get_args();
    $fn = array_shift($args);
    if (!is_a($fn, 'ReflectionFunction')) {
        $fn = new ReflectionFunction($fn);
    }
    if (count($args) >= $fn->getNumberOfRequiredParameters()) {
        return $fn->invokeArgs($args);
    }
    return function() use ($fn, $args) {
        $args = array_merge($args, func_get_args());
        if (count($args) >= $fn->getNumberOfRequiredParameters()) {
            return $fn->invokeArgs($args);
        } else {
            return call_user_func_array('bind', array_merge([$fn], $args));
        }
    };
}

function not($x) { return !$x; }

function otherwise() { return true; }
function cons($x, $xs) {
    if ($xs instanceof Iterator) {
        $class = get_class($xs);
        $appiter = new AppendIterator();
        $appiter->append(new $class($x));
        $appiter->append($xs);
        return $appiter;
    } else if (is_string($xs)){
        return $x.$xs;
    }
    return array_merge([$x], $xs);
}
function id($x) { return $x; }
function con($x, $_) { return $x; }
function compose(callable $f, callable $g) {
    return function ($x) use ($f, $g) { return call_user_func($f, call_user_func($g, $x)); };
}
function flip(callable $f, $x, $y) { return call_user_func($f, $y, $x); }
function until(callable $p, callable $f, $x) {
    $cond = false;
    for ($z = $x; !$cond; $z = call_user_func($f, $z)) {
        $cond = call_user_func($p, $z);
        if ($cond) {
            return $z;
        }
    }
}

function plus($a, $b) {
    return $a + $b;
}
function subtruct($a, $b) {
    return $b - $a;
}
function multi($a, $b) {
    return $a * $b;
}
function div($a, $b) {
    return $a / $b;
}
function mod($a, $b) {
    return $a % $b;
}

