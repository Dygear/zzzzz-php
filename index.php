<?php

function contains(string $string, array $array): bool {
    foreach($array as $element) {
        if (false !== stripos($string, $element)) {
            return true;
        }
    }
    return false;
}

if (!empty($_POST['dateStart']) AND !empty($_POST['dateEnd'])) {
    $dateStart = ((new DateTime($_POST['dateStart'], new DateTimeZone('America/New_York')))->setTimezone(new DateTimeZone('UTC')))->getTimestamp();
    $dateEnd = ((new DateTime($_POST['dateEnd'], new DateTimeZone('America/New_York')))->setTimezone(new DateTimeZone('UTC')))->getTimestamp();
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Stalky</title>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    </head>
    <body>
        <div id="container">
            <main>
                <nav class="navbar navbar-inverse navbar-fixed-top">
                    <div class="container">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a class="navbar-brand" href="/">Stalky</a>
                        </div>
                        <div id="navbar" class="navbar-collapse collapse">
                            <ul class="nav navbar-nav">
                                <li class="active"><a href="/">Home</a></li>
                                <li><a href="//github.com/Dygear/zzzzz-php">Fork On Github!</a></li>
                            </ul>
                        </div><!--/.nav-collapse -->
                    </div>
                </nav>
                <div class="container theme-showcase" role="main">
<?php   if (isset($_POST['submit']) AND !empty($_POST['dateStart']) AND !empty($_POST['dateEnd']) AND !empty($_POST['uid']) AND $_POST['submit'] == 'RAW'):
            foreach (scandir(__DIR__ . '/raw/') as $i => $fileName) {
                if ($fileName == '.' OR $fileName == '..') {
                    continue;
                }

                $timeStamp = explode('.', $fileName)[0];
                if ($timeStamp > $dateStart AND $timeStamp < $dateEnd) {
                    $file = file_get_contents(__DIR__ . '/raw/' . $fileName);

                    if (!contains($file, $_POST['uid'])) {
                        continue;
                    }
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre><?=print_r($file, true);?></pre>
<?php
                }
            }
        elseif (isset($_POST['submit']) AND !empty($_POST['dateStart']) AND !empty($_POST['dateEnd']) AND !empty($_POST['uid']) AND $_POST['submit'] == 'JSON'):
            foreach (scandir(__DIR__ . '/raw/') as $i => $fileName) {
                if ($fileName == '.' OR $fileName == '..') {
                    continue;
                }

                $timeStamp = explode('.', $fileName)[0];
                if ($timeStamp > $dateStart AND $timeStamp < $dateEnd) {
                    $file = file_get_contents(__DIR__ . '/raw/' . $fileName);

                    if (!contains($file, $_POST['uid'])) {
                        continue;
                    }

                    $json = json_decode(substr($file, 10), true);

?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre><?=print_r($json);?></pre>
<?php
                }
            }
        elseif (isset($_POST['submit']) AND !empty($_POST['dateStart']) AND !empty($_POST['dateEnd']) AND !empty($_POST['uid'])):
            $uid = $_POST['uid'][0];
            foreach (scandir(__DIR__ . '/raw/') as $i => $fileName) {
                if ($fileName == '.' OR $fileName == '..') {
                    continue;
                }

                $timeStamp = explode('.', $fileName)[0];
                if ($timeStamp > $dateStart AND $timeStamp < $dateEnd) {
                    $file = file_get_contents(__DIR__ . '/raw/' . $fileName);

                    if (!contains($file, $_POST['uid'])) {
                        continue;
                    }

                    $json = json_decode(substr($file, 10), true);

                    foreach ($json['ms'] as $message):
                        switch($message['type']):
                            case 'chatproxy-presence':
                                if (isset($message['buddyList'][$uid]['lat'])):
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre>Last Active @ <?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($message['buddyList'][$uid]['lat']))->format('Y-m-d H:i:s');?></pre>
<?php
                                endif;
                            break;
                            case 'buddylist_overlay':
                                if (isset($message['overlay'][$uid]['la'])):
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre>Last Active @ <?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($message['overlay'][$uid]['la']))->format('Y-m-d H:i:s');?> - Status <?=$message['overlay'][$uid]['s']?></pre>
<?php
                                endif;
                            break;
                            case 'm_notification':
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre>Notification - <?=$message['data']['body']['__html']?></pre>
<?php
                            break;
                            case 'delta':
                                switch ($message['delta']['class']):
                                    case 'NewMessage':
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre>New Message @ <?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($message['ofd_ts'] / 1000))->format('Y-m-d H:i:s');?> - (From: <?=$message['delta']['messageMetadata']['actorFbId']?> To: <?=$message['delta']['messageMetadata']['threadKey']['otherUserFbId']?>) <?=$message['delta']['body']?></pre>
<?php
                                    break;
                                    case 'ReplaceMessage':
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre>Replace Message @ <?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($message['ofd_ts'] / 1000))->format('Y-m-d H:i:s');?> - (From: <?=$message['delta']['newMessage']['messageMetadata']['actorFbId']?> To: <?=$message['delta']['newMessage']['messageMetadata']['threadKey']['otherUserFbId']?>) <?=$message['delta']['newMessage']['body']?></pre>
<?php
                                    break;
                                    case 'DeliveryReceipt':
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre>Delivery Receipt @ <?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($message['ofd_ts'] / 1000))->format('Y-m-d H:i:s');?> - (To: <?=$message['delta']['threadKey']['otherUserFbId']?>) DeliveryReceipt</pre>
<?php
                                    break;
                                    default:
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre><?=print_r($message, true);?></pre>
<?php
                                endswitch;
                            break;
                            default;
?>
                    <label><?=((new DateTime(null, new DateTimeZone('America/New_York')))->setTimestamp($timeStamp))->format('Y-m-d H:i:s')?></label>
                    <pre><?=print_r($message, true);?></pre>
<?php
                        endswitch;
                    endforeach;
                    echo '              <hr />';
                }
            }

        else:   ?>
                    <!-- Main jumbotron for a primary marketing message or call to action -->
                    <div class="jumbotron">
                        <h1>Pick People (One or More)<br />&amp; A Time Frame</h1>
                    </div>
                    <pre><?=print_r($_POST, true);?></pre>
                    <form target="_self" method="post">
                        <datalist id="userIds">
<?php       $names = (function($fileName = 'NAMES.txt') {
                if (!file_exists(__DIR__ . '/' . $fileName)) {
                    return [];
                }
                foreach (file(__DIR__ . '/' . $fileName) as $line) {
                    list($uid, $name) = explode('=', $line);
                    $names[$uid] = $name;
                }
                return $names;
            })();
            foreach (scandir(__DIR__ . '/log/') as $i => $fileName):
                // If $i > 1 because we don't want the `.` and `..` directories that are at index 0 and 1.
                if ($i > 1):
                    $uid = explode('.', $fileName)[0]; ?>
                            <option value="<?=$uid?>"><?=(isset($names[$uid])) ? $names[$uid] : '' ?></option> 
<?php           endif;
            endforeach; ?>
                        </datalist>
<?php       $uids = array_merge((isset($_POST['uid'])) ? $_POST['uid'] : [], []);
            foreach ($uids as $uid):    ?>
                        <input class="form-input" type="text" name="uid[]" list="userIds" value="<?=$uid?>" /><br />
<?php       endforeach; ?>
                        <button class="btn btn-success" type="submit" name="uid[]">Add User</button>
                        <hr />
                        <input class="form-input" type="datetime-local" name="dateStart" min="0" max="<?=PHP_INT_MAX?>" value="<?=(isset($_POST['dateStart'])) ? $_POST['dateStart'] : null; ?>" />
                        <input class="form-input" type="datetime-local" name="dateEnd" min="0" max="<?=PHP_INT_MAX?>" value="<?=(isset($_POST['dateEnd'])) ? $_POST['dateEnd'] : null; ?>" />
                        <hr />
                        <button class="btn btn-primary" type="submit" name="submit">Search</button>
                        <button class="btn btn-info" type="submit" name="submit" value="RAW">RAW</button>
                        <button class="btn btn-info" type="submit" name="submit" value="JSON">JSON</button>
                    </form>
<?php   endif;  ?>
                </div><!-- /.container -->
                <script src="//code.jquery.com/jquery-1.12.4.min.js" integrity="sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ" crossorigin="anonymous"></script>
                <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
                <footer class="footer">
                    <div class="container">
                        <p class="text-muted">
                            &copy; <?=date('Y')?> Because That's Not Creepy At All, LLC.
                        </p>
                    </div>
                </footer>
            </main>
        </div>
    </body>
</html>