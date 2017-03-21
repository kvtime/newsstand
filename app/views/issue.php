<?php if ($editor) : ?>
    <p style="text-align:center"><a href="/@/<?=$issue->id?>" class="btn">изменить</a></p>
<?php endif?>

<h1 class="page-title">Выпуск № <?=$issue->number ?> от <?=$issue->prettyDate();?></h1>

<p style="text-align: center;font-size: 25px;margin: 50px 0"><a href="/<?=$issue->date->format('Y');?>/<?=$issue->number?>/buy" class="btn" rel="nofollow">Купить</a> за <?=$cost?> рублей</p>

<p style="text-align:center"><img src="/covers/<?=$issue->file?>.jpg" alt="" class="issue-cover"></p>

