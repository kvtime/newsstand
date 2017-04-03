<?php

error_reporting(E_ALL & ~E_NOTICE);

session_start();

define('APP', dirname(dirname(__FILE__)).'/app/');

require '../vendor/autoload.php';

use \KV\Issue;

$config = include(APP.'config.php');

Flight::set('flight.views.path', APP.'views');
Flight::set('flight.handle_errors', true);

// Database
Flight::register('db', 'PDO', $config['db']);

// Robokassa
Flight::register(
    'payment',
    '\Lexty\Robokassa\Payment',
    [new \Lexty\Robokassa\Auth(
        $config['robokassa']['login'],
        $config['robokassa']['password1'],
        $config['robokassa']['password2'],
        $config['robokassa']['test']
    )]
);
Flight::payment()->setSum($config['cost']);


/**
 * Initialisation for all routes
 */
Flight::route('*', function () use ($config) {
    Flight::view()->set('editor', isset($_SESSION['editor']));
    Flight::view()->set('cost', $config['cost']);

    return true; // go to next route
});

/**
 * Index page
 */
Flight::route('/', function () {
    $sth = Flight::db()->prepare("SELECT * FROM `issues` ORDER BY `date` DESC");
    $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);
    $sth->execute();
    $issues = $sth->fetchAll();

    Flight::render('index', [
        'issues' => $issues,
        'lastIssue' => $issues[0]
    ], 'content');
    Flight::render('layout');
});

/**
 * Issue page
 */
Flight::route('/@year:[0-9]{4}/@number:[0-9]{1,2}', function ($year, $number) {

    $sth = Flight::db()->prepare("
        SELECT * from `issues`
        WHERE `number` = :number
          AND YEAR(`date`) = :year
        LIMIT 1
    ");
    $sth->bindValue(':number', $number);
    $sth->bindValue(':year', $year);
    $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);
    $sth->execute();

    if ($issue = $sth->fetch()) {
        Flight::render('issue', [
            'title' => $issue->getDescription(),
            'issue' => $issue
        ], 'content');

        Flight::render('layout');
    } else {
        Flight::notFound();
    }
});

Flight::route('GET /@year:[0-9]{4}/@number:[0-9]{1,2}/buy', function ($year, $number) {
    $sth = Flight::db()->prepare("
        SELECT * from `issues`
        WHERE `number` = :number
        AND YEAR(`date`) = :year
        LIMIT 1
    ");
    $sth->bindValue(':number', $number);
    $sth->bindValue(':year', $year);
    $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);
    $sth->execute();

    $issue = $sth->fetch();

    if (!$issue) {
        Flight::notFound();
    }

    $sth = Flight::db()->prepare("INSERT INTO `invoices` (`issue`) VALUES (:issue)");
    $sth->bindValue(':issue', $issue->id);
    $sth->execute();

    $invoiceId = Flight::db()->lastInsertId();

    Flight::payment()->setDescription($issue->getDescription());
    Flight::payment()->setId($invoiceId);

    Flight::redirect(Flight::payment()->getPaymentUrl());
});


/**
 * Robokassa result URL
 *
 * Payment service make request to this page when user done payment.
 *
 * Here we update invoice status in database
 *
 * Docs: http://docs.robokassa.ru/#1250
 */
Flight::route('/payment/result', function () {
    if (Flight::payment()->validateResult($_GET)) {
        $sth = Flight::db()->prepare("
            UPDATE `invoices`
            SET `status` = 'success'
            WHERE `invoice_id` = :invoice
        ");
        $sth->bindValue(':invoice', Flight::payment()->getId());
        $sth->execute();

        // We need to return special value
        echo Flight::halt(200, Flight::payment()->getSuccessAnswer());
    }
});

/**
 * Robokassa success and fail page
 *
 * The user can open this page from the payment service
 * to see if everything is good or not.
 *
 * http://docs.robokassa.ru/#1261
 */
Flight::route('/payment/@status:success|fail', function ($status) {

    $signature = htmlspecialchars($_GET['SignatureValue']);

    if ($status == 'success' && Flight::payment()->validateSuccess($_GET)) {
        $sth = Flight::db()->prepare("
            UPDATE `invoices`
            SET `crc` = :signature
            WHERE `invoice_id` = :invoice
        ");
        $sth->bindValue(':signature', $signature);
        $sth->bindValue(':invoice', Flight::payment()->getId());
        $sth->execute();

        $sth = Flight::db()->prepare("
            SELECT * FROM `issues`
            LEFT JOIN `invoices`
            ON `issues`.`id` = `invoices`.`issue`
            WHERE `invoice_id` = :invoice
            LIMIT 1
        ");
        $sth->bindValue(':invoice', Flight::payment()->getId());
        $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);
        $sth->execute();
        $issue = $sth->fetch();

        Flight::render('payment/success', array(
            'issue' => $issue,
            'crc' => $signature
        ), 'content');
    } else {
        Flight::render('payment/fail', null, 'content');
    }

    Flight::render('layout');
});

Flight::route('/download/@hash:[a-z0-9]+', function ($hash) {
    $sth = Flight::db()->prepare("
        SELECT `issue`, `status`, `issues`.`number`, `issues`.`date`, `issues`.`file`
        from `invoices`
        left join `issues`
        on `issues`.`id` = `invoices`.`issue`
        WHERE `crc` = :crc
    ");
    $sth->bindValue(':crc', $hash);
    $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);
    $sth->execute();

    $invoice = $sth->fetch();

    if ($invoice) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$invoice->getDescription().'.pdf"');

        readfile('../archive/'.$invoice->file.'.pdf');
    } else {
        Flight::notFound();
    }
});

// Sitemap.xml
Flight::route('/sitemap.xml', function () {
    header("Content-Type: application/xml");

    $sth = Flight::db()->prepare("SELECT * FROM `issues` ORDER BY `date` DESC");
    $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);

    $sth->execute();

    Flight::render('sitemap', ['issues' => $sth->fetchAll()]);
});

/**
 * Editor specific routes
 */

Flight::route('/%40(/*)', function () use ($config) {
    if (isset($_SERVER['PHP_AUTH_USER']) and
        $_SERVER['PHP_AUTH_USER'] == $config['editor']['login'] and
        $_SERVER['PHP_AUTH_PW'] == $config['editor']['password']
    ) {
        $_SESSION['editor'] = true;

        return true;
    } else {
        header('WWW-Authenticate: Basic realm="Staff only"');
        header('HTTP/1.0 401 Unauthorized');
        echo '<meta http-equiv="refresh" content="0;/">';
    }
});

Flight::route('GET /%40', function () {
    $sth = Flight::db()->prepare("
        SELECT `invoice_id`, `issue`, `status`, `time`,
               `issues`.`number` as 'number',
               `issues`.`date` as 'pubdate'
        from `invoices`
        left join `issues`
        on `issues`.`id` = `issue`
        order by `time` desc
    ");
    $sth->execute();

    $invoices = $sth->fetchAll();

    Flight::render('editor/index', array(
        'invoices' => $invoices
    ), 'content');

    Flight::render('layout');
});

/**
 * Create issue
 *
 * Show empty form
 */
Flight::route('GET /%40/new', function () {
    $sth = Flight::db()->prepare("SELECT * from `issues` ORDER BY `date` DESC LIMIT 1");
    $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);
    $sth->execute();
    $lastIssue = $sth->fetch();

    Flight::render('editor/issue-form', [
        'possibleNewNumber' => $lastIssue->number + 1
    ], 'content');
    Flight::render('layout');
});

/**
 * Edit issue
 *
 * Shows form with issue information
 */
Flight::route('GET /%40/@id:[0-9]+', function ($issueId) {
    $sth = Flight::db()->prepare("SELECT * from `issues` WHERE `id` = :id limit 1");
    $sth->bindValue(':id', $issueId);

    $sth->setFetchMode(PDO::FETCH_CLASS, Issue::class);
    $sth->execute();
    $issue = $sth->fetch();

    if ($issue) {
        Flight::view()->set('issue', $issue);
        Flight::render('editor/issue-form', null, 'content');
        Flight::render('layout');
    } else {
        Flight::notFound();
    }
});


/**
 * Save issue data
 *
 * Adding sended information to database
 */
Flight::route('POST /%40', function () {
    $id = intval($_POST['id']);
    $number = intval($_POST['number']);
    $date = $_POST['date'];
    $filehash = $_POST['filehash'];

    if (!empty($id)) {
        $sth = Flight::db()->prepare("
            UPDATE `issues` SET
            `number` = :number,
            `date` = :date,
            `file` = :file
            WHERE `id` = :id;
        ");
        $sth->bindValue(':id', $id);
    } else {
        $sth = Flight::db()->prepare("
            INSERT INTO `issues`
            (`number`, `date`, `file`) VALUES
            (:number, :date, :file);
        ");
    }

    $sth->bindValue(':number', $number);
    $sth->bindValue(':date', $date);
    $sth->bindValue(':file', $filehash);

    $sth->execute();

    Flight::redirect('/' . (new DateTime($date))->format('Y') . '/' . $number);
});

/**
 * Remove issue
 *
 * Delete issue from database
 */
Flight::route('DELETE /%40/@id:[0-9]+', function ($issueId) {
    $sth = Flight::db()->prepare("DELETE FROM `issues` WHERE `id` = :id LIMIT 1");
    $sth->bindValue(':id', $issueId);
    $sth->execute();

    Flight::halt();
});

/**
 * Upload issue PDF version
 *
 * Save the file and save preview from uploaded PDF
 */
Flight::route('POST /%40/upload', function () use ($config) {

    $tempFile = $_FILES['issue']['tmp_name'];

    if (empty($tempFile)) {
        Flight::json(['success' => false, 'error' => 'No file specified']);
        return false;
    }

    // Generate file hash. This hash will be the file name
    $hash = sha1_file($tempFile);

    // PDF path with folder and extension
    $file = '../archive/' . $hash . '.pdf';

    // PHP function move_uploaded_file not replace existing file.
    // That's why the condition is made here
    if (!file_exists($file)) {
        move_uploaded_file($tempFile, $file);
    }

    // Extract first page from PDF as image
    $im = new Imagick();
    $im->readImage($file . '[0]'); // [0] means «only first page»
    $im->setImageFormat('jpg');
    $im->writeImage('covers/'.$hash.'.jpg');

    Flight::json([
        'success' => true,
        'hash' => $hash
    ]);
});


Flight::map('notFound', function () {
    Flight::render('404', ['title' => 'Страница не найдена'], 'content');
    Flight::render('layout');

    Flight::stop(404);
});

Flight::map('month2Name', function ($monthNumber, $plural = false) {
    if ($plural) {
        $months = [
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря'
        ];
    } else {
        $months = [
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь'
        ];
    }
    return $months[$monthNumber-1];
});

Flight::start();
