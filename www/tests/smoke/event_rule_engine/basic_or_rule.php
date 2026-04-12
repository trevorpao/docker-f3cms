<?php

require_once dirname(__DIR__, 2) . '/bootstrap/smoke.php';
require_once dirname(__DIR__, 2) . '/adapters/f3cms/bootstrap.php';

tests_smoke_run(function () {
    $payload = tests_smoke_load_json_fixture('event_rule_engine/basic_or_rule.json');

    $engine = new \F3CMS\EventRuleEngine($payload);
    $engine->validatePayload();

    $matched = $engine->evaluate([
        'member_id' => 101,
        'watched_video_codes' => ['vid_001'],
        'exam_scores' => ['default' => 85],
        'heraldry_codes' => [],
        'member_seen_targets' => [],
        'account_balance' => 100,
        'account_status' => 'ACTIVE',
    ])->toArray();

    $notMatched = $engine->evaluate([
        'member_id' => 102,
        'watched_video_codes' => [],
        'exam_scores' => ['default' => 60],
        'heraldry_codes' => [],
        'member_seen_targets' => [],
        'account_balance' => 0,
        'account_status' => 'ACTIVE',
    ])->toArray();

    $invalidPayloadEngine = new \F3CMS\EventRuleEngine([
        'operator' => 'AND',
        'rules' => [],
    ]);
    $invalidPayload = $invalidPayloadEngine->evaluate([
        'member_id' => 103,
        'watched_video_codes' => [],
        'exam_scores' => ['default' => 0],
        'heraldry_codes' => [],
        'member_seen_targets' => [],
        'account_balance' => 0,
        'account_status' => 'ACTIVE',
    ])->toArray();

    $partialRegistry = \F3CMS\EventRuleEngine::createRegistryForTypes(['WATCHED_VIDEO', 'EXAM_SCORE']);
    $missingEvaluatorEngine = new \F3CMS\EventRuleEngine($payload, [
        'registry' => $partialRegistry,
    ]);
    $missingEvaluator = $missingEvaluatorEngine->evaluate([
        'member_id' => 104,
        'watched_video_codes' => [],
        'exam_scores' => ['default' => 90],
        'heraldry_codes' => ['badge_novice'],
        'member_seen_targets' => [],
        'account_balance' => 0,
        'account_status' => 'ACTIVE',
    ])->toArray();

    $contextError = $engine->evaluate([
        'member_id' => 105,
        'watched_video_codes' => ['vid_001'],
        'exam_scores' => ['default' => 90],
        'account_balance' => 0,
        'account_status' => 'ACTIVE',
    ])->toArray();

    if (true !== $matched['matched'] || 'matched' !== $matched['result_type']) {
        throw new \RuntimeException('Expected matched payload evaluation result.');
    }

    if (false !== $notMatched['matched'] || 'not_matched' !== $notMatched['result_type']) {
        throw new \RuntimeException('Expected not_matched payload evaluation result.');
    }

    if ('invalid_payload' !== $invalidPayload['result_type']) {
        throw new \RuntimeException('Expected invalid_payload result for invalid payload.');
    }

    if ('missing_evaluator' !== $missingEvaluator['result_type']) {
        throw new \RuntimeException('Expected missing_evaluator result for incomplete registry.');
    }

    if ('context_error' !== $contextError['result_type']) {
        throw new \RuntimeException('Expected context_error result for incomplete context.');
    }

    return [
        'matched' => $matched,
        'not_matched' => $notMatched,
        'invalid_payload' => $invalidPayload,
        'missing_evaluator' => $missingEvaluator,
        'context_error' => $contextError,
    ];
}, 'tests_bootstrap_f3cms');