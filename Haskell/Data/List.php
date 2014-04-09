<?php

require_once(dirname(dirname(__FILE__)).'/GHC/Base.php');

function filter(callable $p, $iterator) {
    if (is_string($iterator)) {
        return iterator_to_string(new FilteredIterator($p, new StringIterator($iterator)), false);
    } else if (is_array($iterator)) {
        return iterator_to_array(new FilteredIterator($p, new ArrayIterator($iterator)), false);
    }
    return new FilteredIterator($p, $iterator);
}
function head($xs) {
    return not(array_null($xs)) ? Just($xs[0]) : Nothing();
}
function tail($iterator) {
    if (is_string($iterator)) {
        return $iterator ? Just(mb_substr($iterator, 1, length($iterator) - 1)) : Nothing();
    } else if (is_array($iterator)) {
        return $iterator ? Just(array_slice($iterator, 1, length($iterator) - 1)) : Nothing();
    } else if ($iterator instanceof Iterator) {
        $iterator->next();
        $iterator = $iterator;
        return Just($iterator->new_iterator());
    }
}
function last($xs) {
    $len = length($xs);
    return $xs ? Just($xs[$len - 1]) : Nothing();
}
function init($iterator) {
    if (is_array($iterator)) {
        return $iterator ? Just(array_slice($iterator, 0, length($iterator) - 1)) : Nothing();
    } else if (is_string($iterator)) {
        return $iterator ? Just(mb_substr($iterator, 0, length($iterator) - 1)) : Nothing();
    }
}
function array_null($xs) {
    if ($xs instanceof Iterator) { return !$xs->valid(); }
    return $xs ? false : true;
}
function length($iterator) {
    if (is_array($iterator)) { return count($iterator); }
    if (is_string($iterator)) { return mb_strlen($iterator); }
    if ($iterator instanceof Iterator) { return iterator_count($iterator); }
}
function foldl1(callable $f, $a) {
    if (!$a) { exit("foldl1: empty list"); }
    return foldl($f, fromJust(head($a)), fromJust(tail($a)));
}
function scanl(callable $f, $q, $xs) {
    if (!$xs) {
        return [is_string($xs) ? '' : []];
    }
    $len = length($xs);
    $r[] = $q;
    for ($i = 0; $i < $len; ++$i) {
        $q = call_user_func($f, $q, $xs[$i]);
        $r[] = $q;
    }
    return $r;
}
function foldr1(callable $f, $a) {
    if (!$a) { exit("foldr1: empty list"); }
    return foldr($f, fromJust(head($a)), fromJust(tail($a)));
}
function scanr(callable $f, $q, $xs) {
    if (!$xs) {
        return [is_string($xs) ? '' : []];
    }
    $len = length($xs);
    $r[] = $q;
    for ($i = $len - 1; $i >= 0; --$i) {
        $q = call_user_func($f, $xs[$i], $q);
        $r = cons($q, $r);
    }
    return $r;
}
function iterate(callable $f, $x) {
    return new IterateIterator($f, $x);
}
function repeat($x) {
    return new RepeatIterator($x);
}
function replicate($n, $x) {
    return take($n, repeat($x));
}
function cycle($xs) {
    if (!$xs) { exit("cycle: empty list"); }
    return new CycleIterator($xs);
}
function takeWhile(callable $p, $xs) {
    $r = is_string($xs) ? '' : ($xs instanceof Iterator && is_string($xs->current()) ? '' : []);
    if ($xs instanceof Iterator) {
        foreach($xs as $i => $x) {
            if (!call_user_func($p, $xs[$i])) {
                return take($i, $xs);
            }
        }
    } else {
        $len = length($xs);
        for($i = 0; $i < $len; ++$i) {
            if (call_user_func($p, $xs[$i])) {
                if (is_string($xs)) {
                    $r .= $xs[$i];
                } else {
                    $r[] = $xs[$i];
                }
            } else {
                return $r;
            }
        }
    }
    return $r;
}
function dropWhile(callable $p, $xs) {
    $r = is_string($xs) ? '' : ($xs instanceof Iterator && is_string($xs->current()) ? '' : []);
    $cond = true;
    if ($xs instanceof Iterator) {
        foreach($xs as $i => $x) {
            $cond &= call_user_func($p, $xs[$i]);
            if (!$cond) {
                return $xs->new_iterator();
            }
        }
    } else {
        $len = length($xs);
        for($i = 0; $i < $len; ++$i) {
            $cond &= call_user_func($p, $xs[$i]);
            if (!$cond) {
                if (is_string($xs)) {
                    $r .=  $xs[$i];
                } else {
                    $r[] =  $xs[$i];
                }
            }
        }
    }
    return $r;
}
function take($n, $xs) {
    if ($n <= 0) {
        return is_string($xs) ? '' : ($xs instanceof Iterator && is_string($xs->current()) ? '' : []);
    }
    $r = is_string($xs) ? '' : ($xs instanceof Iterator && is_string($xs->current()) ? '' : []);
    if ($xs instanceof Iterator) {
        foreach($xs as $i => $x) {
            if ($i === $n) {
                break;
            } else {
                if (is_string($xs->current())) {
                    $r .= $x;
                } else {
                    $r[] = $x;
                }
            }
        }
    } else {
        $len = length($xs);
        for($i = 0; $i < $len; ++$i) {
            if ($i === $n) {
                break;
            } else {
                if (is_string($xs)) {
                    $r .= $xs[$i];
                } else {
                    $r[] = $xs[$i];
                }
            }
        }
    }
    return $r;
}
function drop($n, $xs) {
    if ($n <= 0) {
        return $xs;
    }
    $r = is_string($xs) ? '' : ($xs instanceof Iterator && is_string($xs->current()) ? '' : []);
    if ($xs instanceof Iterator) {
        foreach($xs as $i => $x) {
            if ($i < $n) {
                continue;
            } else {
                return $xs->new_iterator();
            }
        }
    } else {
        $len = length($xs);
        for($i = 0; $i < $len; ++$i) {
            if ($i < $n) {
                continue;
            } else {
                if (is_string($xs)) {
                    $r .= $xs[$i];
                } else {
                    $r[] = $xs[$i];
                }
            }
        }
    }
    return $r;
}
function splitAt($n, $xs) {
    return [take($n, $xs), drop($n, $xs)];
}
function span(callable $p, $xs) {
    if ($xs instanceof Iterator) {
        return iterator_span($p, $xs);
    }
    $is_string = is_string($xs);
    $d = $is_string ? '' : [];
    $r = [$d, $d];
    $cond = true;
    $len = length($xs);
    for($i = 0; $i < $len; ++$i) {
        $cond &= call_user_func($p, $xs[$i]);
        if ($cond) {
            if ($is_string) {
                $r[0] .= $xs[$i];
            } else {
                $r[0][] = $xs[$i];
            }
        } else {
            if ($is_string) {
                $r[1] .= $xs[$i];
            } else {
                $r[1][] = $xs[$i];
            }
        }
    }
    return $r;
}
function iterator_span(callable $p, $xs) {
    $is_string = is_string($xs->current());
    $d = $is_string ? '' : [];
    $r = [$d, $d];
    $cond = true;
    foreach($xs as $x) {
        $cond &= call_user_func($p, $x);
        if ($cond) {
            if ($is_string) {
                $r[0] .= $x;
            } else {
                $r[0][] = $x;
            }
        } else {
            $r[1] = $xs->new_iterator();
            break;
        }
    }
    return $r;
}
function array_break(callable $p, $xs) {
    return span(compose('not', $p), $xs);
}
function reverse($xs) {
    $z = $xs instanceof Iterator ? (is_string($xs->current) ? '' : []) : (is_string($xs) ? '' : []);
    return foldl(function ($ys, $y) { return cons($y, $ys); }, $z, $xs);
}
function array_and($xs) {
    $and = function ($a, $b) { return $a && $b; };
    return foldr($and, true, $xs);
}
function array_or($xs) {
    $or = function ($a, $b) { return $a || $b; };
    return foldr($or, false, $xs);
}
function any($p, $xs) {
    return bind(compose('array_or', bind('map', $p)), $xs);
}
function all($p, $xs) {
    return bind(compose('array_and', bind('map', $p)), $xs);
}
function elem($x, $xs) {
    $eq = function ($a, $b) { return $a === $b; };
    return any(bind($eq, $x), $xs);
}
function notElem($x, $xs) {
    $ne = function ($a, $b) { return $a != $b; };
    return all(bind($ne, $x), $xs);
}
function lookup($key, $xys) {
    if (!$xys) {
        return Nothing();
    }
    foreach($xys as $x => $y) {
        if (is_array($y) && length($y) === 2) {
            list($a, $b) = $y;
            if ($key === $a) {
                return Just($b);
            }
        } else if ($key === $x) {
            return Just($y);
        }
    }
    return Nothing();
}
function concatenate($xs, $ys) {
    if (is_string($xs) && is_string($ys)) {
        return $xs.$ys;
    } else if ($xs instanceof Iterator && $ys instanceof Iterator){
        $it = new AppendIterator;
        $it->append($xs);
        $it->append($ys);
        return $it;
    } else {
        return array_merge($xs, $ys);
    }
}
function concatMap(callable $f, $xss) {
    return bind(compose('concat', bind('map', $f)), $xss);
}
function concat($xss) {
    if($xss) {
        $z = is_string($xss[0]) ? '' : [];
    }
    return foldr('concatenate', $z, $xss);
}
function is_iterator($x) {
    return $x instanceof Iterator;
}
function zip($as, $bs) {
    if (any('is_iterator', [$as, $bs])) {
        return new ZipedIterator(null, [$as, $bs]);
    }
    $alen = length($as);
    $blen = length($bs);
    $len = $alen > $blen ? $blen : $alen;
    $r = [];
    for ($i = 0; $i < $len; ++$i) {
        $r[] = [$as[$i], $bs[$i]];
    }
    return $r;
}
function zip3($as, $bs, $cs) {
    if (any('is_iterator', [$as, $bs, $cs])) {
        return new ZipedIterator(null, [$as, $bs, $cs]);
    }
    $alen = length($as);
    $blen = length($bs);
    $clen = length($cs);
    $len = $alen > $blen ? ($blen > $clen ? $clen : $blen) : ($alen > $clen ? $clen : $alen);
    $r = [];
    for ($i = 0; $i < $len; ++$i) {
        $r[] = [$as[$i], $bs[$i], $cs[$i]];
    }
    return $r;
}
function zipWith(callable $f, $as, $bs) {
    if (any('is_iterator', [$as, $bs])) {
        return new ZipedIterator($f, [$as, $bs]);
    }
    $alen = length($as);
    $blen = length($bs);
    $len = $alen > $blen ? $blen : $alen;
    $r = [];
    for ($i = 0; $i < $len; ++$i) {
        $r[] = call_user_func($f, $as[$i], $bs[$i]);
    }
    return $r;
}
function zipWith3(callable $f, $as, $bs, $cs) {
    if (any('is_iterator', [$as, $bs, $cs])) {
        return new ZipedIterator($f, [$as, $bs, $cs]);
    }
    $alen = length($as);
    $blen = length($bs);
    $clen = length($cs);
    $len = $alen > $blen ? ($blen > $clen ? $clen : $blen) : ($alen > $clen ? $clen : $alen);
    $len = $alen > $blen ? ($blen > $clen ? $clen : $blen) : ($alen > $clen ? $clen : $alen);
    $r = [];
    for ($i = 0; $i < $len; ++$i) {
        $r[] = call_user_func($f, $as[$i], $bs[$i], $cs[$i]);
    }
    return $r;
}
function unzip($xys) {
    $f = function ($r, $xy) {
        list($x, $y) = $xy;
        $r[0][] = $x;
        $r[1][] = $y;
        return $r;
    };
    return foldl($f, [[], []], $xys);
}
function unzip3($xyzs) {
    $f = function ($xyz, $r) {
        list($x, $y, $z) = $xyz;
        $r[0] = cons($x, $r[0]);
        $r[1] = cons($y, $r[1]);
        $r[2] = cons($z, $r[2]);
        return $r;
    };
    return foldr($f, [[], [], []], $xyzs);
}
function dropWhileEnd(callable $p, $xs) {
    $f = function ($y, $ys) use ($p) {
        return call_user_func($p, $y) && array_null($ys) ? (is_string($ys) ? '' : []) : cons($y, $ys);
    };
    return foldr($f, is_string($xs) ? '' : [], $xs);
}
function stripPrefix($xs, $ys) {
    $xlen = length($xs);
    $ylen = length($ys);
    for($i = 0; $i < $xlen; ++$i) {
        if ($xs[$i] === $ys[$i]) {
            continue;
        } else {
            return Nothing();
        }
    }
    $r = is_string($xs) ? '' : [];
    for(; $i < $ylen; ++$i) {
        if (is_string($xs)) {
            $r .= $ys[$i];
        } else {
            $r[] = $ys[$i];
        }
    }
    return Just($r);
}
function elemIndex($x, $xs) {
    return findIndex(function ($b) use ($x) { return $x === $b; }, $xs);
}
function elemIndices($x, $xs) {
    return findIndices(bind(function ($a, $b) { return $a === $b; },$x), $xs);
}
function find($p, $xs) {
    return bind(compose('listToMaybe', bind('filter', $p)), $xs);
}
function findIndex($p, $xs) {
    return bind(compose('listToMaybe', bind('findIndices', $p)), $xs);
}
function findIndices($p, $xs) {
    if (is_iterator($xs)) {
        return new IndicesIterator($p, $xs);
    }
    $len = length($xs);
    $r = [];
    for ($i = 0; $i < $len; ++$i) {
        if (call_user_func($p, $xs[$i])) {
            $r[] = $i;
        }
    }
    return $r;
}
function isPrefixOf($xs, $ys) {
    for ($i = 0; true; ++$i) {
        if (not(isset($xs[$i]))) {
            return true;
        } else if (not(isset($ys[$i]))) {
            return false;
        } else {
            if (not($xs[$i] === $ys[$i])) {
                return false;
            }
        }
    }
    return true;
}
function isSuffixOf($xs, $ys) {
    return isPrefixOf(reverse($xs), reverse($ys));
}
function isInfixOf($needle, $hystack) {
    return any(bind('isPrefixOf', $needle), tails($hystack));
}
function nub($xs) {
    return nubBy('EQ', $xs);
}
function nubBy(callable $eq, $xs) {
    $r = is_string($xs) ? '' : [];
    while (true) {
        $x = head($xs);
        if ($x instanceof Just) {
            $x = fromJust($x);
            $xs = fromjust(tail($xs));
            $xs = filter(function ($y) use ($eq, $x) { return not(call_user_func($eq, $x, $y)); }, $xs);
            if (is_string($xs)) {
                $r .= $x;
            } else {
                $r[] = $x;
            }
        } else {
            break;
        }
    }
    return $r;
}


