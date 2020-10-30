<?php
// エスケープ処理 jsなどの悪意のあるコードから守る
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}


