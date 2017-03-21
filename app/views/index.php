<section class="last-issue clearfix">
    <h2 class="last-issue__title">Свежий выпуск</h2>
    <h3 class="last-issue__description"><strong>№&nbsp;<?=$lastIssue->number?></strong> от <?=$lastIssue->prettyDate()?></h3>
    <p><a href="<?=$lastIssue->year?>/<?=$lastIssue->number?>"><img src="/covers/<?=$lastIssue->file?>.jpg" alt="Качканарское время №<?=$lastIssue->number?>" class="last-issue__cover"></a></p>
    <p><a class="btn" href="/<?=$lastIssue->date->format('Y')?>/<?=$lastIssue->number?>/buy">Купить</a> за <?=$cost?> рублей</p>
</section>

<div class="issues-list">
    <?php
    $previousIssue = null;
    foreach ($issues as $issue) {
        if ($issue->year != $previousIssue->year and $issue->year != date('Y')) {
            echo '<h2 class="issues-list__year-title"><a name="'.$issue->year.'">'.$issue->year.'</a></h2>';
        }

        if ($issue->month != $previousIssue->month) {
            echo '<h3 class="issues-list__month-title">'.Flight::month2Name($issue->month).'</h3>';
        }

        ?>
        <a href="/<?=$issue->date->format('Y')?>/<?=$issue->number ?>" class="issue">
            <img class="issue__cover" src="/covers/<?=$issue->file?>.jpg" alt="<?=$issue->getDescription()?>">
            <div class="issue__description">
                <strong>№ <?=$issue->number?></strong>
                <span class="issue__description__date">
                    <?=$issue->date->format('j')?>
                    <?=Flight::month2Name($issue->month, true)?>
                </span>
            </div>
        </a>
        <?php
        $previousIssue = $issue;
    }
    ?>
</div>
