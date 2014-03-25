haskell_prelude_for_php
=======================

<h3>Maybe</h3>
<pre>
$m = Maybe::inject([1, 2, 3, 4, 5])
    ->bind(function ($xs) { return $xs === [] ? Nothing() : Just($xs); })
    ->fmap('array_map', function ($x) { return $x * 2; });
var_dump($m);
// Just [2, 4, 6, 8, 10]

$m = Maybe::inject([])
    ->bind(function ($xs) { return $xs === [] ? Nothing() : Just($xs); })
    ->fmap('array_map', function ($x) { return $x * 2; });
var_dump($m);
// Nothing
</pre>

maybe($default, callable $callback, Maybe $m);
<pre>
$num = maybe(0, function ($data) { return $data; }, Just(100));
var_dump($num);
// int 100

$num = maybe(0, function ($data) { return $data; }, Nothing());
var_dump($num);
// int 0
</pre>

<h3>Either</h3>
<pre>
$e = Either::inject([1, 2, 3, 4, 5])
    ->bind(function ($xs) { return $xs === [] ? Left('empty') : Right($xs); })
    ->fmap('array_map', function ($x) { return $x * 2; });
var_dump($e);
// Right [2, 4, 6, 8, 10]

$e = Either::inject([])
    ->bind(function ($xs) { return $xs === [] ? Left('empty') : Right($xs); })
    ->fmap('array_map', function ($x) { return $x * 2; });
var_dump($e);
// Left 'empty'
</pre>

either(callable $left, callable $right, Either $e);
<pre>
</pre>

<h3>List</h3>
map
<pre>
</pre>

filter
<pre>
</pre>

foldl
<pre>
</pre>

foldr
<pre>
</pre>
