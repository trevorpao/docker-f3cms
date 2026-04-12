<?php

require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/press.php';

$context = tests_bootstrap_f3cms();
$f3 = $context['f3'];

$pressId = isset($argv[1]) ? (int) $argv[1] : 910902;
$targetStatus = isset($argv[2]) ? trim((string) $argv[2]) : \F3CMS\fPress::ST_PUBLISHED;

tests_f3cms_prepare_press_publish_smoke($f3, $pressId, $targetStatus);

$reaction = new \F3CMS\rPress();
$reaction->do_published($f3, []);