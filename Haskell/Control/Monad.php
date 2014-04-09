<?php

function liftM(callable $f) {
    return function (Monad $m) use ($f) {
        return $m->inject(call_user_func($f, $m->value()));
    };
}
