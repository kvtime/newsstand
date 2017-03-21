<h1 class="page-title"><a href="/@/">Управление</a>: <?=($issue) ? 'изменение номера' : 'добавление номера'?></h1>
<div class="new-issue-dropzone">
    <div class="issue-upload-form-preloader"></div>
    <img src="/covers/<?=$issue->file?>.jpg" alt="" class="issue-upload-preview <?=($issue->file?'issue-upload-preview-displayed':'')?>">
    <div class="issue-upload-title">Загрузите PDF<br>
    <input type="file" name="issue" class="issue-upload-input"></div>
</div>

<form class="issue-form" action="/@" method="post">
    <input type="hidden" name="id" value="<?=$issue->id?>">
    <p><label>Номер<br>
        <input class="issue-form__number" type="number" name="number" value="<?=($issue->number)? $issue->number : $possibleNewNumber ?>" placeholder="Напишите номер" required>
    </label></p>
    <p><label>Дата выпуска<br>
        <input class="issue-form__date" type="date" name="date" value="<?=($issue->date ? $issue->date->format('Y-m-d'): date('Y-m-d'))?>" required>
    </label></p>
    <input type="hidden" name="filehash" value="<?=$issue->file?>">
    <p><button type="submit" class="btn"><?=($issue) ? 'Обновить' : 'Добавить'?></button></p>
    <?php if ($issue) : ?>
    <p><button class="issue-form__remove-button">удалить</button></p>
    <?php endif; ?>
</form>
