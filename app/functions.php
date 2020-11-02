<?php
// エスケープ処理 jsなどの悪意のあるコードから守る
function h($str)
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}


