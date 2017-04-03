<?='<?xml version="1.0" encoding="UTF-8"?>'?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($issues as $issue) : ?>
    <url>
        <loc>https://<?=$_SERVER['SERVER_NAME']?>/<?=$issue->year?>/<?=$issue->number?></loc>
        <changefreq>monthly</changefreq>
        <lastmod><?=$issue->date->format('c') ?></lastmod>
        <priority>1.0</priority>
    </url>
<?php endforeach;?>
</urlset>
