<h1 class="page-title">Управление</h1>

<p style="text-align: right"><a href="/@/new" class="btn">Добавить новый номер</a></p>

<div class="invoices">
    <?php foreach ($invoices as $invoice) :
        $time = new DateTime($invoice['time']);
        $pubdate = new Datetime($invoice['pubdate']);
        ?>
        <div class="invoice invoice_status_<?=$invoice['status']?>">
            <span class="invoice__id">#<?=$invoice['invoice_id']?></span>
            <span class="invoice__description"><strong>№<?=$invoice['number']?></strong> от <?=$invoice['pubdate']?></span>
            <time class="invoice__time" datetime="<?=$time->format(DateTime::W3C)?>"><?=$time->format('d.m h:i')?></time>
        </div>
    <?php endforeach; ?>
</div>
